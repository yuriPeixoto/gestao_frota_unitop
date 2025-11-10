<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes do Orçamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.orcamentos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alertas -->
            @if(session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif

            @if(session('warning'))
            <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p>{{ session('warning') }}</p>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Cabeçalho com informações principais e botões de ação -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Orçamento #{{ $orcamento->id_orcamento }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Pedido: <a href="{{ route('admin.compras.pedidos.show', $orcamento->id_pedido) }}"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    {{ $orcamento->pedidoCompra->numero_pedido ?? 'N/A' }}
                                </a>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            @if(!$orcamento->selecionado)
                            @can('update', $orcamento)
                            <a href="{{ route('admin.compras.orcamentos.edit', $orcamento->id_orcamento) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Editar
                            </a>
                            @endcan

                            @can('select', $orcamento)
                            <form method="POST"
                                action="{{ route('admin.compras.orcamentos.selecionar', $orcamento->id_orcamento) }}"
                                class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    onclick="return confirm('Tem certeza que deseja selecionar este orçamento? Isso definirá o fornecedor para o pedido de compra.')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selecionar
                                </button>
                            </form>
                            @endcan

                            @can('reject', $orcamento)
                            @include('admin.compras.orcamentos._modal-rejeitar')
                            @endcan

                            @can('delete', $orcamento)
                            <form method="POST"
                                action="{{ route('admin.compras.orcamentos.destroy', $orcamento->id_orcamento) }}"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    onclick="return confirm('Tem certeza que deseja excluir este orçamento?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Excluir
                                </button>
                            </form>
                            @endcan
                            @else
                            <span
                                class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Orçamento Selecionado
                            </span>
                            @endif

                            <!-- Link para ver orçamentos do mesmo pedido -->
                            <a href="{{ route('admin.compras.orcamentos.comparativo', ['pedido_id' => $orcamento->id_pedido]) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Ver Comparativo
                            </a>
                        </div>
                    </div>

                    <!-- Detalhes do Orçamento -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Informações do Orçamento</h3>
                        <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Fornecedor</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $orcamento->fornecedor->nome_fornecedor ??
                                        'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Data do Orçamento</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $orcamento->data_orcamento ?
                                        $orcamento->data_orcamento->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Data de Inclusão</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $orcamento->data_inclusao ?
                                        $orcamento->data_inclusao->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Valor Total</p>
                                    <p class="mt-1 text-sm text-gray-900">R$ {{ number_format($orcamento->valor_total,
                                        2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Prazo de Entrega</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $orcamento->prazo_entrega }} dias</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Validade</p>
                                    <p
                                        class="mt-1 text-sm text-gray-900 {{ $orcamento->validade && $orcamento->validade < now() ? 'text-red-600' : '' }}">
                                        {{ $orcamento->validade ? $orcamento->validade->format('d/m/Y') : 'N/A' }}
                                        @if($orcamento->validade && $orcamento->validade < now()) (Vencido) @endif </p>
                                </div>
                                <div class="sm:col-span-3">
                                    <p class="text-sm font-medium text-gray-500">Observação</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $orcamento->observacao ?: 'Sem observações'
                                        }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status do Orçamento -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Status do Orçamento</h3>
                        <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full {{ $orcamento->selecionado ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} text-xs font-medium">
                                    {{ $orcamento->selecionado ? 'Selecionado' : 'Não Selecionado' }}
                                </span>

                                @if($orcamento->rejeitado)
                                <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-medium">
                                    Rejeitado
                                </span>
                                @endif

                                @if($orcamento->validade && $orcamento->validade < now()) <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                                    Validade Expirada
                                    </span>
                                    @endif
                            </div>

                            @if($orcamento->rejeitado && $orcamento->motivo_rejeicao)
                            <div class="mt-3 bg-red-50 p-3 rounded-md">
                                <h4 class="text-sm font-medium text-red-800">Motivo da Rejeição:</h4>
                                <p class="mt-1 text-sm text-red-700">{{ $orcamento->motivo_rejeicao }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Itens do Orçamento -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Itens do Orçamento</h3>
                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantidade
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Unitário
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($orcamento->itens as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $item->itemPedidoCompra->descricao ?? 'Item não encontrado' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $item->itemPedidoCompra->observacao ?? '' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($item->quantidade,
                                                    2, ',', '.') }}</div>
                                                <div class="text-xs text-gray-500">{{
                                                    $item->itemPedidoCompra->unidade_medida ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">R$ {{
                                                    number_format($item->valor_unitario, 2, ',', '.') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">R$ {{
                                                    number_format($item->valor_total, 2, ',', '.') }}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3"
                                                class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                                Total
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>