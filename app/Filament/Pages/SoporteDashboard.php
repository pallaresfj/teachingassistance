<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PunctualityWidget;
use App\Filament\Widgets\RecentAttendancesWidget;
use App\Filament\Widgets\SoporteStatsWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class SoporteDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Panel de Soporte';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()?->isSoporte();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Panel de Soporte';
    }

    public function getWidgets(): array
    {
        return [
            SoporteStatsWidget::class,
        ];
    }

    public function getDetailWidgets(): array
    {
        return [
            PunctualityWidget::class,
            RecentAttendancesWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Estadísticas del Sistema')
                    ->description('Usuarios y sedes registrados')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make($this->getColumns())
                            ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
                    ]),

                Section::make('Monitoreo de Asistencias')
                    ->description('Puntualidad general y registros recientes')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(3)
                            ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getDetailWidgets())),
                    ]),
            ]);
    }
}
