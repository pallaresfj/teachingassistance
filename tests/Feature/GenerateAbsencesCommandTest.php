<?php

use App\Console\Commands\GenerateAbsences;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Campus;
use App\Models\NonWorkingDay;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Habilitar feature flag para tests
    Config::set('attendance.absence_tracking_enabled', true);
    Config::set('attendance.absence_tracking_start_date', '2026-01-01');

    // Crear datos de prueba
    $this->campus = Campus::factory()->create(['name' => 'Campus Test']);
    $this->teacher = User::factory()->create([
        'name' => 'Profesor Test',
        'is_active' => true,
    ]);
});

describe('GenerateAbsences Command', function () {

    it('generates absence records for teachers without attendance', function () {
        // Crear horario para el lunes (día 1)
        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1, // Lunes
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        // Simular que es martes y procesamos el lunes anterior
        $monday = Carbon::parse('2026-02-02'); // Lunes 2 de febrero 2026

        $this->artisan('attendance:generate-absences', ['--date' => $monday->toDateString()])
            ->assertExitCode(0);

        // Verificar que se creó el registro de ausencia
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->first();

        expect($absence)->not->toBeNull();
        expect($absence->status)->toBe(AttendanceStatus::ABSENT);
        expect($absence->notes)->toContain('Generado automáticamente');
    });

    it('does not generate absence if teacher already registered', function () {
        // Crear horario para el lunes
        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $monday = Carbon::parse('2026-02-02');

        // Crear registro de asistencia existente
        Attendance::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'date' => $monday->toDateString(),
            'check_in_time' => $monday->setTime(8, 5),
            'status' => AttendanceStatus::ON_TIME,
        ]);

        $this->artisan('attendance:generate-absences', ['--date' => $monday->toDateString()])
            ->assertExitCode(0);

        // Verificar que solo existe el registro original
        $attendances = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->get();

        expect($attendances)->toHaveCount(1);
        expect($attendances->first()->status)->toBe(AttendanceStatus::ON_TIME);
    });

    it('skips non-working days', function () {
        // Crear horario para el lunes
        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $monday = Carbon::parse('2026-02-02');

        // Marcar el lunes como día no laborable
        NonWorkingDay::create([
            'date' => $monday->toDateString(),
            'name' => 'Día Festivo',
            'type' => NonWorkingDay::TYPE_HOLIDAY,
        ]);

        $this->artisan('attendance:generate-absences', ['--date' => $monday->toDateString()])
            ->assertExitCode(0);

        // Verificar que NO se creó registro de ausencia
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->first();

        expect($absence)->toBeNull();
    });

    it('respects feature flag when disabled', function () {
        Config::set('attendance.absence_tracking_enabled', false);

        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $monday = Carbon::parse('2026-02-02');

        $this->artisan('attendance:generate-absences', ['--date' => $monday->toDateString()])
            ->assertExitCode(0);

        // Verificar que NO se creó registro (feature desactivado)
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->first();

        expect($absence)->toBeNull();
    });

    it('works with --force flag when feature is disabled', function () {
        Config::set('attendance.absence_tracking_enabled', false);

        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $monday = Carbon::parse('2026-02-02');

        $this->artisan('attendance:generate-absences', [
            '--date' => $monday->toDateString(),
            '--force' => true,
        ])->assertExitCode(0);

        // Verificar que SÍ se creó registro con --force
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->first();

        expect($absence)->not->toBeNull();
    });

    it('dry-run mode does not create records', function () {
        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1,
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        $monday = Carbon::parse('2026-02-02');

        $this->artisan('attendance:generate-absences', [
            '--date' => $monday->toDateString(),
            '--dry-run' => true,
        ])->assertExitCode(0);

        // Verificar que NO se creó registro en dry-run
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $monday)
            ->first();

        expect($absence)->toBeNull();
    });

    it('respects absence tracking start date', function () {
        Config::set('attendance.absence_tracking_start_date', '2026-02-01');

        Schedule::create([
            'user_id' => $this->teacher->id,
            'campus_id' => $this->campus->id,
            'day_of_week' => 1, // Lunes
            'check_in_time' => '08:00:00',
            'check_out_time' => '12:00:00',
            'is_active' => true,
        ]);

        // Fecha anterior al inicio de tracking
        $oldMonday = Carbon::parse('2026-01-26');

        $this->artisan('attendance:generate-absences', ['--date' => $oldMonday->toDateString()])
            ->assertExitCode(0);

        // Verificar que NO se creó registro (fecha anterior)
        $absence = Attendance::where('user_id', $this->teacher->id)
            ->whereDate('date', $oldMonday)
            ->first();

        expect($absence)->toBeNull();
    });

});
