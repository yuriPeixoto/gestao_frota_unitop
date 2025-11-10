@props(['type'])

@php
    $colorMap = [
        'bg' => 'bg-' . $type->color() . '-100',
        'text' => 'text-' . $type->color() . '-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $colorMap['bg'] . ' ' . $colorMap['text']]) }}>
    <i class="fas fa-{{ $type->icon() }} mr-1"></i>
    {{ $type->label() }}
</span>
