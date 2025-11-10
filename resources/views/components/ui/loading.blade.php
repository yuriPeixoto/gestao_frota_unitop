@props([
    'message' => 'Carregando...',
    'size' => 'md',
    'color' => 'primary',
    'fullscreen' => false,
    'animation' => 'spin'
])

@php
    // Definir tamanhos
    $sizes = [
        'sm' => 'w-6 h-6',
        'md' => 'w-10 h-10',
        'lg' => 'w-16 h-16',
        'xl' => 'w-24 h-24',
    ];
    
    // Definir cores
    $colors = [
        'primary' => 'text-indigo-600',
        'secondary' => 'text-gray-600',
        'success' => 'text-green-600',
        'danger' => 'text-red-600',
        'warning' => 'text-orange-500',
        'info' => 'text-blue-500',
    ];
    
    // Definir animações
    $animations = [
        'spin' => 'animate-spin',
        'pulse' => 'animate-pulse',
        'bounce' => 'animate-bounce',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $colorClass = $colors[$color] ?? $colors['primary'];
    $animationClass = $animations[$animation] ?? $animations['spin'];
    
    $containerClass = $fullscreen 
        ? 'fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50' 
        : 'inline-flex flex-col items-center justify-center';
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }}>
    <div class="relative flex justify-center items-center">
        <!-- Círculo animado -->
        <svg class="{{ $sizeClass }} {{ $animationClass }} {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    
    @if($message)
        <div class="mt-3 text-center">
            <p class="{{ $colorClass }} font-medium">{{ $message }}</p>
        </div>
    @endif
</div>