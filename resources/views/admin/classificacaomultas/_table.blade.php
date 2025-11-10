<div class="results-table mt-4">
    <x-tables.table>
        <x-tables.header>

            <x-tables.head-cell>ID</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusao</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Multa</x-tables.head-cell>
            <x-tables.head-cell>Pontos</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>

        </x-tables.header>

        <x-tables.body>
            @forelse($classificacaoMultas as $itens)
            <x-tables.row>

                <x-tables.cell>{{ $itens->id_classificacao_multa}}</x-tables.cell>
                <x-tables.cell>{{ $itens->data_inclusao}}</x-tables.cell>
                <x-tables.cell>
                    {{ $itens->data_alteracao}}
                </x-tables.cell>
                <x-tables.cell>{{ $itens->descricao_multa}}</x-tables.cell>
                <x-tables.cell>{{ $itens->pontos }}
                </x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <x-tooltip content="Editar" placement="top">
                            <a href="{{ route('admin.classificacaomultas.edit', $itens->id_classificacao_multa) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-4 w-4" />
                            </a>
                        </x-tooltip>
                        <x-tooltip content="Excluir" placement="top">
                            <form
                                action="{{ route('admin.classificacaomultas.destroy', $itens->id_classificacao_multa) }}"
                                method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir esta classificação de multa?')"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">

                                @csrf
                                @method('DELETE')

                                <button type="submit">
                                    <x-icons.trash class="h-4 w-4" />
                                </button>
                            </form>
                        </x-tooltip>

                    </div>
                </x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    <div class="mt-4">
        {{ $classificacaoMultas->links() }}
    </div>
</div>