<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. OS.<br>Imobilizados</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Cód. <br> Produto Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>


        <x-tables.body>
            @forelse ($manutencaoImobilizados as $index => $manutencaoImobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $manutencaoImobilizado->id_manutencao_imobilizado }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoImobilizado->filial->name ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoImobilizado->fornecedor->nome_fornecedor ?? ''}}</x-tables.cell>
                <x-tables.cell nowrap>{{ $manutencaoImobilizado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $manutencaoImobilizado->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoImobilizado->produto->descricao_produto ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoImobilizado->situacao }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.ordemservicoimobilizado.edit', $manutencaoImobilizado->id_manutencao_imobilizado) }}"
                            title="Editar"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        <a onclick="onVoltarEstoque({{ $manutencaoImobilizado->id_manutencao_imobilizado }})"
                            title="Estornar para o estoque"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <x-icons.refresh class="h-3 w-3" />
                        </a>
                    </div>
                </x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $manutencaoImobilizados->links() }}
    </div>
</div>