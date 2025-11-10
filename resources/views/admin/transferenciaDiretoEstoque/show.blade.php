<x-app-layout>


    <div class="bg-white  overflow-hidden p-6 shadow-sm sm:rounded-lg">
        <div class="w-full flex justify-between itens-center">
            <div class="w-10/12">
                <form method="GET" action="{{ route('admin.transferenciaDiretoEstoque.visualizar', $id) }}">
                    <div class="flex items-center gap-2">

                        <button type="submit"
                            class="px-4 py-2 mb-4 h-12 text-white bg-blue-500 rounded hover:bg-blue-600">
                            Enviar Transferência
                        </button>
                    </div>
                </form>
            </div>
            <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
                class="px-4 pt-3 mb-4 h-12 text-white bg-blue-500 rounded hover:bg-blue-600">
                voltar
            </a>
        </div>
        <x-tables.table>

            <x-tables.header>
                <x-tables.head-cell>Cód. Item</x-tables.head-cell>
                <x-tables.head-cell>Cód. Transferência</x-tables.head-cell>
                <x-tables.head-cell>Produto</x-tables.head-cell>
                <x-tables.head-cell>Qtde Pedido</x-tables.head-cell>
                <x-tables.head-cell>Qtde Baixa</x-tables.head-cell>
                <x-tables.head-cell>Estoque Matriz</x-tables.head-cell>
                <x-tables.head-cell>Estoque Filial</x-tables.head-cell>
                <x-tables.body>
                    @forelse ($transferencia as $result)
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
            </x-tables.header>
        </x-tables.table>
        <div class="mt-4">
            {{ $transferencia->links() }}
        </div>
    </div>
</x-app-layout>

<script>


</script>