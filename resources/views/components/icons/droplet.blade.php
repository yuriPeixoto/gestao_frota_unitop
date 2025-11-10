@props(['class' => 'w-6 h-6', 'color' => 'currentColor'])

<svg xmlns="http://www.w3.org/2000/svg" 
    {{ $attributes->merge(['class' => $class]) }} 
    fill="none" 
    viewBox="0 0 24 24" 
    stroke="{{ $color }}">
    <path stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M12 21c4.418 0 8-3.582 8-8 0-5.5-8-13-8-13S4 7.5 4 13c0 4.418 3.582 8 8 8z" />
</svg>