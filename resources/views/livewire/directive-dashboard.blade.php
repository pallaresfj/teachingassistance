<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Directivo</h1>
            <p class="text-gray-600">Resumen de asistencia del personal</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="exportExcel"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </button>
            <button wire:click="exportPdf"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sede</label>
                <select wire:model.live="selectedCampus" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="">Todas las sedes</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Docente</label>
                <select wire:model.live="selectedUser" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="">Todos los docentes</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" wire:model.live="startDate" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" wire:model.live="endDate" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>
    </div>

    {{-- Directivo's Own Attendance Section --}}
    {{-- Directivo's Own Attendance Section --}}
    @if($todaySchedule)
        <div class="space-y-6">
            {{-- Shift Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Mi Asistencia Hoy</h3>
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
                                        Registrado a las {{ $todayAttendance->check_in_time->format('H:i') }}
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
    @endif

    {{-- Dashboard Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Statistics (Donut Chart) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-1">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Statistics</h3>
            <div class="flex flex-col items-center justify-center relative">
                {{-- Simple CSS Donut Chart Representation --}}
                <div class="relative w-48 h-48">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <!-- Background Circle -->
                        <path class="text-gray-100" stroke-width="3" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <!-- Data Circle (Punctuality) -->
                        <path class="text-blue-500 drop-shadow-lg"
                            stroke-dasharray="{{ $stats['global_punctuality'] ?? 0 }}, 100" stroke-width="3"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold text-gray-800">{{ $stats['global_punctuality'] ?? 0 }}%</span>
                        <span class="text-xs text-gray-500 font-medium">Puntualidad</span>
                    </div>
                </div>
                <div class="mt-6 w-full grid grid-cols-2 gap-4 text-center">
                    <div>
                        <div class="text-xs text-gray-400">Total Empleados</div>
                        <div class="font-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400">Presentes</div>
                        <div class="font-bold text-blue-600">{{ $stats['present_today'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance KPI Cards --}}
        <div class="lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Attendance</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                {{-- Card 1: Checked In --}}
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="text-gray-500 text-xs font-medium mb-1">Checked In</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_attendances'] ?? 0 }}</div>
                </div>

                {{-- Card 2: Late --}}
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center text-red-500 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-gray-500 text-xs font-medium mb-1">Late</div>
                    <div class="text-2xl font-bold text-red-500">{{ $stats['total_late'] ?? 0 }}</div>
                </div>

                {{-- Card 3: On Time --}}
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center text-green-500 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-gray-500 text-xs font-medium mb-1">On Time</div>
                    <div class="text-2xl font-bold text-green-500">{{ $stats['total_on_time'] ?? 0 }}</div>
                </div>

                {{-- Card 4: Absent --}}
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center justify-center py-6 hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div class="text-gray-500 text-xs font-medium mb-1">Not Checked In</div>
                    <div class="text-2xl font-bold text-amber-500">{{ $stats['absent_today'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Campus Summary --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Resumen por Sede</h3>
            </div>
            <div class="divide-y">
                @forelse($campusSummary as $campus)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ $campus->name }}</span>
                            <span class="text-sm text-gray-500">{{ $campus->total_attendances }} registros</span>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <span class="text-green-600">✓ {{ $campus->on_time_count }} a tiempo</span>
                            <span class="text-amber-600">⏱ {{ $campus->late_count }} retardos</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        No hay datos para mostrar.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Absent Today --}}
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Sin Asistencia Hoy</h3>
            </div>
            <div class="divide-y max-h-80 overflow-y-auto">
                @forelse($absentUsers as $user)
                    <div class="px-6 py-4 flex items-center gap-4">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-red-600 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-green-600">
                        <svg class="w-12 h-12 mx-auto mb-3 text-green-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ¡Todos han registrado asistencia!
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- User Summary Table --}}
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Resumen por Docente</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Docente</th>
                        <th class="px-6 py-3 text-center">Total</th>
                        <th class="px-6 py-3 text-center">A tiempo</th>
                        <th class="px-6 py-3 text-center">Retardos</th>
                        <th class="px-6 py-3 text-center">Puntualidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($userSummary as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $user->total_attendances }}</td>
                            <td class="px-6 py-4 text-center text-green-600">{{ $user->on_time_count }}</td>
                            <td class="px-6 py-4 text-center text-amber-600">{{ $user->late_count }}</td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                                                    {{ $user->punctuality >= 90 ? 'bg-green-100 text-green-800' : ($user->punctuality >= 70 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $user->punctuality }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No hay datos para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('message'))
        <div class="fixed bottom-4 right-4 px-4 py-3 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg shadow-lg">
            {{ session('message') }}
        </div>
    @endif
</div>