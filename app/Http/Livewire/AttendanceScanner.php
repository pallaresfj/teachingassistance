<?php

namespace App\Http\Livewire;

use App\Models\Campus;
use App\Services\AttendanceService;
use App\Services\GeolocationService;
use App\Services\QRGeneratorService;
use Livewire\Component;

class AttendanceScanner extends Component
{
    public bool $showScanner = false;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?float $accuracy = null;
    public bool $scanning = false;
    public bool $locationObtained = false;
    public string $locationStatus = 'Esperando ubicación...';
    public string $scanStatus = '';
    public string $errorMessage = '';
    public bool $registrationSuccess = false;

    protected $listeners = [
        'locationReceived',
        'qrScanned',
        'locationError',
    ];

    public function openScanner(): void
    {
        $this->reset(['errorMessage', 'scanStatus', 'registrationSuccess']);
        $this->showScanner = true;
        $this->scanning = true;
        $this->locationStatus = 'Obteniendo ubicación...';

        // Dispatch event to start camera and location
        $this->dispatch('startScanner');
    }

    public function closeScanner(): void
    {
        $this->showScanner = false;
        $this->scanning = false;
        $this->dispatch('stopScanner');
    }

    public function locationReceived(float $lat, float $lon, float $accuracy): void
    {
        $this->latitude = $lat;
        $this->longitude = $lon;
        $this->accuracy = $accuracy;
        $this->locationObtained = true;
        $this->locationStatus = "Ubicación obtenida (±{$accuracy}m)";
    }

    public function locationError(string $message): void
    {
        $this->locationStatus = 'Error: ' . $message;
        $this->errorMessage = 'No se pudo obtener la ubicación. Por favor, habilite el GPS.';
    }

    public function qrScanned(string $qrData): void
    {
        // Prevent multiple scans
        if (!$this->scanning) {
            return;
        }

        $this->scanning = false;

        // Check if location is obtained
        if (!$this->locationObtained || !$this->latitude || !$this->longitude) {
            $this->errorMessage = 'Esperando ubicación GPS. Por favor, intente de nuevo.';
            $this->scanning = true;
            return;
        }

        // Validate QR token
        $qrService = app(QRGeneratorService::class);
        $campus = $qrService->validateQRToken($qrData);

        if (!$campus) {
            $this->errorMessage = 'Código QR no válido o sede inactiva.';
            $this->scanning = true;
            return;
        }

        // Check if within radius
        $geoService = app(GeolocationService::class);
        $locationCheck = $geoService->isWithinCampusRadius($this->latitude, $this->longitude, $campus);

        if (!$locationCheck['within_radius']) {
            $distance = $geoService->formatDistance($locationCheck['distance']);
            $this->errorMessage = "Está fuera del radio permitido. Distancia: {$distance}";
            $this->scanning = true;
            return;
        }

        // Check if already registered today
        $attendanceService = app(AttendanceService::class);
        if ($attendanceService->hasRegisteredToday(auth()->user(), $campus)) {
            $this->errorMessage = 'Ya ha registrado asistencia hoy en esta sede.';
            $this->closeScanner();
            return;
        }

        // Register attendance
        try {
            $attendance = $attendanceService->registerAttendance(
                auth()->user(),
                $campus,
                $this->latitude,
                $this->longitude,
                $locationCheck['distance'],
                request()
            );

            $this->registrationSuccess = true;
            $this->scanStatus = "¡Asistencia registrada! Estado: {$attendance->status->label()}";
            $this->closeScanner();

            // Refresh the page after a short delay
            $this->dispatch('attendanceRegistered', status: $attendance->status->value);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al registrar asistencia. Intente de nuevo.';
            $this->scanning = true;
        }
    }

    public function render()
    {
        return view('livewire.attendance-scanner');
    }
}
