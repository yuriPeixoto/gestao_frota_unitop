<div class="modal-body">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Código do Pedido -->
        <x-forms.input name="id_pedido_compras" label="Cód. Pedido:" value="{{ $listagempedido->id_pedido_compras }}"
            disabled />

        <!-- Data Pedido -->
        <x-forms.input name="data_inclusao" type="date" label="Data Inclusão:"
            value="{{ \Carbon\Carbon::parse($listagempedido->data_inclusao)->format('Y-m-d') }}" disabled />

        <!-- Situação -->
        <x-forms.input name="situacao_pedido" label="Situação Pedido:" value="{{ $listagempedido->situacao_pedido }}"
            disabled />

        <!-- Comprador -->
        <x-forms.input name="id_comprador" label="Nome Comprador:" value="{{ $listagempedido->comprador->name ?? '-' }}"
            disabled />

        <!-- Código da Solicitação -->
        <x-forms.input name="id_solicitacoes_compras" label="Cód. Solicitação:"
            value="{{ $listagempedido->id_solicitacoes_compras ?? '-' }}" disabled />

        <!-- Data Solicitação -->
        <x-forms.input name="data_solicitacao" type="date" label="Data Solicitação:"
            value="{{ optional($listagempedido->solicitacaoCompra)->data_inclusao ? \Carbon\Carbon::parse($listagempedido->solicitacaoCompra->data_inclusao)->format('Y-m-d') : '' }}"
            disabled />

        <!-- Data Aprovação -->
        <x-forms.input name="data_aprovacao" type="date" label="Data Aprovação:"
            value="{{ \Carbon\Carbon::parse($listagempedido->solicitacaoCompra->data_aprovacao)->format('Y-m-d') }}"
            disabled />

        <!-- Filial -->
        <x-forms.input name="id_filial" label="Filial:" value="{{ $listagempedido->filial->name ?? '-' }}" disabled />


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
            @forelse($listagempedido->itens as $item)
            <x-tables.row>
                <x-tables.cell>{{ $item->produtos->id_produto ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $item->produtos->descricao_produto ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $listagempedido->fornecedor->nome_fornecedor ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $listagempedido->solicitacaoCompra->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $item->quantidade_produtos }}</x-tables.cell>
                <x-tables.cell>{{ number_format($item->valor_total, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ number_format($item->valor_total_desconto, 2, ',', '.') }}</x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum item encontrado para este pedido." />
            @endforelse
        </x-tables.body>
    </x-tables.table>
</div>