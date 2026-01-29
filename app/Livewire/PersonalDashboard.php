<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Livewire\Component;

class PersonalDashboard extends Component
{
    public array $stats = [];
    public $attendances;
    public $todayAttendance;
    public $todaySchedule;
    public string $selectedMonth;
    public array $calendarData = [];

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->loadData();
    }

    public function loadData(): void
    {
        $user = auth()->user();

        // Get stats
        $attendanceService = app(AttendanceService::class);
        $this->stats = $attendanceService->getUserStats($user);

        // Get today's attendance
        $this->todayAttendance = $attendanceService->getTodayAttendance($user);

        // Get today's schedule
        $this->todaySchedule = Schedule::where('user_id', $user->id)
            ->where('day_of_week', now()->dayOfWeek)
            ->where('is_active', true)
            ->with('campus')
            ->first();

        // Get recent attendances
        $this->attendances = Attendance::where('user_id', $user->id)
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->with(['campus', 'schedule'])
            ->get();

        // Build calendar data
        $this->buildCalendarData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->buildCalendarData();
    }

    protected function buildCalendarData(): void
    {
        $user = auth()->user();
        [$year, $month] = explode('-', $this->selectedMonth);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($a) => $a->check_in_time->format('Y-m-d'));

        $this->calendarData = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            $this->calendarData[$dateKey] = [
                'day' => $current->day,
                'status' => $attendance?->status?->value,
                'color' => $attendance?->status?->hexColor() ?? '#e5e7eb',
            ];

            $current->addDay();
        }
    }

    public function render()
    {
        return view('livewire.personal-dashboard')
            ->layout('layouts.app');
    }
}
