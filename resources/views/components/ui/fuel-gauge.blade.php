@props([
    'title',
    'value',
    'max',
    'percentage',
    'color' => '#0984E3',
    'lastUpdated' => null
])

@php
    // Function to determine color based on percentage
    $fillLevelColor = function($pct) {
        if ($pct <= 25) {
            return '#EF4444'; // Vermelho - Crítico
        } elseif ($pct <= 50) {
            return '#F59E0B'; // Amarelo - Atenção
        } elseif ($pct <= 75) {
            return '#3B82F6'; // Azul - Bom
        } else {
            return '#10B981'; // Verde - Excelente
        }
    };
    
    // Get the color for this specific gauge
    $gaugeColor = $fillLevelColor($percentage);
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
    <div class="p-4">
        <div class="flex justify-between items-center mb-2">
            <h4 class="font-bold text-lg uppercase" style="color: {{ $color }}">{{ $title }}</h4>
            <span class="text-sm text-gray-500">
                @if($lastUpdated)
                    Atualizado em {{ $lastUpdated->format('d/m/Y H:i') }}
                @endif
            </span>
        </div>
        
        <div class="mb-2">
            <div class="flex justify-between mb-1">
                <span class="text-sm font-medium">{{ number_format($value, 2, ',', '.') }} / {{ number_format($max, 0, ',', '.') }}</span>
                <span class="text-sm font-bold">{{ number_format($percentage, 1, ',', '.') }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div 
                    class="h-4 rounded-full transition-all duration-500 ease-in-out" 
                    style="width: {{ min($percentage, 100) }}%; background-color: {{ $gaugeColor }}">
                </div>
            </div>
        </div>
        
        <div class="flex justify-between items-end">
            <div class="text-sm text-gray-500">
                {{ number_format($percentage, 0) }}% da capacidade total
            </div>
            <div class="flex items-center">
                <div class="h-8 w-8 flex items-center justify-center rounded-full"
                    style="background-color: {{ $gaugeColor }}">
                    <x-icons.droplet class="h-4 w-4 text-white" />
                </div>
            </div>
        </div>
    </div>
</div>