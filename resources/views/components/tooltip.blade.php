@props([
    'content' => '',
    'placement' => 'top',
    'theme' => 'light',
])

<span {{ $attributes->merge(['class' => 'tooltip-trigger']) }} data-tippy-content="{{ $content }}"
    data-placement="{{ $placement }}" data-theme="{{ $theme }}">
    {{ $slot }}
</span>
