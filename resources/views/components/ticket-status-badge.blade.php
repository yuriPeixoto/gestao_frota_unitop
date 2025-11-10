@props(['status'])

@php
    $colorMap = [
        'bg' => 'bg-' . $status->color() . '-100',
        'text' => 'text-' . $status->color() . '-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorMap['bg'] . ' ' . $colorMap['text']]) }}>
    <i class="fas fa-{{ $status->icon() }} mr-1"></i>
    {{ $status->label() }}
</span>
