@props(['striped' => false, 'hover' => true, 'index' => null, 'clickable' => false])

@php
    $classes = ['border-b'];
    
    if ($striped && $index !== null) {
        $classes[] = $index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
    } else {
        $classes[] = 'bg-white';
    }
    
    if ($hover) {
        $classes[] = 'hover:bg-gray-50';
    }
    
    if ($clickable) {
        $classes[] = 'cursor-pointer';
    }
@endphp

<tr {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</tr>