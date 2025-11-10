<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Movimentações de Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.estoque.itens', $item->id_estoque) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Informações do Item e Produto -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Informações do Produto -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-2">Informações do Produto</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-gray-500">ID do Produto:</span>
                                    <p class="font-medium">{{ $item->produto->id_produto ?? $item->id_produto }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Descrição:</span>
                                    <p class="font-medium">{{ $item->produto->descricao_produto ?? 'N/A' }}</p>
                                </div>
                                @if(isset($item->produto->codigo_produto))
                                <div>
                                    <span class="text-sm text-gray-500">Código:</span>
                                    <p class="font-medium">{{ $item->produto->codigo_produto }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Informações do Estoque -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-2">Informações do Estoque</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm text-gray-500">Estoque:</span>
                                    <p class="font-medium">{{ $item->estoque->descricao_estoque ?? 'N/A' }} (ID: {{
                                        $item->id_estoque }})</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Quantidade Atual:</span>
                                    <p class="font-medium">{{ number_format($item->quantidade_atual, 2, ',', '.') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Localização:</span>
                                    <p class="font-medium">{{ $item->localizacao ?? 'Não especificada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card com Resumo de Movimentações -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <!-- Total de Entradas -->
                        <div class="bg-green-50 border border-green-100 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-700">Total de Entradas</h3>
                                    @php
                                    $totalEntradas = $movimentacoes->where('tipo_movimento',
                                    'entrada')->sum('quantidade');
                                    @endphp
                                    <p class="text-lg font-semibold text-gray-900">{{ number_format($totalEntradas, 2,
                                        ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total de Saídas -->
                        <div class="bg-red-50 border border-red-100 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-700">Total de Saídas</h3>
                                    @php
                                    $totalSaidas = $movimentacoes->where('tipo_movimento', 'saida')->sum('quantidade');
                                    @endphp
                                    <p class="text-lg font-semibold text-gray-900">{{ number_format($totalSaidas, 2,
                                        ',', '.') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Transferências -->
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-700">Transferências</h3>
                                    @php
                                    $totalTransferencias = $movimentacoes->where('tipo_movimento',
                                    'transferencia')->count();
                                    @endphp
                                    <p class="text-lg font-semibold text-gray-900">{{ $totalTransferencias }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Última Movimentação -->
                        <div class="bg-purple-50 border border-purple-100 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-700">Última Movimentação</h3>
                                    @php
                                    $ultimaMovimentacao = $movimentacoes->sortByDesc('data_movimento')->first();
                                    @endphp
                                    @if($ultimaMovimentacao)
                                    <p class="text-sm font-semibold text-gray-900">{{
                                        $ultimaMovimentacao->data_movimento->format('d/m/Y H:i') }}</p>
                                    @else
                                    <p class="text-sm font-semibold text-gray-900">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET"
                        action="{{ route('admin.estoque.movimentacoes', [$item->id_estoque, $item->id_estoque_item]) }}"
                        class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="tipo_movimento" class="block text-sm font-medium text-gray-700">Tipo de
                                    Movimento</label>
                                <select name="tipo_movimento" id="tipo_movimento"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Todos</option>
                                    <option value="entrada" {{ request('tipo_movimento')=='entrada' ? 'selected' : ''
                                        }}>Entradas</option>
                                    <option value="saida" {{ request('tipo_movimento')=='saida' ? 'selected' : '' }}>
                                        Saídas</option>
                                    <option value="transferencia" {{ request('tipo_movimento')=='transferencia'
                                        ? 'selected' : '' }}>Transferências</option>
                                    <option value="ajuste" {{ request('tipo_movimento')=='ajuste' ? 'selected' : '' }}>
                                        Ajustes</option>
                                </select>
                            </div>

                            <div>
                                <label for="data_inicial" class="block text-sm font-medium text-gray-700">Data
                                    Inicial</label>
                                <input type="date" name="data_inicial" id="data_inicial"
                                    value="{{ request('data_inicial') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="data_final" class="block text-sm font-medium text-gray-700">Data
                                    Final</label>
                                <input type="date" name="data_final" id="data_final" value="{{ request('data_final') }}"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="flex items-end space-x-2">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Filtrar
                                </button>

                                <a href="{{ route('admin.estoque.movimentacoes', [$item->id_estoque, $item->id_estoque_item]) }}"
                                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Limpar
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Lista de Movimentações -->
                    <div class="overflow-x-auto relative">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Data/Hora
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantidade
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Origem/Destino
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Referência
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuário
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($movimentacoes as $movimento)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $movimento->id_movimento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $movimento->data_movimento->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($movimento->tipo_movimento == 'entrada')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Entrada
                                        </span>
                                        @elseif($movimento->tipo_movimento == 'saida')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Saída
                                        </span>
                                        @elseif($movimento->tipo_movimento == 'transferencia')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Transferência
                                        </span>
                                        @elseif($movimento->tipo_movimento == 'ajuste')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Ajuste
                                        </span>
                                        @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($movimento->tipo_movimento) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        @if($movimento->tipo_movimento == 'entrada' || ($movimento->tipo_movimento ==
                                        'ajuste' && $movimento->quantidade >= 0))
                                        <span class="font-medium text-green-600">+{{
                                            number_format($movimento->quantidade, 2, ',', '.') }}</span>
                                        @else
                                        <span class="font-medium text-red-600">-{{ number_format($movimento->quantidade,
                                            2, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($movimento->tipo_movimento == 'entrada')
                                        <span class="text-green-600">{{ $movimento->origem ?? 'N/A' }}</span>
                                        @elseif($movimento->tipo_movimento == 'saida')
                                        <span class="text-red-600">{{ $movimento->destino ?? 'N/A' }}</span>
                                        @elseif($movimento->tipo_movimento == 'transferencia')
                                        @if($movimento->origem == 'transferencia')
                                        <span class="text-blue-600">De: Outro Estoque</span>
                                        @else
                                        <span class="text-blue-600">Para: Outro Estoque</span>
                                        @endif
                                        @else
                                        <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($movimento->id_referencia)
                                        @if($movimento->tipo_movimento == 'entrada' && $movimento->origem == 'compra')
                                        <a href="{{ route('admin.compras.pedidos.show', $movimento->id_referencia) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            Pedido #{{ $movimento->id_referencia }}
                                        </a>
                                        @elseif($movimento->tipo_movimento == 'saida' && $movimento->destino ==
                                        'requisicao')
                                        <span class="text-gray-500">Requisição #{{ $movimento->id_referencia }}</span>
                                        @elseif($movimento->tipo_movimento == 'transferencia')
                                        <span class="text-gray-500">Item Estoque #{{ $movimento->id_referencia }}</span>
                                        @else
                                        <span class="text-gray-500">Ref #{{ $movimento->id_referencia }}</span>
                                        @endif
                                        @else
                                        <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $movimento->usuario->name ?? 'N/A' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Nenhuma movimentação encontrada para este item.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $movimentacoes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>