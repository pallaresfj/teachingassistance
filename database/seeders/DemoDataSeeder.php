<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Schedule;
use App\Models\User;
use App\Services\QRGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create soporte user
        User::create([
            'name' => 'Admin Soporte',
            'email' => 'soporte@teachingassistance.com',
            'password' => Hash::make('pass1234'),
            'role' => 'soporte',
            'phone' => '3001234567',
            'identification_number' => '1234567890',
            'is_active' => true,
        ]);

        // Create directivo user
        $directivo = User::create([
            'name' => 'Juan Directivo',
            'email' => 'directivo@teachingassistance.com',
            'password' => Hash::make('pass1234'),
            'role' => 'directivo',
            'phone' => '3009876543',
            'identification_number' => '0987654321',
            'is_active' => true,
        ]);

        // Create docentes
        $docentes = [];
        for ($i = 1; $i <= 5; $i++) {
            $docentes[] = User::create([
                'name' => "Docente {$i}",
                'email' => "docente{$i}@teachingassistance.com",
                'password' => Hash::make('pass1234'),
                'role' => 'docente',
                'phone' => "300111000{$i}",
                'identification_number' => "111222333{$i}",
                'is_active' => true,
            ]);
        }

        // Create campuses with QR codes
        $campusesData = [
            [
                'name' => 'Sede Norte',
                'address' => 'Calle 100 #15-20, Bogotá',
                'latitude' => 4.7110,
                'longitude' => -74.0721,
                'radius_meters' => 100,
            ],
            [
                'name' => 'Sede Sur',
                'address' => 'Carrera 30 #45-67, Bogotá',
                'latitude' => 4.5981,
                'longitude' => -74.0758,
                'radius_meters' => 100,
            ],
            [
                'name' => 'Sede Centro',
                'address' => 'Avenida Jiménez #10-25, Bogotá',
                'latitude' => 4.6097,
                'longitude' => -74.0817,
                'radius_meters' => 80,
            ],
        ];

        $qrService = app(QRGeneratorService::class);
        $campuses = [];

        foreach ($campusesData as $data) {
            $campus = Campus::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'radius_meters' => $data['radius_meters'],
                'qr_token' => Str::random(32),
                'is_active' => true,
            ]);

            // Generate QR code for the campus
            try {
                $qrService->generateCampusQR($campus);
            } catch (\Exception $e) {
                // QR generation might fail if package not installed yet
                $this->command->warn("Could not generate QR for {$campus->name}: {$e->getMessage()}");
            }

            $campuses[] = $campus;
        }

        // Create schedules for docentes
        $daysOfWeek = [1, 2, 3, 4, 5]; // Monday to Friday
        $times = [
            ['check_in' => '07:00', 'check_out' => '12:00'],
            ['check_in' => '08:00', 'check_out' => '13:00'],
            ['check_in' => '14:00', 'check_out' => '18:00'],
        ];

        foreach ($docentes as $index => $docente) {
            // Assign each docente to a campus
            $campus = $campuses[$index % count($campuses)];
            $time = $times[$index % count($times)];

            // Create schedule for each weekday
            foreach ($daysOfWeek as $day) {
                Schedule::create([
                    'user_id' => $docente->id,
                    'campus_id' => $campus->id,
                    'day_of_week' => $day,
                    'check_in_time' => $time['check_in'],
                    'check_out_time' => $time['check_out'],
                    'tolerance_minutes' => 15,
                    'is_active' => true,
                ]);
            }
        }

        // Create schedule for directivo
        $campus = $campuses[0];
        foreach ([1, 2, 3, 4, 5] as $day) {
            Schedule::create([
                'user_id' => $directivo->id,
                'campus_id' => $campus->id,
                'day_of_week' => $day,
                'check_in_time' => '08:00',
                'check_out_time' => '17:00',
                'tolerance_minutes' => 15,
                'is_active' => true,
            ]);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Soporte', 'soporte@teachingassistance.com', 'pass1234'],
                ['Directivo', 'directivo@teachingassistance.com', 'pass1234'],
                ['Docente', 'docente1@teachingassistance.com', 'pass1234'],
            ]
        );
    }
}

