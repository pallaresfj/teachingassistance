<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampusSummaryWidget extends Widget
{
    protected string $view = 'filament.widgets.campus-summary-widget';
    
    protected int | string | array $columnSpan = 'full';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function getCampusSummary()
    {
        $query = Attendance::query();

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('check_in_time', [$this->startDate, $this->endDate]);
        } else {
            $query->whereBetween('check_in_time', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        return $query->select(
            'campus_id',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'on_time' THEN 1 ELSE 0 END) as on_time"),
            DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late"),
            DB::raw("ROUND((SUM(CASE WHEN status = 'on_time' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as punctuality")
        )
            ->with('campus')
            ->groupBy('campus_id')
            ->get();
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isDirectivo();
    }
}
