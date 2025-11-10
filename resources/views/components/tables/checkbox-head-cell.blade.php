@props(['text' => ''])

<th {{ $attributes->merge(['class' => 'px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider
    w-10']) }}>
    <div class="flex items-center justify-center">
        <input type="checkbox" id="select-all-checkbox"
            class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" @click.stop>
        @if($text)
        <span class="ml-2">{{ $text }}</span>
        @endif
    </div>
</th>