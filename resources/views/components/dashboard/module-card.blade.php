@props([
    'title',
    'icon',
    'color' => 'blue',
    'route' => null,
    'alerts' => [],
    'metrics' => [],
    'actions' => []
])

@php
    $colors = [
        'blue' => 'border-blue-200 bg-blue-50',
        'green' => 'border-green-200 bg-green-50',
        'yellow' => 'border-yellow-200 bg-yellow-50',
        'red' => 'border-red-200 bg-red-50',
        'purple' => 'border-purple-200 bg-purple-50',
        'indigo' => 'border-indigo-200 bg-indigo-50',
        'orange' => 'border-orange-200 bg-orange-50',
        'gray' => 'border-gray-200 bg-gray-50',
    ];

    $iconColors = [
        'blue' => 'text-blue-600',
        'green' => 'text-green-600',
        'yellow' => 'text-yellow-600',
        'red' => 'text-red-600',
        'purple' => 'text-purple-600',
        'indigo' => 'text-indigo-600',
        'orange' => 'text-orange-600',
        'gray' => 'text-gray-600',
    ];

    $cardClass = $colors[$color] ?? $colors['blue'];
    $iconClass = $iconColors[$color] ?? $iconColors['blue'];
@endphp

<div class="relative bg-white rounded-xl shadow-lg border-2 {{ $cardClass }} hover:shadow-xl transition-all duration-300 group">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 rounded-lg {{ $cardClass }}">
                    <x-dynamic-component :component="$icon" class="{{ $iconClass }} w-8 h-8" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @if($route)
                        <a href="{{ $route }}" class="text-sm text-{{ $color }}-600 hover:text-{{ $color }}-800 font-medium">
                            Acessar módulo →
                        </a>
                    @endif
                </div>
            </div>

            @if(count($alerts) > 0)
                <div class="flex items-center space-x-1">
                    @foreach($alerts as $alert)
                        @if($alert['type'] === 'warning')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $alert['count'] }}
                            </span>
                        @elseif($alert['type'] === 'danger')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $alert['count'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Metrics -->
    @if(count($metrics) > 0)
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-{{ count($metrics) <= 2 ? count($metrics) : '3' }} gap-4">
                @foreach($metrics as $metric)
                    <div class="text-center">
                        <div class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 whitespace-nowrap overflow-hidden text-ellipsis px-1" title="{{ $metric['value'] }}">{{ $metric['value'] }}</div>
                        <div class="text-sm text-gray-500 mt-1 break-words">{{ $metric['label'] }}</div>
                        @if(isset($metric['trend']))
                            <div class="flex items-center justify-center mt-1">
                                @if($metric['trend'] === 'up')
                                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($metric['trend'] === 'down')
                                    <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                                @if(isset($metric['change']))
                                    <span class="text-xs text-gray-500 ml-1">{{ $metric['change'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Alert Details -->
    @if(count($alerts) > 0)
        <div class="px-6 pb-4">
            @foreach($alerts as $alert)
                <div class="mb-2 p-3 rounded-md {{ $alert['type'] === 'warning' ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200' }} border">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($alert['type'] === 'warning')
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium {{ $alert['type'] === 'warning' ? 'text-yellow-800' : 'text-red-800' }}">
                                {{ $alert['title'] }}
                            </p>
                            <p class="text-sm {{ $alert['type'] === 'warning' ? 'text-yellow-700' : 'text-red-700' }} mt-1">
                                {{ $alert['description'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Actions -->
    @if(count($actions) > 0)
        <div class="px-6 pb-6">
            <div class="flex flex-wrap gap-2">
                @foreach($actions as $action)
                    <a href="{{ $action['url'] }}"
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md {{ $action['style'] ?? 'text-'.$color.'-700 bg-'.$color.'-100 hover:bg-'.$color.'-200' }} transition-colors duration-200">
                        @if(isset($action['icon']))
                            {!! $action['icon'] !!}
                        @endif
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Hover overlay -->
    <div class="absolute inset-0 bg-gradient-to-r from-{{ $color }}-500/5 to-{{ $color }}-600/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
</div>
