@props([
'name',
'label',
'value' => null,
'required' => false,
'min' => null,
'max' => null,
'autofocus' => false,
'disabled' => false,
'helpText' => null,
])

@php
// Processar o value (pode ser um Carbon, string ou null)
$dateValue = $value;

if ($value instanceof \Illuminate\Support\Carbon) {
$dateValue = $value->format('Y-m-d');
} elseif (is_string($value) && strlen($value) > 10) {
// Se for uma string de data+hora, pegar apenas a parte da data
try {
$dateValue = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
} catch (\Exception $e) {
$dateValue = $value;
}
}
@endphp

<div x-data="{ 
    focused: false,
    dateValue: '{{ $dateValue }}',
    isValid: true,
    errorMessage: '',
    
    validate() {
        this.isValid = true;
        this.errorMessage = '';
        
        if (!this.dateValue) {
            return true;
        }
        
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(this.dateValue)) {
            this.isValid = false;
            this.errorMessage = 'Formato de data inválido';
            return false;
        }
        
        const date = new Date(this.dateValue);
        if (isNaN(date.getTime())) {
            this.isValid = false;
            this.errorMessage = 'Data inválida';
            return false;
        }
        
        // Validar data mínima
        @if ($min)
            const minDate = new Date('{{ $min }}');
            if (!isNaN(minDate.getTime()) && date < minDate) {
                this.isValid = false;
                this.errorMessage = 'Data não pode ser anterior a {{ \Illuminate\Support\Carbon::parse($min)->format('d/m/Y') }}';
                return false;
            }
        @endif
        
        // Validar data máxima
        @if ($max)
            const maxDate = new Date('{{ $max }}');
            if (!isNaN(maxDate.getTime()) && date > maxDate) {
                this.isValid = false;
                this.errorMessage = 'Data não pode ser posterior a {{ \Illuminate\Support\Carbon::parse($max)->format('d/m/Y') }}';
                return false;
            }
        @endif
        
        return true;
    }
}" x-init="$nextTick(() => validate())"
    @input-date-changed.window="if ($event.detail.name === '{{ $name }}') dateValue = $event.detail.value"
    class="w-full">

    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($required)
        <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="mt-1 relative rounded-md shadow-sm">
        <input type="date" name="{{ $name }}" id="{{ $name }}" x-model="dateValue" @change="validate()"
            @focus="focused = true" @blur="focused = false" {{ $required ? 'required' : '' }} {{ $autofocus
            ? 'autofocus' : '' }} {{ $disabled ? 'disabled' : '' }} class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                  disabled:bg-gray-100 disabled:cursor-not-allowed
                  transition duration-150 ease-in-out"
            :class="{'border-red-300': !isValid, 'ring-red-500': !isValid && focused}" {{ $attributes }}>
    </div>

    <div x-show="!isValid" class="mt-1 text-sm text-red-600" x-text="errorMessage"></div>

    @if ($helpText)
    <p class="mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif

    @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>