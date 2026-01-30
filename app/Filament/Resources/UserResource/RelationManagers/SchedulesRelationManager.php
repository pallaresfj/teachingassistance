<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Campus;
use App\Models\Schedule;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Validation\Rules\Unique;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Horarios';

    protected static ?string $modelLabel = 'Horario';

    protected static ?string $pluralModelLabel = 'Horarios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asignación')
                    ->schema([
                        Select::make('campus_id')
                            ->label('Sede')
                            ->options(Campus::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('day_of_week')
                            ->label('Día de la Semana')
                            ->options(Schedule::DAYS)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Horario')
                    ->schema([
                        TimePicker::make('check_in_time')
                            ->label('Hora de Entrada')
                            ->required()
                            ->seconds(false),

                        TimePicker::make('check_out_time')
                            ->label('Hora de Salida')
                            ->seconds(false),

                        TextInput::make('tolerance_minutes')
                            ->label('Tolerancia')
                            ->required()
                            ->numeric()
                            ->default(15)
                            ->minValue(0)
                            ->maxValue(60)
                            ->suffix('minutos'),

                        Toggle::make('is_active')
                            ->label('Horario Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Check if times overlap.
     */
    protected function timesOverlap(string $start1, ?string $end1, string $start2, ?string $end2): bool
    {
        $toMinutes = fn(string $time): int => ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
        
        $start1Min = $toMinutes($start1);
        $end1Min = $end1 ? $toMinutes($end1) : $start1Min + 60;
        $start2Min = $toMinutes($start2);
        $end2Min = $end2 ? $toMinutes($end2) : $start2Min + 60;

        return $start1Min < $end2Min && $start2Min < $end1Min;
    }

    /**
     * Validate no overlapping schedules exist.
     */
    protected function validateNoOverlap(array $data, ?Schedule $record = null): ?string
    {
        $userId = $this->getOwnerRecord()->id;
        $dayOfWeek = $data['day_of_week'] ?? null;
        $checkInTime = $data['check_in_time'] ?? null;
        $checkOutTime = $data['check_out_time'] ?? null;

        if (!$userId || $dayOfWeek === null || !$checkInTime) {
            return null;
        }

        // Normalize time format
        $normalizeTime = function ($time): string {
            if ($time instanceof \DateTimeInterface) {
                return $time->format('H:i');
            }
            if (is_string($time) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
                return substr($time, 0, 5);
            }
            $parsed = date_create($time);
            return $parsed ? $parsed->format('H:i') : (string) $time;
        };

        $checkInTime = $normalizeTime($checkInTime);
        $checkOutTime = $checkOutTime ? $normalizeTime($checkOutTime) : null;

        $query = Schedule::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true);

        if ($record) {
            $query->where('id', '!=', $record->id);
        }

        foreach ($query->get() as $schedule) {
            $existingIn = $schedule->check_in_time->format('H:i');
            $existingOut = $schedule->check_out_time?->format('H:i');

            if ($this->timesOverlap($checkInTime, $checkOutTime, $existingIn, $existingOut)) {
                $dayName = Schedule::DAYS[$dayOfWeek] ?? $dayOfWeek;
                return "Ya existe un horario activo el día {$dayName} que se superpone ({$existingIn}" . ($existingOut ? " - {$existingOut}" : "") . ").";
            }
        }

        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->recordTitleAttribute('day_name')
            ->columns([
                TextColumn::make('campus.name')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('day_name')
                    ->label('Día')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('check_in_time')
                    ->label('Entrada')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('check_out_time')
                    ->label('Salida')
                    ->time('H:i'),

                TextColumn::make('tolerance_minutes')
                    ->label('Tolerancia')
                    ->suffix(' min')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('campus_id')
                    ->label('Sede')
                    ->options(Campus::pluck('name', 'id')),

                SelectFilter::make('day_of_week')
                    ->label('Día')
                    ->options(Schedule::DAYS),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Horario')
                    ->before(function (CreateAction $action, array $data) {
                        $error = $this->validateNoOverlap($data);
                        if ($error) {
                            Notification::make()
                                ->title('Error de validación')
                                ->body($error)
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->before(function (EditAction $action, array $data, Schedule $record) {
                        $error = $this->validateNoOverlap($data, $record);
                        if ($error) {
                            Notification::make()
                                ->title('Error de validación')
                                ->body($error)
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('day_of_week')
            ->emptyStateHeading('Sin horarios asignados')
            ->emptyStateDescription('Agregue horarios para este usuario usando el botón de arriba.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
