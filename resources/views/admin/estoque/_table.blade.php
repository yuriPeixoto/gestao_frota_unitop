<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Código do Estoque
            </th>

            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Descrição Estoque
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Filial
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($estoques as $estoque)
            <tr>
                <td class="px-6 py-4 flex items-center whitespace-nowrap text-sm font-medium space-x-2">
                    <x-tooltip content="Editar">
                        <a href="{{ route('admin.estoque.edit', $estoque->id_estoque) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.edit class="h-3 w-3" />
                        </a>
                    </x-tooltip>
                    <form action="{{ route('admin.estoque.delete', $estoque->id_estoque) }}" method="POST"
                        class="inline-block">
                        @csrf
                        @method('DELETE')
                        <x-tooltip content="Excluir">
                            <button type="submit" onclick="return confirm('Tem certeza que deseja excluir?')"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </x-tooltip>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $estoque->id_estoque }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $estoque->descricao_estoque }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $estoque->filial->name ?? null }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    Nenhuma pergunta cadastrado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $estoques->links() }}
</div>
