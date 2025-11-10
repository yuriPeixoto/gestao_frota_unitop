@props(['class' => 'w-6 h-6'])

<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5"
    stroke="currentColor" {{ $attributes->merge(['class' => $class]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
</svg>
