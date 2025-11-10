<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Saldo Estoque Combustível') }}
            </h2>
            <div class="flex items-center space-x-4">
                @can('editar_estoquecombustivel')
                <a href="{{ route('admin.estoque-combustivel.refresh') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-icons.refresh class="w-4 h-4 mr-2" />
                    Atualizar Dados
                </a>
                @endcan

                <x-help-icon title="Ajuda - Saldo Estoque Combustível"
                    content="Este dashboard apresenta o saldo atual de combustível em cada tanque por filial, com seus respectivos percentuais de capacidade. Utilize os filtros para visualizar o histórico de entradas e saídas." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <!-- Filtros -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <form method="GET" action="{{ route('admin.estoque-combustivel.dashboard') }}"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="data_inicio" :value="__('Data Início')" />
                        <x-text-input id="data_inicio" class="block mt-1 w-full" type="date" name="data_inicio"
                            :value="$filtros['data_inicio'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="data_fim" :value="__('Data Fim')" />
                        <x-text-input id="data_fim" class="block mt-1 w-full" type="date" name="data_fim"
                            :value="$filtros['data_fim'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="data_especifica" :value="__('Data Específica')" />
                        <x-text-input id="data_especifica" class="block mt-1 w-full" type="date" name="data_especifica"
                            :value="$filtros['data_especifica'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="tipo_movimentacao" :value="__('Tipo Movimentação')" />
                        <select id="tipo_movimentacao" name="tipo_movimentacao"
                            class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos</option>
                            <option value="entrada" {{ ($filtros['tipo_movimentacao'] ?? '' )=='entrada' ? 'selected'
                                : '' }}>Entrada</option>
                            <option value="saida" {{ ($filtros['tipo_movimentacao'] ?? '' )=='saida' ? 'selected' : ''
                                }}>Saída</option>
                        </select>
                    </div>
                    @if(Auth::user()->isSuperuser())
                    <div>
                        <x-input-label for="filial_id" :value="__('Filial')" />
                        <select id="filial_id" name="filial_id"
                            class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todas</option>
                            @foreach($filiais as $filial)
                            <option value="{{ $filial->id }}" {{ ($filtros['filial_id'] ?? '' )==$filial->id ?
                                'selected'
                                : '' }}>{{ $filial->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="flex items-end {{ Auth::user()->isSuperuser() ? '' : 'md:col-span-2' }}">
                        <x-primary-button
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Filtrar') }}
                        </x-primary-button>

                        @if(!empty($filtros['data_inicio']) || !empty($filtros['data_fim']) ||
                        !empty($filtros['tipo_movimentacao']) || !empty($filtros['data_especifica']) ||
                        !empty($filtros['filial_id']))
                        <a href="{{ route('admin.estoque-combustivel.dashboard') }}"
                            class="inline-flex items-center px-4 py-2 ml-3 bg-white border border-gray-300 rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-gray-100 focus:bg-gray-100 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Limpar Filtros') }}
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabela de movimentações quando filtro estiver ativo -->
            @if(!empty($movimentacoes) && count($movimentacoes) > 0)
            <div class="mb-6 overflow-x-auto">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold">Histórico de Movimentações</h3>

                    {{-- Botões de exportação - Protegidos por permissão --}}
                    @can('ver_estoquecombustivel')
                    <div>
                        <x-ui.export-buttons route="admin.estoque-combustivel" :formats="['pdf', 'xls', 'csv']"
                            :routeParams="$filtros" />
                    </div>
                    @endcan
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Data
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'data_alteracao', 'order_direction' => ($filtros['order_by'] == 'data_alteracao' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'data_alteracao')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Filial
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'nome_filial', 'order_direction' => ($filtros['order_by'] == 'nome_filial' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'nome_filial')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Tanque
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'tanque', 'order_direction' => ($filtros['order_by'] == 'tanque' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'tanque')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Combustível
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'tipo_combustivel', 'order_direction' => ($filtros['order_by'] == 'tipo_combustivel' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'tipo_combustivel')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Quantidade Anterior
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'quantidade_anterior', 'order_direction' => ($filtros['order_by'] == 'quantidade_anterior' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'quantidade_anterior')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Quantidade Atual
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'quantidade_em_estoque', 'order_direction' => ($filtros['order_by'] == 'quantidade_em_estoque' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'quantidade_em_estoque')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    Diferença
                                    <a href="{{ route('admin.estoque-combustivel.dashboard', array_merge($filtros, ['order_by' => 'diferenca', 'order_direction' => ($filtros['order_by'] == 'diferenca' && $filtros['order_direction'] == 'asc') ? 'desc' : 'asc'])) }}"
                                        class="ml-1">
                                        @if($filtros['order_by'] == 'diferenca')
                                        @if($filtros['order_direction'] == 'asc')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4" />
                                        </svg>
                                        @endif
                                    </a>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movimentacoes as $mov)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($mov->data_alteracao)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $mov->nome_filial }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $mov->tanque }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $mov->tipo_combustivel }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($mov->quantidade_anterior, 2, ',', '.') }} L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($mov->quantidade_em_estoque, 2, ',', '.') }} L
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm {{ $mov->diferenca > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format(abs($mov->diferenca), 2, ',', '.') }} L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mov->diferenca > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $mov->diferenca > 0 ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif(!empty($filtros['data_inicio']) || !empty($filtros['data_fim']) ||
            !empty($filtros['data_especifica']) || !empty($filtros['filial_id']))
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg text-center text-yellow-800">
                Nenhuma movimentação encontrada para os filtros selecionados.
            </div>
            @endif

            <!-- Visualização de saldo atual -->
            <div class="p-6 bg-white">
                <h3 class="text-lg font-semibold mb-4">Saldo Atual</h3>
                @foreach($tanks as $locationKey => $location)
                <div class="mb-8">
                    <div class="flex items-center mb-4">
                        <x-icons.gas-pump class="w-6 h-6 mr-2" color="{{ $location['icon_color'] }}" />
                        <h3 class="text-xl font-bold" style="color: {{ $location['icon_color'] }}">{{ $location['name']
                            }}</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($location['tanks'] as $tank)
                        <x-ui.fuel-gauge :title="$tank['display_name']" :value="$tank['current_amount']"
                            :max="$tank['capacity']" :percentage="$tank['percentage']" :color="$location['icon_color']"
                            :last-updated="$tank['last_updated']" />
                        @endforeach
                    </div>
                </div>

                @if(!$loop->last)
                <div class="border-t border-gray-200 my-6"></div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>