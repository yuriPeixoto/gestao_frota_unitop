<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Requisição<br>Imobilizados</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Observação</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($relacaoImobilizados as $index => $relacaoImobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $relacaoImobilizado->id_relacao_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $relacaoImobilizado->status}}</x-tables.cell>
                <x-tables.cell>{{ $relacaoImobilizado->user->name ?? $relacaoImobilizado->id_usuario }}</x-tables.cell>
                <x-tables.cell>{{ $relacaoImobilizado->filial->name }}</x-tables.cell>
                <x-tables.cell>{{ $relacaoImobilizado->motivo_transferencia }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $relacaoImobilizado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $relacaoImobilizado->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="#" onclick="visualizarItens({{ $relacaoImobilizado->id_relacao_imobilizados }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.print class="h-3 w-3" />
                        </a>

                        <a href="{{ route('admin.saidarelacaoimobilizado.edit', $relacaoImobilizado->id_relacao_imobilizados) }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md shadow-sm text-gray-600 bg-white hover:bg-gray-650 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.box-open class="h-4 w-4 mr-2" />
                            Baixar Imobilizados
                        </a>
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <x-bladewind.modal name="vizualizar-requisicao-itens" size="omg" cancel_button_label="" ok_button_label="Ok"
        title="Requisição Itens">
        <x-tables.table>
            <x-tables.header id="tabelaHeader">
                <x-tables.head-cell>Cód<br>Requisição<br>Imobilizado<br>Itens</x-tables.head-cell>
                <x-tables.head-cell>Cód. Relação Imobilizado</x-tables.head-cell>
                <x-tables.head-cell>Produto</x-tables.head-cell>
                <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tabelaBody"></x-tables.body>

        </x-tables.table>


    </x-bladewind.modal>

    <div class="mt-4">
        {{ $relacaoImobilizados->links() }}
    </div>
</div>