<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Cód. Transferência Item</x-tables.head-cell>
        <x-tables.head-cell>Produto</x-tables.head-cell>
        <x-tables.head-cell>Quantidade Solicitada</x-tables.head-cell>
        <x-tables.head-cell>Quantidade Recebida</x-tables.head-cell>
    </x-tables.header>
    <x-tables.body>
        @foreach ($transferencia->itens as $item)
        <x-tables.row>
            <x-tables.cell>{{ $item->id_transferencia_itens ?? '' }}</x-tables.cell>
            <x-tables.cell>{{ $item->id_produto }} - {{ $item->produto->descricao_produto ?? '' }}</x-tables.cell>
            <x-tables.cell>{{ $item->quantidade }}</x-tables.cell>
            <x-tables.cell>{{ $item->quantidade_baixa }}</x-tables.cell>
        </x-tables.row>
        @endforeach
    </x-tables.body>
</x-tables.table>