<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach ($options as $option)
        <option value="{{ $option }}" {{ old($name, $value ?? '' )==$option ? 'selected' : '' }}>
            {{ $option }}
        </option>
        @endforeach
    </select>
    @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>