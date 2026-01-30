<?php

namespace App\Filament\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Campus;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationLabel = 'Asistencias';

    protected static ?string $modelLabel = 'Asistencia';

    protected static ?string $pluralModelLabel = 'Asistencias';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->isDirectivo();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de Asistencia')
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(AttendanceStatus::options())
                            ->required(),

                        Textarea::make('notes')
                            ->label('Observaciones')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Docente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('campus.name')
                    ->label('Sede')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('check_in_time')
                    ->label('Entrada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('check_out_time')
                    ->label('Salida')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label())
                    ->color(fn($state) => $state?->color()),

                TextColumn::make('distance_from_campus')
                    ->label('Distancia')
                    ->suffix(' m')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('campus_id')
                    ->label('Sede')
                    ->options(Campus::pluck('name', 'id')),

                SelectFilter::make('user_id')
                    ->label('Docente')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(AttendanceStatus::options()),

                Filter::make('check_in_time')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde'),
                        DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('check_in_time', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('check_in_time', '<=', $data['until']));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Modificar Estado'),
            ])
            ->bulkActions([
                // Read-only, no bulk actions
            ])
            ->defaultSort('check_in_time', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
