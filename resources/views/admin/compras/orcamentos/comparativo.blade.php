<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Comparativo de Orçamentos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.orcamentos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Informações do Pedido -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="bg-indigo-50 px-4 py-3 border-b border-indigo-200">
                    <h3 class="text-lg font-medium text-indigo-800">Informações do Pedido</h3>
                </div>
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Número do Pedido</p>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $pedido->numero_pedido }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Solicitante</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $pedido->solicitacaoCompra->solicitante->name ??
                                'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Data do Pedido</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $pedido->data_pedido->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <a href="{{ route('admin.compras.pedidos.show', $pedido->id_pedido) }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Ver Pedido Completo
                        </a>
                        <a href="{{ route('admin.compras.orcamentos.create', ['pedido_id' => $pedido->id_pedido]) }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Adicionar Orçamento
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabela de Comparativo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800">Comparativo de Orçamentos</h3>
                </div>
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(count($fornecedores) > 0 && count($comparativo) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50"
                                        style="min-width: 180px; max-width: 200px;">
                                        Item / Fornecedor
                                    </th>
                                    @foreach($fornecedores as $fornecedorId => $fornecedor)
                                    <th scope="col"
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        style="min-width: 180px;">
                                        <div class="flex flex-col">
                                            <span>{{ $fornecedor['nome'] }}</span>
                                            <span class="text-xs font-normal text-gray-400 mt-1">Prazo: {{
                                                $fornecedor['prazo_entrega'] }} dias</span>
                                            <span class="text-xs font-normal text-gray-400">Validade: {{
                                                $fornecedor['validade'] }}</span>

                                            <div class="mt-2">
                                                @if($fornecedor['selecionado'])
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Selecionado
                                                </span>
                                                @else
                                                <form method="POST"
                                                    action="{{ route('admin.compras.orcamentos.selecionar', $fornecedorId) }}"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-indigo-500"
                                                        onclick="return confirm('Tem certeza que deseja selecionar este orçamento como vencedor?')">
                                                        Selecionar
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                $totalPorFornecedor = [];
                                foreach($fornecedores as $fId => $f) {
                                $totalPorFornecedor[$fId] = 0;
                                }
                                @endphp

                                @foreach($comparativo as $itemPedidoId => $item)
                                <tr>
                                    <td class="px-4 py-4 sticky left-0 bg-white"
                                        style="min-width: 180px; max-width: 200px;">
                                        <div class="text-sm text-gray-900 font-medium">{{ $item['descricao'] }}</div>
                                        <div class="text-xs text-gray-500">Qtd: {{ number_format($item['quantidade'], 2,
                                            ',', '.') }} {{ $item['unidade_medida'] }}</div>
                                    </td>

                                    @foreach($fornecedores as $fornecedorId => $fornecedor)
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if(isset($item['fornecedores'][$fornecedorId]))
                                        @php
                                        $valorItem = $item['fornecedores'][$fornecedorId]['valor_total'];
                                        $totalPorFornecedor[$fornecedorId] += $valorItem;

                                        // Identificar o menor preço para este item
                                        $precos = [];
                                        foreach($item['fornecedores'] as $f) {
                                        $precos[] = $f['valor_unitario'];
                                        }
                                        $menorPreco = min($precos);
                                        $ehMenorPreco = $item['fornecedores'][$fornecedorId]['valor_unitario'] ==
                                        $menorPreco;
                                        @endphp

                                        <div
                                            class="text-sm font-medium {{ $ehMenorPreco ? 'text-green-600' : 'text-gray-900' }}">
                                            R$ {{ number_format($item['fornecedores'][$fornecedorId]['valor_unitario'],
                                            2, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Total: R$ {{
                                            number_format($item['fornecedores'][$fornecedorId]['valor_total'], 2, ',',
                                            '.') }}
                                        </div>
                                        @if($ehMenorPreco)
                                        <span
                                            class="px-2 mt-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Menor preço
                                        </span>
                                        @endif
                                        @else
                                        <span class="text-sm text-gray-400">Não orçado</span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach

                                <!-- Linha de totais -->
                                <tr class="bg-gray-50 font-medium">
                                    <td class="px-4 py-4 sticky left-0 bg-gray-50">
                                        <div class="text-sm text-gray-900 font-medium">Valor Total</div>
                                    </td>

                                    @foreach($fornecedores as $fornecedorId => $fornecedor)
                                    @php
                                    // Identificar o menor preço total
                                    $menorTotal = min($totalPorFornecedor);
                                    $ehMenorTotal = $totalPorFornecedor[$fornecedorId] == $menorTotal &&
                                    $totalPorFornecedor[$fornecedorId] > 0;
                                    @endphp

                                    <td
                                        class="px-4 py-4 whitespace-nowrap text-sm font-medium {{ $ehMenorTotal ? 'text-green-600' : 'text-gray-900' }}">
                                        R$ {{ number_format($totalPorFornecedor[$fornecedorId], 2, ',', '.') }}

                                        @if($ehMenorTotal)
                                        <div>
                                            <span
                                                class="px-2 mt-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Melhor oferta
                                            </span>
                                        </div>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="flex justify-center items-center py-8">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum orçamento cadastrado</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Não há orçamentos cadastrados para este pedido de compra.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('admin.compras.orcamentos.create', ['pedido_id' => $pedido->id_pedido]) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Cadastrar Orçamento
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Exportar Comparativo -->
            @if(count($fornecedores) > 0 && count($comparativo) > 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Exportar Comparativo</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.compras.orcamentos.export-comparativo', ['pedido_id' => $pedido->id_pedido, 'format' => 'pdf']) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                PDF
                            </a>
                            <a href="{{ route('admin.compras.orcamentos.export-comparativo', ['pedido_id' => $pedido->id_pedido, 'format' => 'excel']) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>