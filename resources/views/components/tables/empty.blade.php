@props(['cols' => 1, 'message' => 'Nenhum registro encontrado'])

<tr class="bg-white">
    <td colspan="{{ $cols }}" class="px-6 py-8 text-sm text-center text-gray-500">
        <div class="flex flex-col items-center justify-center space-y-3">
            <svg class="w-12 h-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    </td>
</tr>