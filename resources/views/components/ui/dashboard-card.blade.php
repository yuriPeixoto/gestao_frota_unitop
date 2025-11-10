@props([
    'title',
    'value',
    'icon' => null,
    'iconColor' => '#3B82F6',
    'trend' => null,     // positive, negative, neutral
    'trendValue' => null,
    'trendLabel' => null,
    'footer' => null,
    'size' => 'md',      // sm, md, lg
    'valueFormat' => null // callback function to format the value
])

@php
    // Set size specific classes
    $containerClass = match($size) {
        'sm' => 'p-3',
        'lg' => 'p-6',
        default => 'p-4',
    };
    
    $titleClass = match($size) {
        'sm' => 'text-sm',
        'lg' => 'text-xl',
        default => 'text-lg',
    };
    
    $valueClass = match($size) {
        'sm' => 'text-xl',
        'lg' => 'text-4xl',
        default => 'text-3xl',
    };
    
    // Format value if a formatter is provided
    $formattedValue = $value;
    if (is_callable($valueFormat)) {
        $formattedValue = $valueFormat($value);
    }
    
    // Set trend icon and color
    $trendIcon = match($trend) {
        'positive' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'negative' => 'M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6',
        default => null,
    };
    
    $trendColor = match($trend) {
        'positive' => 'text-green-500',
        'negative' => 'text-red-500',
        'neutral' => 'text-gray-500',
        default => 'text-gray-500',
    };
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ $containerClass }}">
    <div class="flex justify-between items-start mb-3">
        <h4 class="font-medium {{ $titleClass }} text-gray-700">
            {{ $title }}
        </h4>
        
        @if($icon)
            <div class="rounded-full p-2" style="background-color: {{ $iconColor }}20">
                <div class="text-white" style="color: {{ $iconColor }}">
                    {{ $icon }}
                </div>
            </div>
        @endif
    </div>
    
    <div class="flex items-end justify-between">
        <div>
            <div class="font-bold {{ $valueClass }} text-gray-800">
                {{ $formattedValue }}
            </div>
            
            @if($trend && $trendValue)
                <div class="flex items-center mt-1 {{ $trendColor }}">
                    @if($trendIcon)
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trendIcon }}"></path>
                        </svg>
                    @endif
                    <span class="text-sm font-medium">{{ $trendValue }}</span>
                    @if($trendLabel)
                        <span class="text-xs text-gray-500 ml-1">{{ $trendLabel }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    @if($footer)
        <div class="mt-4 pt-3 border-t border-gray-200 text-sm text-gray-500">
            {{ $footer }}
        </div>
    @endif
</div>