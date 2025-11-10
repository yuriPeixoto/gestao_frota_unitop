<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Deflatores</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Cód. Deflator</x-tables.head-cell>
            <x-tables.head-cell>Motorista</x-tables.head-cell>
            <x-tables.head-cell>Data Evento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($listagem as $evento)
            <x-tables.row>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.deflatoreseventospormotoristas.edit', $evento->id_deflatores_motoristas_eventos) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>
                        <form
                            action="{{ route('admin.deflatoreseventospormotoristas.delete', $evento->id_deflatores_motoristas_eventos) }}"
                            method="POST" class="inline-block"
                            onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </form>

                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $evento->id_deflatores_motoristas_eventos }}</x-tables.cell>
                <x-tables.cell>{{ format_date($evento->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($evento->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $evento->deflatores->descricao_evento }}</x-tables.cell>
                <x-tables.cell>{{ $evento->motorista->nome ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{ format_date($evento->data_evento) }}</x-tables.cell>
                <x-tables.cell>{{ $evento->filial->name ?? '' }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="6" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    {{ $listagem->links() }}
</div>
</div>