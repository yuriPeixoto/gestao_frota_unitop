<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Cód. Item</x-tables.head-cell>
        <x-tables.head-cell>Cód. Transferência</x-tables.head-cell>
        <x-tables.head-cell>Produto</x-tables.head-cell>
        <x-tables.head-cell>Qtde Pedido</x-tables.head-cell>
        <x-tables.head-cell>Qtde Baixa</x-tables.head-cell>
        <x-tables.head-cell>Estoque Matriz</x-tables.head-cell>
        <x-tables.head-cell>Estoque Filial</x-tables.head-cell>
    </x-tables.header>
    <x-tables.body>
        @foreach ($transferencia as $result)
        <x-tables.row>
            <x-tables.cell>{{ $result->id_transferencia}}</x-tables.cell>
            <x-tables.cell>{{ $result->id_transferencia_item}}</x-tables.cell>
            <x-tables.cell>{{ $result->id_produto}} - {{ $result->descricao_produto}}</x-tables.cell>
            <x-tables.cell>{{ $result->qtde_produto}}</x-tables.cell>
            <x-tables.cell>{{ $result->qtd_baixa}}</x-tables.cell>
            <x-tables.cell>{{ $result->matriz->quantidade_produto}}</x-tables.cell>
            <x-tables.cell>{{ $result->qtde_produto}}</x-tables.cell>
        </x-tables.row>
        @endforeach
    </x-tables.body>
</x-tables.table>

<div class="mt-4">
    {{ $transferencia->links() }}
</div>