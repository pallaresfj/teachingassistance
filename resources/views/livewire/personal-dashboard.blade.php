<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mi Dashboard</h1>
            <p class="text-gray-600">Bienvenido, {{ auth()->user()->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ now()->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
    </div>

    {{-- Today's Status --}}
    @if($todaySchedule)
        <div class="space-y-6">
            {{-- Shift Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hoy</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sede</p>
                            <p class="font-semibold text-gray-900">{{ $todaySchedule->campus->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Horario</p>
                            <p class="font-semibold text-gray-900">{{ $todaySchedule->time_range }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attendance Action Card --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Registro de Asistencia</h3>
                <div style="display: flex; justify-content: center;">
                    <div style="width: 60%;">
                        @if($todayAttendance)
                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium text-green-700">
                                        Asistencia registrada a las {{ $todayAttendance->check_in_time->format('H:i') }}
                                        - {{ $todayAttendance->status->label() }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <livewire:attendance-scanner />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50 rounded-xl p-6 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-600">No tiene horario programado para hoy.</p>
        </div>
    @endif

    {{-- Personal Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {{-- Total --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500 mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div class="text-gray-500 text-xs font-medium mb-1">Total Asistencias</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</div>
        </div>

        {{-- On Time --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-500 mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-gray-500 text-xs font-medium mb-1">A tiempo</div>
            <div class="text-2xl font-bold text-green-500">{{ $stats['on_time'] ?? 0 }}</div>
        </div>

        {{-- Late --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500 mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-gray-500 text-xs font-medium mb-1">Retardos</div>
            <div class="text-2xl font-bold text-amber-500">{{ $stats['late'] ?? 0 }}</div>
        </div>

        {{-- Punctuality --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-500 mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="text-gray-500 text-xs font-medium mb-1">Puntualidad</div>
            <div class="text-2xl font-bold text-indigo-500">{{ $stats['punctuality'] ?? 0 }}%</div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        {{-- Header: Title, Month Selector, and Legend --}}
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Calendario de Asistencia</h3>
                <input type="month" wire:model.live="selectedMonth" 
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            {{-- Legend with Icons --}}
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600 bg-gray-50 rounded-lg px-4 py-3">
                <span class="font-medium text-gray-700">Convenciones:</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                    <span>A tiempo</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd" />
                    </svg>
                    <span>Retardo</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
                    </svg>
                    <span>Falta</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M9 1.5H5.625c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5zm6.61 10.936a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 14.47a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                        <path d="M14.25 5.25a5.23 5.23 0 00-1.279-3.434 9.768 9.768 0 016.963 6.963A5.23 5.23 0 0016.5 7.5h-1.875a.375.375 0 01-.375-.375V5.25z" />
                    </svg>
                    <span>Justificado</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm0 8.625a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd" />
                    </svg>
                    <span>Sin registro</span>
                </div>
            </div>
        </div>

        <x-attendance-calendar :data="$calendarData" />
    </div>

    {{-- Recent Attendances --}}
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Últimas Asistencias</h3>
        </div>
        <div class="divide-y">
            @forelse($attendances as $attendance)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                            style="background-color: {{ $attendance->status->hexColor() }}20;">
                            <svg class="w-5 h-5" style="color: {{ $attendance->status->hexColor() }};" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                @if($attendance->status->value === 'on_time')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                @elseif($attendance->status->value === 'late')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $attendance->campus->name }}</p>
                            <p class="text-sm text-gray-500">{{ $attendance->check_in_time->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium rounded-full"
                        style="background-color: {{ $attendance->status->hexColor() }}20; color: {{ $attendance->status->hexColor() }};">
                        {{ $attendance->status->label() }}
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No hay registros de asistencia.
                </div>
            @endforelse
        </div>
    </div>
</div>