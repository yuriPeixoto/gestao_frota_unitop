@props(['class' => 'w-auto max-w-[157px] h-10 sm:h-14 transition-all dark:brightness-100'])

<img src="{{ asset('images/logoM.svg') }}" alt="Logo Unitop" {{ $attributes->merge(['class' => $class]) }} />
