<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserSummaryTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Docente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('attendances_count')
                    ->label('Total')
                    ->alignCenter()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('on_time_count')
                    ->label('A Tiempo')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('late_count')
                    ->label('Retardos')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('punctuality')
                    ->label('Puntualidad')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        if ($record->attendances_count == 0) {
                            return 0;
                        }
                        return round(($record->on_time_count / $record->attendances_count) * 100, 1);
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('attendances_count', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return User::where('role', UserRole::DOCENTE)
            ->where('is_active', true)
            ->withCount([
                'attendances' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_in_time', [$startDate, $endDate]);
                },
                'attendances as on_time_count' => function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'on_time')
                        ->whereBetween('check_in_time', [$startDate, $endDate]);
                },
                'attendances as late_count' => function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'late')
                        ->whereBetween('check_in_time', [$startDate, $endDate]);
                },
            ])
            ->having('attendances_count', '>', 0);
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isDirectivo();
    }
}
