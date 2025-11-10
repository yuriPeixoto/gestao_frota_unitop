@props(['priority'])

@php
    $colorMap = [
        'bg' => 'bg-' . $priority->color() . '-100',
        'text' => 'text-' . $priority->color() . '-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorMap['bg'] . ' ' . $colorMap['text']]) }}>
    {{ $priority->label() }}
</span>
