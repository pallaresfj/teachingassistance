@props([
    'data' => []
])

@php
    // Days of week headers (starting from Sunday)
    $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    
    // Get the first day of the month to calculate offset
    $firstDate = collect($data)->keys()->first();
    $startOffset = $firstDate ? \Carbon\Carbon::parse($firstDate)->dayOfWeek : 0;
@endphp

<div>
    {{-- Legend --}}
    <div class="flex gap-4 mb-4 text-xs">
        <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded" style="background-color: #10b981;"></span>
            <span>A tiempo</span>
        </div>
        <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded" style="background-color: #f59e0b;"></span>
            <span>Retardo</span>
        </div>
        <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded" style="background-color: #ef4444;"></span>
            <span>Falta</span>
        </div>
        <div class="flex items-center gap-1">
            <span class="w-3 h-3 rounded" style="background-color: #3b82f6;"></span>
            <span>Justificado</span>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="grid grid-cols-7 gap-1">
        {{-- Day headers --}}
        @foreach($days as $day)
            <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $day }}</div>
        @endforeach

        {{-- Empty cells for offset --}}
        @for($i = 0; $i < $startOffset; $i++)
            <div class="aspect-square"></div>
        @endfor

        {{-- Calendar days --}}
        @foreach($data as $date => $dayData)
            <div 
                class="aspect-square rounded-lg flex items-center justify-center text-sm font-medium transition-transform hover:scale-105 cursor-default"
                style="background-color: {{ $dayData['color'] }};"
                title="{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}"
            >
                <span class="@if($dayData['status']) text-white @else text-gray-600 @endif">
                    {{ $dayData['day'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>
