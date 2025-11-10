<div>
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="flex items-center space-x-4">
        @foreach ($options as $optionLabel => $optionValue)
        <label class="label cursor-pointer">
            <span class="label-text mr-1 text-sm font-medium text-gray-700">{{ $optionLabel }}</span>
            <input type="radio" name="{{ $name }}" value="{{ $optionValue }}"
                class="radio {{ $optionValue == 1 ? 'checked:bg-green-500' : 'checked:bg-red-500' }}" @checked((string)
                old($name, $value==='' ? '0' : '1' )===(string) $optionValue) />
        </label>
        @endforeach
    </div>
    @error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>