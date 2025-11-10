@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'label' => null,
    'placeholder' => 'Selecione...',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'multiple' => false,
    'size' => null,
    'help' => null,
    'valueField' => 'id',
    'textField' => 'name',
])

@php
    $id = $id ?? $name;
@endphp

<div class="w-full text-black-100">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }} {{ $readonly ? 'readonly' : '' }} {{ $multiple ? 'multiple' : '' }}
        @if ($size) size="{{ $size }}" @endif
        {{ $attributes->merge([
            'class' =>
                'mt-1 block w-full rounded-md shadow-sm sm:text-sm ' .
                ($readonly || $disabled ? 'bg-gray-100 border-gray-300' : 'border-gray-300') .
                ' focus:border-indigo-500 focus:ring-indigo-500',
        ]) }}>
        @if (!$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $option)
            @php
                $optionValue = is_array($option) || is_object($option) ? data_get($option, $valueField) : $option;
                $optionText = is_array($option) || is_object($option) ? data_get($option, $textField) : $option;
                $isSelected = is_array($selected) ? in_array($optionValue, $selected) : $optionValue == $selected;
            @endphp
            <option value="{{ $optionValue }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $optionText }}
            </option>
        @endforeach
    </select>

    @if ($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
