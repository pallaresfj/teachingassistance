<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Campus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Generate a general attendance report.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param array $filters Optional filters (campus_id, user_id, status)
     * @return Collection
     */
    public function generateGeneralReport(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        array $filters = []
    ): Collection {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = Attendance::with(['user', 'campus', 'schedule'])
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->orderBy('check_in_time', 'desc');

        // Apply filters
        if (!empty($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Generate an absence report for a specific date.
     *
     * @param Carbon|null $date
     * @return Collection
     */
    public function generateAbsenceReport(?Carbon $date = null): Collection
    {
        $date = $date ?? today();
        $dayOfWeek = $date->dayOfWeek;

        // Get all users with active schedules on this day
        $usersWithSchedules = User::whereHas('schedules', function ($query) use ($dayOfWeek) {
            $query->where('day_of_week', $dayOfWeek)
                ->where('is_active', true);
        })
            ->where('is_active', true)
            ->get();

        // Get users who registered attendance on this date
        $usersWithAttendance = Attendance::whereDate('check_in_time', $date)
            ->pluck('user_id')
            ->toArray();

        // Filter users who didn't register
        return $usersWithSchedules->filter(function ($user) use ($usersWithAttendance) {
            return !in_array($user->id, $usersWithAttendance);
        })->sortBy('name')->values();
    }

    /**
     * Get attendance summary by campus.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     */
    public function getSummaryByCampus(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return Campus::withCount([
            'attendances as total_attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_time', [$startDate, $endDate]);
            },
            'attendances as on_time_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_time', [$startDate, $endDate])
                    ->where('status', AttendanceStatus::ON_TIME->value);
            },
            'attendances as late_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('check_in_time', [$startDate, $endDate])
                    ->where('status', AttendanceStatus::LATE->value);
            },
        ])->get();
    }

    /**
     * Get attendance summary by user.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param int|null $campusId
     * @return Collection
     */
    public function getSummaryByUser(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $campusId = null
    ): Collection {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = User::query()
            ->where('is_active', true)
            ->withCount([
                'attendances as total_attendances' => function ($q) use ($startDate, $endDate, $campusId) {
                    $q->whereBetween('check_in_time', [$startDate, $endDate]);
                    if ($campusId) {
                        $q->where('campus_id', $campusId);
                    }
                },
                'attendances as on_time_count' => function ($q) use ($startDate, $endDate, $campusId) {
                    $q->whereBetween('check_in_time', [$startDate, $endDate])
                        ->where('status', AttendanceStatus::ON_TIME->value);
                    if ($campusId) {
                        $q->where('campus_id', $campusId);
                    }
                },
                'attendances as late_count' => function ($q) use ($startDate, $endDate, $campusId) {
                    $q->whereBetween('check_in_time', [$startDate, $endDate])
                        ->where('status', AttendanceStatus::LATE->value);
                    if ($campusId) {
                        $q->where('campus_id', $campusId);
                    }
                },
            ]);

        return $query->get()->map(function ($user) {
            $user->punctuality = $user->total_attendances > 0
                ? round(($user->on_time_count / $user->total_attendances) * 100, 1)
                : 0;
            return $user;
        });
    }

    /**
     * Export data to Excel format (stub - requires maatwebsite/excel).
     *
     * @param Collection $data
     * @param string $filename
     * @return string Path to exported file
     */
    public function exportToExcel(Collection $data, string $filename): string
    {
        // TODO: Implement with maatwebsite/excel if needed
        // For now, return a placeholder
        return "exports/{$filename}.xlsx";
    }

    /**
     * Export data to PDF format (stub - requires barryvdh/laravel-dompdf).
     *
     * @param Collection $data
     * @param string $filename
     * @return string Path to exported file
     */
    public function exportToPDF(Collection $data, string $filename): string
    {
        // TODO: Implement with barryvdh/laravel-dompdf if needed
        // For now, return a placeholder
        return "exports/{$filename}.pdf";
    }

    /**
     * Get daily attendance trend.
     *
     * @param int $days Number of days to include
     * @param int|null $campusId Filter by campus
     * @return Collection
     */
    public function getDailyTrend(int $days = 30, ?int $campusId = null): Collection
    {
        $startDate = now()->subDays($days);

        $query = Attendance::selectRaw('DATE(check_in_time) as date, status, COUNT(*) as count')
            ->where('check_in_time', '>=', $startDate)
            ->groupBy('date', 'status')
            ->orderBy('date');

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        return $query->get()->groupBy('date');
    }
}
