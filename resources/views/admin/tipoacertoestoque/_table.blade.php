<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($tipoacertos as $tipoacerto)
            <x-tables.row>
                <x-tables.cell>
                    {{ $tipoacerto->id_tipo_acerto_estoque }}
                </x-tables.cell>

                <x-tables.cell>
                    {{ $tipoacerto->descricao_tipo_acerto }}
                </x-tables.cell>

                <x-tables.cell>
                    {{ format_date($tipoacerto->data_inclusao) }}
                </x-tables.cell>

                <x-tables.cell>
                    {{ format_date($tipoacerto->data_alteracao) }}
                </x-tables.cell>

                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="javascript:void(0)" onclick="openDrawerEdit({{ $tipoacerto->id_tipo_acerto_estoque }})"
                            title="Editar">
                            <x-icons.edit class="w-4 h-4 text-blue-600" />
                        </a>


                        <form
                            action="{{ route('admin.tipoacertoestoque.destroy', $tipoacerto->id_tipo_acerto_estoque) }}"
                            method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                onclick="return confirm('Tem certeza que deseja excluir este Tipo de Acerto ?')"
                                title="Excluir" class="p-1 hover:bg-red-100 rounded">
                                <x-icons.trash class="w-4 h-4 text-red-600" />
                            </button>
                        </form>
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoacertos->links() }}
    </div>
</div>