@props(['class' => 'w-3 h-3'])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <g id="Exchange">
        <path
            d="M16.293,5.707,17.586,7H4A1,1,0,0,0,4,9H17.586L16.293,10.293a1,1,0,0,0,1.414,1.414l3-3a1,1,0,0,0,0-1.414l-3-3a1,1,0,0,0-1.414,1.414Z"
            fill="currentcolor" stroke="currentColor" {{ $attributes->merge(['class' => 'inline-block']) }} />
        <path
            d="M7.707,18.293L6.414,17H20a1,1,0,0,0,0-2H6.414l1.293-1.293a1,1,0,0,0-1.414-1.414l-3,3a1,1,0,0,0,0,1.414l3,3a1,1,0,0,0,1.414-1.414Z"
            fill="currentcolor" stroke="currentColor" />
    </g>
</svg>
