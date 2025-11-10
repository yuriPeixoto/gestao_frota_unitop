@props([
    'type' => 'text',
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'autofocus' => false,
    'maxlength' => null,
    'step' => null,
    'min' => null,
    'max' => null,
    'autocomplete' => 'on',
    'help' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}"
        placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} {{ $readonly ? 'readonly' : '' }}
        {{ $disabled ? 'disabled' : '' }} {{ $autofocus ? 'autofocus' : '' }}
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        @if ($type === 'number' && $step) step="{{ $step }}" @endif
        @if ($min) min="{{ $min }}" @endif
        @if ($max) max="{{ $max }}" @endif autocomplete="{{ $autocomplete }}"
        {{ $attributes->merge([
            'class' =>
                'mt-1 block w-full rounded-md shadow-sm sm:text-sm ' .
                ($readonly || $disabled ? 'bg-gray-100 border-gray-300' : 'border-gray-300') .
                ' focus:border-indigo-500 focus:ring-indigo-500',
        ]) }} />

    @if ($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
