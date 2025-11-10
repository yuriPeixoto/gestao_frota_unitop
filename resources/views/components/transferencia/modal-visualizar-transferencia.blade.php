<form method="POST" action="{{ route('admin.transferenciaDiretoEstoque.solicitar', $id) }}">
    @csrf
    <div class="flex justify-end mb-2">
        <button type="submit" name="action" value="enviar"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Solicitar Transferência
        </button>
    </div>
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. Item</x-tables.head-cell>
            <x-tables.head-cell>Cód. Transferência</x-tables.head-cell>
            <x-tables.head-cell>Produto</x-tables.head-cell>
            <x-tables.head-cell>Qtde Pedido</x-tables.head-cell>
            <x-tables.head-cell>Qtde Baixa</x-tables.head-cell>

        </x-tables.header>
        <x-tables.body>
            @foreach ($transferencia as $result)
            <x-tables.row>
                <x-tables.cell>{{ $result->id_transferencia}}</x-tables.cell>
                <x-tables.cell>{{ $result->id_transferencia_item}}</x-tables.cell>
                <x-tables.cell>{{ $result->id_produto}} - {{ $result->descricao_produto}}</x-tables.cell>
                <x-tables.cell>{{ $result->qtde_produto}}</x-tables.cell>
                <x-tables.cell>{{ $result->qtd_baixa}}</x-tables.cell>

            </x-tables.row>
            @endforeach
        </x-tables.body>
    </x-tables.table>


</form>