@props([
    'name',
    'id' => null,
    'label' => '',
    'checked' => false,
    'value' => '1',
    'disabled' => false,
    'required' => false,
    'error' => null,
])

@php
    $id = $id ?? $name;
    // Converte para booleano para garantir funcionamento adequado
    $isChecked = filter_var($checked, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="flex items-center {{ $disabled ? 'opacity-60' : '' }}">
    {{-- Campo oculto para garantir que o valor '0' é enviado quando não estiver marcado --}}
    <input type="hidden" name="{{ $name }}" value="0">
    
    <input
        type="checkbox"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($isChecked)
        @disabled($disabled)
        @required($required)
        {{ $attributes->merge(['class' => 'rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50']) }}
    >
    
    @if($label)
        <label for="{{ $id }}" class="ml-2 block text-sm text-gray-700 {{ $disabled ? 'cursor-not-allowed' : 'cursor-pointer' }}">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
</div>

@if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif

@error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror