<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Produto</x-tables.head-cell>
            <x-tables.head-cell>Descrição Produto</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Qtde</x-tables.head-cell>
            <x-tables.head-cell>Valor Médio</x-tables.head-cell>
            <x-tables.head-cell>Total</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($estoqueImobilizados as $index => $estoqueImobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $estoqueImobilizado->id_produto_unitop }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->descricao_produto }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->descricao_filial }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->descricao_departamento }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->status }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->quantidade_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->valor_medio }}</x-tables.cell>
                <x-tables.cell>{{ $estoqueImobilizado->total }}</x-tables.cell>
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
        {{ $estoqueImobilizados->links() }}
    </div>
</div>