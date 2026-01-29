<?php

namespace App\Http\Livewire;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Campus;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Component;

class DirectiveDashboard extends Component
{
    public ?int $selectedCampus = null;
    public ?int $selectedUser = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public array $stats = [];
    public $campusSummary;
    public $userSummary;
    public $absentUsers;
    public $campuses;
    public $users;
    public $todaySchedule;
    public $todayAttendance;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->campuses = Campus::where('is_active', true)->get();
        $this->users = User::where('is_active', true)
            ->whereIn('role', ['docente', 'directivo'])
            ->get();

        // Load today's schedule and attendance for the directive
        $user = auth()->user();
        $attendanceService = app(AttendanceService::class);
        $this->todayAttendance = $attendanceService->getTodayAttendance($user);
        $this->todaySchedule = Schedule::where('user_id', $user->id)
            ->where('day_of_week', now()->dayOfWeek)
            ->where('is_active', true)
            ->with('campus')
            ->first();

        $this->loadStats();
    }

    public function loadStats(): void
    {
        $reportService = app(ReportService::class);

        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->startOfMonth();
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now();

        // Get campus summary
        $this->campusSummary = $reportService->getSummaryByCampus($startDate, $endDate);

        // Get user summary
        $this->userSummary = $reportService->getSummaryByUser(
            $startDate,
            $endDate,
            $this->selectedCampus
        );

        // Get today's absent users
        $this->absentUsers = $reportService->generateAbsenceReport(today());

        // Calculate general stats
        $this->stats = [
            'total_attendances' => $this->campusSummary->sum('total_attendances'),
            'total_on_time' => $this->campusSummary->sum('on_time_count'),
            'total_late' => $this->campusSummary->sum('late_count'),
            'global_punctuality' => 0,
            'absent_today' => $this->absentUsers->count(),
        ];

        if ($this->stats['total_attendances'] > 0) {
            $this->stats['global_punctuality'] = round(
                ($this->stats['total_on_time'] / $this->stats['total_attendances']) * 100,
                1
            );
        }
    }

    public function updatedSelectedCampus(): void
    {
        $this->loadStats();
    }

    public function updatedSelectedUser(): void
    {
        $this->loadStats();
    }

    public function updatedStartDate(): void
    {
        $this->loadStats();
    }

    public function updatedEndDate(): void
    {
        $this->loadStats();
    }

    public function exportExcel(): void
    {
        // TODO: Implement Excel export
        session()->flash('message', 'Exportación a Excel próximamente disponible.');
    }

    public function exportPdf(): void
    {
        // TODO: Implement PDF export
        session()->flash('message', 'Exportación a PDF próximamente disponible.');
    }

    public function render()
    {
        return view('livewire.directive-dashboard');
    }
}
