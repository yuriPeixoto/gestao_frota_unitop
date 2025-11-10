@props([
    'align' => 'left',
    'nowrap' => false,
])

@php
    $alignmentClass =
        [
            'left' => 'text-left',
            'center' => 'text-center',
            'right' => 'text-right',
        ][$align] ?? 'text-left';

    $classes = ['px-6', 'py-4', 'text-sm', 'text-gray-700', $alignmentClass];

    if ($nowrap) {
        $classes[] = 'whitespace-nowrap';
    }
@endphp

<td {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</td>
