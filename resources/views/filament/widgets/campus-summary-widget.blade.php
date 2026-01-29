<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏫 Resumen por Sede
        </x-slot>

        <div class="space-y-3">
            @php
                $summary = $this->getCampusSummary();
            @endphp

            @forelse($summary as $item)
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                {{ $item->campus->name }}
                            </h4>
                            <div class="mt-2 grid grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Total:</span>
                                    <span class="ml-1 font-medium text-gray-900 dark:text-white">{{ $item->total }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">A Tiempo:</span>
                                    <span class="ml-1 font-medium text-green-600 dark:text-green-400">{{ $item->on_time }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Retardos:</span>
                                    <span class="ml-1 font-medium text-yellow-600 dark:text-yellow-400">{{ $item->late }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Puntualidad:</span>
                                    <span class="ml-1 font-medium 
                                        @if($item->punctuality >= 90) text-green-600 dark:text-green-400
                                        @elseif($item->punctuality >= 75) text-yellow-600 dark:text-yellow-400
                                        @else text-red-600 dark:text-red-400
                                        @endif
                                    ">
                                        {{ $item->punctuality }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ml-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold
                                @if($item->punctuality >= 90) bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400
                                @elseif($item->punctuality >= 75) bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400
                                @else bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400
                                @endif
                            ">
                                {{ number_format($item->punctuality, 0) }}%
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>No hay datos de asistencias en el período seleccionado</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
