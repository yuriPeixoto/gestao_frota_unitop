<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Descarte<br>Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Produto<br>Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Produto</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($descarteImobilizados as $index => $descarteImobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $descarteImobilizado->id_descarte_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $descarteImobilizado->id_produtos_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $descarteImobilizado->produtoImobilizado->produto->descricao_produto }}
                </x-tables.cell>
                <x-tables.cell>{{ $descarteImobilizado->user->name ?? ''}}</x-tables.cell>
                <x-tables.cell>{{ $descarteImobilizado->filial->name ?? ''}}</x-tables.cell>
                <x-tables.cell nowrap>{{ $descarteImobilizado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $descarteImobilizado->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.descarteimobilizado.edit', $descarteImobilizado->id_descarte_imobilizados) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25]))
                        <button type="button"
                            onclick="destroyOrdemServico({{ $descarteImobilizado->id_descarte_imobilizados }})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3 w-3" />
                        </button>
                        @endif
                    </div>
                </x-tables.cell>


                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar exclusão">
                    Tem certeza que deseja excluir esse Produto Imobilizado <b class="title"></b>? <br>
                    Esta ação não pode ser desfeita. <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="confirmarExclusao({{ $descarteImobilizado->id_descarte_imobilizados }})"
                        class="mt-3 text-white">
                        Excluir
                    </x-bladewind::button>
                </x-bladewind.modal>

            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $descarteImobilizados->links() }}
    </div>
</div>