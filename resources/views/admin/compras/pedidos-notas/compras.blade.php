<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <x-forms.input name="id_pedido_compras" label="Cód. Pedido:" value="{{ $pedidoNota->id_pedido_compras }}"
            disabled />
    </div>

    <div>
        <x-forms.input type="date" name="data_inclusao" label="Data do Pedido:"
            value="{{ optional($pedidoNota->pedidoCompra->data_inclusao)->format('Y-m-d') }}" disabled />
    </div>

    <div>
        <x-forms.input name="situacao_pedido" label="Situação do Pedido:"
            value="{{ $pedidoNota->pedidoCompra->situacaoPedido->descricao_situacao_pedido }}" disabled />
    </div>

    <div>
        <x-forms.input name="id_comprador" label="Comprador:"
            value="{{ $pedidoNota->pedidoCompra->comprador->name ?? '' }}" disabled />
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <x-forms.input name="id_solicitacoes_compras" label="Cód. Solicitação Compras:"
            value="{{ $pedidoNota->pedidoCompra->id_solicitacoes_compras }}" disabled />
    </div>

    <div>
        <x-forms.input type="date" name="data_solicitacao" label="Data da Solicitação de Compra:"
            value="{{ $pedidoNota->data_solicitacao ? \Carbon\Carbon::parse($pedidoNota->data_solicitacao)->format('Y-m-d') : '' }}"
            disabled />
    </div>

    <div>
        <x-forms.input type="date" name="data_aprovacao" label="Data Aprovação Solicitação:"
            value="{{ optional($pedidoNota->pedidoCompra->solicitacaoCompra)->data_aprovacao ? optional($pedidoNota->pedidoCompra->solicitacaoCompra->data_aprovacao)->format('Y-m-d') : '' }}"
            disabled />
    </div>


    <div>
        <x-forms.input name="filial" label="Filial:" value="{{ $pedidoNota->filial }}" disabled />
    </div>
</div>



<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Cód. Produto</x-tables.head-cell>
        <x-tables.head-cell>Descrição Produto</x-tables.head-cell>
        <x-tables.head-cell>Nome Fornecedor</x-tables.head-cell>
        <x-tables.head-cell>Situação Compra</x-tables.head-cell>
        <x-tables.head-cell>Qtde. Produtos</x-tables.head-cell>
        <x-tables.head-cell>Valor Total</x-tables.head-cell>
        <x-tables.head-cell>Valor Total com Desconto</x-tables.head-cell>
    </x-tables.header>

    <x-tables.body>
        @foreach($pedidoNota->pedidoCompra->itens as $item)
        <x-tables.row>
            <x-tables.cell>{{ $item->cod_produto }}</x-tables.cell>
            <x-tables.cell>{{ $item->produtos->descricao_produto }}</x-tables.cell>
            <x-tables.cell>{{ $pedidoNota->fornecedor->nome_fornecedor }}</x-tables.cell>
            <x-tables.cell>{{ $pedidoNota->pedidoCompra->solicitacaoCompra->situacao_compra }}</x-tables.cell>
            <x-tables.cell>{{ $item->quantidade_produtos }}</x-tables.cell>
            <x-tables.cell>{{ number_format($item->valor_total ?? 0, 2, ',', '.') }}</x-tables.cell>
            <x-tables.cell>{{ number_format($item->valor_total_desconto ?? 0, 2, ',', '.') }}</x-tables.cell>
        </x-tables.row>
        @endforeach
    </x-tables.body>
</x-tables.table>