<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AttendanceCalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.attendance-calendar-widget';
    
    protected int | string | array $columnSpan = 'full';

    public string $selectedMonth;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function getCalendarData(): array
    {
        $startDate = Carbon::parse($this->selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($this->selectedMonth)->endOfMonth();

        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->check_in_time)->format('Y-m-d');
            });

        $calendar = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateKey = $current->format('Y-m-d');
            $attendance = $attendances->get($dateKey);

            $calendar[$dateKey] = [
                'date' => $current->copy(),
                'day' => $current->day,
                'status' => $attendance?->status ?? null,
                'hasAttendance' => $attendance !== null,
            ];

            $current->addDay();
        }

        return $calendar;
    }

    public function previousMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth)->addMonth()->format('Y-m');
    }

    public static function canView(): bool
    {
        return Auth::check() && (Auth::user()->isDocente() || Auth::user()->isDirectivo());
    }
}
