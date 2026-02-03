<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Campus;
use App\Models\NonWorkingDay;
use App\Models\Schedule;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ReportService - Full Attendance Report', function () {

    beforeEach(function () {
        $this->reportService = app(ReportService::class);
        $this->campus = Campus::factory()->create(['name' => 'Campus Test']);
        $this->teacher = User::factory()->create([
            'name' => 'Profesor Test',
            'is_active' => true,
        ]);

        // Crear horario de lunes a viernes
        foreach ([1, 2, 3, 4, 5] as $day) {
            Schedule::create([
                'user_id' => $this->teacher->id,
                'campus_id' => $this->campus->id,
                'day_of_week' => $day,
                'check_in_time' => '08:00:00',
                'check_out_time' => '12:00:00',
                'is_active' => true,
            ]);
        }
    });

    it('calculates expected work days correctly', function () {
        $startDate = Carbon::parse('2026-02-02'); // Lunes
        $endDate = Carbon::parse('2026-02-06');   // Viernes

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        // 5 días hábiles (lun-vie)
        expect($report['summary']['expected_days'])->toBe(5);
    });

    it('excludes non-working days from expected days', function () {
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        // Marcar el miércoles como festivo
        NonWorkingDay::create([
            'date' => '2026-02-04',
            'name' => 'Festivo',
            'type' => NonWorkingDay::TYPE_HOLIDAY,
        ]);

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        // 4 días hábiles (5 - 1 festivo)
        expect($report['summary']['expected_days'])->toBe(4);
    });

    it('counts attendance statuses correctly', function () {
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        // Crear registros de asistencia
        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-02',
            'check_in_time' => Carbon::parse('2026-02-02 08:00:00'),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-03',
            'check_in_time' => Carbon::parse('2026-02-03 08:30:00'),
            'status' => AttendanceStatus::LATE,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-04',
            'check_in_time' => null,
            'status' => AttendanceStatus::ABSENT,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-05',
            'check_in_time' => null,
            'status' => AttendanceStatus::JUSTIFIED,
        ]);

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        expect($report['summary']['on_time'])->toBe(1);
        expect($report['summary']['late'])->toBe(1);
        expect($report['summary']['absent'])->toBe(1);
        expect($report['summary']['justified'])->toBe(1);
        expect($report['summary']['present'])->toBe(3); // on_time + late + justified
    });

    it('calculates attendance rate correctly', function () {
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        // 3 asistencias de 5 días esperados
        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-02',
            'check_in_time' => Carbon::parse('2026-02-02 08:00:00'),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-03',
            'check_in_time' => Carbon::parse('2026-02-03 08:00:00'),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-04',
            'check_in_time' => Carbon::parse('2026-02-04 08:30:00'),
            'status' => AttendanceStatus::LATE,
        ]);

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        // 3/5 = 60%
        expect($report['rates']['attendance_rate'])->toBe(60.0);
    });

    it('calculates punctuality rate correctly', function () {
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        // 2 a tiempo, 2 tarde = 50% puntualidad
        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-02',
            'check_in_time' => Carbon::parse('2026-02-02 08:00:00'),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-03',
            'check_in_time' => Carbon::parse('2026-02-03 08:00:00'),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-04',
            'check_in_time' => Carbon::parse('2026-02-04 08:30:00'),
            'status' => AttendanceStatus::LATE,
        ]);

        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => '2026-02-05',
            'check_in_time' => Carbon::parse('2026-02-05 08:45:00'),
            'status' => AttendanceStatus::LATE,
        ]);

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        // 2/4 = 50%
        expect($report['rates']['punctuality_rate'])->toBe(50.0);
    });

    it('includes non-working days info in report', function () {
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        NonWorkingDay::create([
            'date' => '2026-02-04',
            'name' => 'Festivo Nacional',
            'type' => NonWorkingDay::TYPE_HOLIDAY,
        ]);

        $report = $this->reportService->generateFullAttendanceReport(
            $this->teacher->id,
            $startDate,
            $endDate
        );

        expect($report['non_working_days'])->toHaveCount(1);
        expect($report['non_working_days'][0]['name'])->toBe('Festivo Nacional');
    });

});

describe('ReportService - Absence Summary Report', function () {

    beforeEach(function () {
        $this->reportService = app(ReportService::class);
        $this->campus = Campus::factory()->create(['name' => 'Campus Test']);
        $this->teacher = User::factory()->create([
            'name' => 'Profesor Test',
            'is_active' => true,
        ]);

        // Crear horario de lunes a viernes
        foreach ([1, 2, 3, 4, 5] as $day) {
            Schedule::create([
                'user_id' => $this->teacher->id,
                'campus_id' => $this->campus->id,
                'day_of_week' => $day,
                'check_in_time' => '08:00:00',
                'check_out_time' => '12:00:00',
                'is_active' => true,
            ]);
        }
    });

    it('generates summary for all users', function () {
        $teacher2 = User::factory()->create(['name' => 'Profesor 2', 'is_active' => true]);

        Schedule::create([
            'user_id' => $teacher2->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-06');

        $summary = $this->reportService->generateAbsenceSummaryReport(
            $startDate,
            $endDate
        );

        expect($summary)->toHaveCount(2);
        expect($summary->pluck('user_name')->toArray())->toContain('Profesor Test', 'Profesor 2');
    });

});
