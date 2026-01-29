<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            ⚠️ Docentes Sin Registro Hoy
        </x-slot>

        @php
            $absentUsers = $this->getAbsentUsers();
        @endphp

        @if($absentUsers->count() > 0)
            <div class="space-y-2">
                @foreach($absentUsers as $user)
                    <div class="flex items-center justify-between bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                <span class="text-red-600 dark:text-red-400 font-semibold text-sm">
                                    {{ substr($user->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $user->name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>
                        
                        <x-filament::badge color="danger">
                            Sin registro
                        </x-filament::badge>
                    </div>
                @endforeach
            </div>
            
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                Total: {{ $absentUsers->count() }} {{ $absentUsers->count() === 1 ? 'docente' : 'docentes' }}
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-green-500 text-4xl mb-2">✓</div>
                <p class="text-gray-600 dark:text-gray-400">
                    Todos los docentes han registrado su asistencia
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
