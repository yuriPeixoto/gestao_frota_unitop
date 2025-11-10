{{-- resources/views/components/forms/button.blade.php --}}
@if ($href)
    <a href="{{ $disabled ? '#' : $href }}" class="{{ $classes() }}" {{ $disabled ? 'aria-disabled="true"' : '' }}
        {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $buttonType }}" class="{{ $classes() }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes }}>
        {{ $slot }}
    </button>
@endif
