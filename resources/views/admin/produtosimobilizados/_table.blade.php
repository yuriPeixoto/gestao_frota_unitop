<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Imobilizados</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Patrimonio</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Produto</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Tipo Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($produtosImobilizados as $index => $produtosImobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $produtosImobilizado->id_produtos_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->cod_patrimonio }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->veiculo->placa ?? 'Não Informado'}}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->produto->descricao_produto }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->tipoImobilizado->descricao_tipo_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->departamento->descricao_departamento ?? 'Não informado' }}
                </x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->filial->name }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->status }}</x-tables.cell>
                <x-tables.cell>{{ $produtosImobilizado->user->name ?? $produtosImobilizado->id_usuario }}
                </x-tables.cell>
                <x-tables.cell nowrap>{{ $produtosImobilizado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.produtosimobilizados.edit', $produtosImobilizado->id_produtos_imobilizados) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25]))
                        <button type="button"
                            onclick="destroyOrdemServico({{ $produtosImobilizado->id_produtos_imobilizados }})"
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
                        onclick="confirmarExclusao({{ $produtosImobilizado->id_produtos_imobilizados }})"
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
        {{ $produtosImobilizados->links() }}
    </div>
</div>