<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <span>📆 Calendario de Asistencias</span>
                
                <div class="flex items-center gap-2">
                    <x-filament::icon-button 
                        icon="heroicon-m-chevron-left"
                        wire:click="previousMonth"
                        label="Mes anterior"
                    />
                    
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[120px] text-center">
                        {{ \Carbon\Carbon::parse($selectedMonth)->locale('es')->isoFormat('MMMM YYYY') }}
                    </span>
                    
                    <x-filament::icon-button 
                        icon="heroicon-m-chevron-right"
                        wire:click="nextMonth"
                        label="Mes siguiente"
                    />
                </div>
            </div>
        </x-slot>

        <div class="grid grid-cols-7 gap-2">
            @foreach(['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $day)
                <div class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 py-2">
                    {{ $day }}
                </div>
            @endforeach

            @php
                $calendarData = $this->getCalendarData();
                $startDay = reset($calendarData)['date']->dayOfWeek;
            @endphp

            @for($i = 0; $i < $startDay; $i++)
                <div class="aspect-square"></div>
            @endfor

            @foreach($calendarData as $dateKey => $data)
                <div class="aspect-square">
                    <div class="w-full h-full flex items-center justify-center rounded-lg border 
                        @if($data['hasAttendance'])
                            @if($data['status']->value === 'on_time')
                                bg-green-100 dark:bg-green-900 border-green-500 text-green-700 dark:text-green-300
                            @elseif($data['status']->value === 'late')
                                bg-yellow-100 dark:bg-yellow-900 border-yellow-500 text-yellow-700 dark:text-yellow-300
                            @else
                                bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600
                            @endif
                        @else
                            border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500
                        @endif
                    ">
                        <span class="text-sm font-medium">{{ $data['day'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-4 text-xs mt-4">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-500"></div>
                <span class="text-gray-600 dark:text-gray-400">A tiempo</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-yellow-500"></div>
                <span class="text-gray-600 dark:text-gray-400">Retardo</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2 border-gray-300"></div>
                <span class="text-gray-600 dark:text-gray-400">Sin registro</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
