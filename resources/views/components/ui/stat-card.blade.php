@props([
    'title' => 'Estatística',
    'value' => '0',
    'icon' => 'document-text',
    'color' => 'primary',
    'description' => null,
    'trend' => null,
    'trendUp' => true,
])

@php
    $colors = [
        'primary' => 'bg-indigo-100 text-indigo-800',
        'secondary' => 'bg-gray-100 text-gray-800',
        'success' => 'bg-green-100 text-green-800',
        'danger' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];

    $bgColor = $colors[$color] ?? $colors['primary'];
    
    $trendColor = $trendUp ? 'text-green-600' : 'text-red-600';
    $trendIcon = $trendUp ? 'trending-up' : 'trending-down';
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 rounded-md {{ $bgColor }}">
                    @if($icon === 'document-text')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @elseif($icon === 'calendar')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @elseif($icon === 'check-circle')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($icon === 'x-circle')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($icon === 'lightning-bolt')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    @endif
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    {{ $title }}
                </dt>
                <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($value, 0, ',', '.') }}
                    </div>
                    
                    @if($trend)
                    <div class="ml-2 flex items-center text-sm {{ $trendColor }}">
                        <svg class="h-5 w-5 flex-shrink-0 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if($trendUp)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            @endif
                        </svg>
                        <span class="sr-only">
                            {{ $trendUp ? 'Increased' : 'Decreased' }} by
                        </span>
                        {{ $trend }}
                    </div>
                    @endif
                </dd>
                @if($description)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $description }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>