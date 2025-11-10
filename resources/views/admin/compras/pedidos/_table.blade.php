<div id="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Número</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Data</x-tables.head-cell>
            <x-tables.head-cell>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Valor Total</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Comprador</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($pedidos as $index => $pedido)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <a href="{{ route('admin.compras.pedidos.show', $pedido->id_pedido_compras) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            {{ $pedido->numero }}
                        </a>
                    </x-tables.cell>
                    <x-tables.cell>{{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $pedido->data_inclusao?->format('d/m/Y') }}</x-tables.cell>
                    <x-tables.cell>
                        @if ($pedido->solicitacaoCompra)
                            <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                {{ $pedido->solicitacaoCompra->numero_solicitacao }}
                            </a>
                        @else
                            N/A
                        @endif
                    </x-tables.cell>
                    <x-tables.cell nowrap>R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</x-tables.cell>
                    <x-tables.cell>
                        <span
                            class="{{ $pedido->statusClass }} inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                            {{ $pedido->status }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>{{ $pedido->comprador->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.compras.pedidos.show', $pedido->id_pedido_compras) }}"
                                class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-icons.eye class="h-3 w-3" />
                            </a>

                            @if ($pedido->podeSerEditado() && auth()->user()->can('update', $pedido))
                                <a href="{{ route('admin.compras.pedidos.edit', $pedido->id_pedido_compras) }}"
                                    class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            @endif

                            @if ($pedido->podeSerAprovado() && auth()->user()->can('approve', $pedido))
                                <a href="#" onclick="confirmarAprovacao('{{ $pedido->id_pedido_compras }}')"
                                    class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </a>
                            @endif

                            @if ($pedido->podeSerCancelado() && auth()->user()->can('cancel', $pedido))
                                <button type="button"
                                    onclick="confirmarCancelamento('{{ $pedido->id_pedido_compras }}')"
                                    class="inline-flex items-center rounded-full border border-transparent bg-red-600 p-1 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="8" message="Nenhum pedido de compra encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>
</div>
