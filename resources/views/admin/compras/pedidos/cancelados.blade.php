<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pedidos Cancelados') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Pedidos
                                    Cancelados</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela lista todos os pedidos de compra que foram cancelados ou rejeitados. Você
                                    pode consultar o histórico e os motivos de cancelamento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Filtros de busca -->
                <form action="{{ route('compras.pedidos.cancelados') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-forms.input name="numero" label="Número do Pedido" value="{{ request('numero') }}" />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_inicial" label="Data Inicial"
                                value="{{ request('data_inicial') }}" />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_final" label="Data Final"
                                value="{{ request('data_final') }}" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                placeholder="Selecione o fornecedor..." :options="$fornecedoresFrequentes ?? []"
                                :searchUrl="route('api.compras.fornecedores.search')"
                                :selected="request('id_fornecedor')" asyncSearch="true" />
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <div>
                            <x-ui.export-buttons route="compras.pedidos" :formats="['pdf', 'csv', 'xls']"
                                :params="['status' => 'cancelado']" />
                        </div>

                        <div>
                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabela de Pedidos -->
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Número
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fornecedor
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Valor
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Motivo
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($pedidos as $pedido)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('compras.pedidos.show', $pedido->id_pedido_compras) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        {{ $pedido->numero }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $pedido->data_inclusao?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap max-w-xs truncate"
                                    title="{{ $pedido->justificativa }}">
                                    {{ $pedido->justificativa ?? 'Não informado' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('compras.pedidos.show', $pedido->id_pedido_compras) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        @can('create', App\Models\PedidoCompra::class)
                                        <a href="{{ route('compras.pedidos.create', ['solicitacao_id' => $pedido->id_solicitacoes_compras]) }}"
                                            class="text-green-600 hover:text-green-900" title="Criar Novo Pedido">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Nenhum pedido cancelado encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pedidos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>