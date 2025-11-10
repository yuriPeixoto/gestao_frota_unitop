@props([
    'title',
    'value',
    'max',
    'percentage' => null,
    'color' => '#3B82F6',
    'textColor' => 'white',
    'subtitle' => null,
    'icon' => null,
    'size' => 'md', // sm, md, lg
    'layout' => 'vertical', // vertical, horizontal
])

@php
    // Calculate percentage if not provided
    $calculatedPercentage = $percentage ?? ($max > 0 ? ($value / $max) * 100 : 0);
    
    // Apply size to container
    $containerClass = match($size) {
        'sm' => 'p-3',
        'lg' => 'p-6',
        default => 'p-4',
    };
    
    // Apply size to title
    $titleClass = match($size) {
        'sm' => 'text-sm',
        'lg' => 'text-xl',
        default => 'text-lg',
    };
    
    // Apply size to value
    $valueClass = match($size) {
        'sm' => 'text-lg',
        'lg' => 'text-3xl',
        default => 'text-2xl',
    };
    
    // Layout specific classes
    $layoutClass = $layout === 'horizontal' 
        ? 'flex items-center' 
        : 'flex flex-col';
    
    $contentClass = $layout === 'horizontal' 
        ? 'ml-4 flex-1' 
        : 'mt-2';
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ $containerClass }} {{ $layoutClass }}">
    @if($icon)
        <div class="flex items-center justify-center {{ $layout === 'horizontal' ? 'mr-4' : 'mb-3' }}">
            {{ $icon }}
        </div>
    @endif
    
    <div class="{{ $contentClass }}">
        <h4 class="font-bold {{ $titleClass }} uppercase" style="color: {{ $color }}">
            {{ $title }}
        </h4>
        
        @if($subtitle)
            <p class="text-gray-500 text-sm mb-2">{{ $subtitle }}</p>
        @endif
        
        <div class="mt-2 mb-3">
            <div class="flex justify-between mb-1">
                <span class="text-sm font-medium">{{ number_format($value, 2, ',', '.') }}</span>
                <span class="text-sm font-bold">{{ number_format($calculatedPercentage, 1, ',', '.') }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                    class="h-2.5 rounded-full transition-all duration-500 ease-in-out" 
                    style="width: {{ min($calculatedPercentage, 100) }}%; background-color: {{ $color }}">
                </div>
            </div>
        </div>
        
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">
                de {{ number_format($max, 0, ',', '.') }}
            </div>
            <div class="{{ $valueClass }} font-bold" style="color: {{ $color }}">
                {{ number_format($calculatedPercentage, 0) }}%
            </div>
        </div>
    </div>
</div>