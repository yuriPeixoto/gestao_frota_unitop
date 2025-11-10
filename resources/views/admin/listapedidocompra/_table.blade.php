<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell></x-tables.head-cell>
            <x-tables.head-cell>Cód.Pedido</x-tables.head-cell>
            <x-tables.head-cell>Data</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Vlr Pedido</x-tables.head-cell>
            <x-tables.head-cell>Vlr Total com Desconto</x-tables.head-cell>
            <x-tables.head-cell>Nome Comprador</x-tables.head-cell>
            <x-tables.head-cell>Cód.Solicitado</x-tables.head-cell>
            <x-tables.head-cell>Situação Pedido</x-tables.head-cell>
        </x-tables.header>


        <x-tables.body>
            @forelse( $listagempedido as $list)
            <x-tables.row>
                <x-tables.cell content="Visualizar Pedido" placement="bottom">
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="abrirModalPedidoCompra({{ $list->id_pedido_compras }})"
                            class="p-1 hover:bg-gray-100 rounded" title="Visualizar Pedido">
                            <x-icons.eye class="w-5 h-5 text-blue-600 hover:text-gray-800" title="Ver Pedido" />
                        </button>
                        <a href="{{ route('admin.listapedidocompra.pdf', $list->id_pedido_compras) }}" target="_blank"
                            class="p-1 hover:bg-gray-100 rounded" title="Gerar PDF">
                            <x-icons.pdf-doc class="w-5 h-5 text-red-600 hover:text-red-800" title="Imprimir Pedido" />
                        </a>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{$list->id_pedido_compras}}</x-tables.cell>
                <x-tables.cell>{{$list->data_inclusao}}</x-tables.cell>
                <x-tables.cell>{{ $list->fornecedor->nome_fornecedor ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ number_format($list->itens->sum('valor_produto'), 2, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell>{{$list->valor_total}}</x-tables.cell>
                <x-tables.cell>{{ $list->comprador->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{$list->solicitacaoCompra->name ?? '-'}}</x-tables.cell>
                <x-tables.cell>{{$list->SituacaoPedido->descricao_situacao_pedido ?? '-'}}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>

    </x-tables.table>

    <div class="mt-4">
        {{ $listagempedido->links() }}
    </div>

    <div id="modalPedidoCompra" class="hidden fixed inset-0 z-40 bg-opacity-80 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
            <h2 class="text-xl font-semibold mb-4">Visualizar Pedido de Compra</h2>

            <div id="conteudoModalPedidoCompra" class="overflow-y-auto max-h-[70vh]">
                <p class="text-gray-500">Carregando...</p>
            </div>

            <button onclick="fecharModalPedidoCompra()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                Fechar
            </button>
        </div>
    </div>

    <script>
        function abrirModalPedidoCompra(id) {
        const modal = document.getElementById('modalPedidoCompra');
        const conteudo = document.getElementById('conteudoModalPedidoCompra');

        modal.classList.remove('hidden');
        conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

        fetch(` /admin/listapedidocompra/${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar dados');
                return response.text();
            })
            .then(html => {
                conteudo.innerHTML = html;
            })
            .catch(() => {
                conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar o pedido.</p>';
            });
    }

    function fecharModalPedidoCompra() {
        const modal = document.getElementById('modalPedidoCompra');
        modal.classList.add('hidden');
    }
    </script>


</div>