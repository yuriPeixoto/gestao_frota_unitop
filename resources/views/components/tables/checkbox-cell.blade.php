@props(['id' => null, 'checked' => false])

<td {{ $attributes->merge(['class' => 'px-3 py-2 whitespace-nowrap text-center w-10']) }}>
    <div class="flex items-center justify-center">
        <input type="checkbox" id="row-checkbox-{{ $id }}" data-id="{{ $id }}" {{ $checked ? 'checked' : '' }}
            class="table-row-checkbox form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
            @click.stop x-bind:checked="selectedRows.includes('{{ $id }}')">
    </div>
</td>