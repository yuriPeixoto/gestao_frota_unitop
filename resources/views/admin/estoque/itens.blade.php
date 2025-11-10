<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestão de Itens de Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">


                <a href="{{ route('admin.estoque.index') }}"
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
                    <!-- Informações do Estoque -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Informações do Estoque</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">ID do Estoque:</span>
                                <p class="font-medium">{{ $estoque->id_estoque }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Descrição:</span>
                                <p class="font-medium">{{ $estoque->descricao_estoque }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Filial:</span>
                                <p class="font-medium">{{ $estoque->filial->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário para adicionar novo item -->
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-900 mb-2">Adicionar Novo Item ao Estoque</h3>
                        <form action="{{ route('admin.estoque.adicionar-item', $estoque->id_estoque) }}" method="POST"
                            class="bg-gray-50 p-4 rounded-lg">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <x-forms.smart-select name="id_produto" label="Produto"
                                        placeholder="Selecione um produto..." asyncSearch="false" :options="$produtos"
                                        :searchUrl="route('admin.api.produtos.search')" />
                                </div>


                                <div>
                                    <label for="quantidade_minima"
                                        class="block text-sm font-medium text-gray-700">Quantidade Mínima</label>
                                    <input type="number" name="quantidade_minima" id="quantidade_minima" min="0"
                                        step="0.01" required
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label for="quantidade_maxima"
                                        class="block text-sm font-medium text-gray-700">Quantidade Máxima</label>
                                    <input type="number" name="quantidade_maxima" id="quantidade_maxima" min="0"
                                        step="0.01"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label for="localizacao"
                                        class="block text-sm font-medium text-gray-700">Localização</label>
                                    <input type="text" name="localizacao" id="localizacao" maxlength="100"
                                        placeholder="Ex: Prateleira A, Corredor 3"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Adicionar Item
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" action="{{ route('admin.estoque.itens', $estoque->id_estoque) }}"
                        class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="busca" class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="busca" id="busca" value="{{ request('busca') }}"
                                    placeholder="ID ou descrição do produto"
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label for="estoque_baixo" class="block text-sm font-medium text-gray-700">Filtrar
                                    por</label>
                                <select name="estoque_baixo" id="estoque_baixo"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Todos os Itens</option>
                                    <option value="1" {{ request('estoque_baixo')=='1' ? 'selected' : '' }}>Apenas
                                        Estoque Baixo</option>
                                    <option value="zerados" {{ request('estoque_baixo')=='zerados' ? 'selected' : '' }}>
                                        Apenas Zerados</option>
                                    <option value="inativos" {{ request('estoque_baixo')=='inativos' ? 'selected' : ''
                                        }}>Inativos</option>
                                </select>
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

                                <a href="{{ route('admin.estoque.itens', $estoque->id_estoque) }}"
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

                    <!-- Lista de Itens -->
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
                                        Produto
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantidade Atual
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantidade Mínima
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Localização
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($itens as $item)
                                <tr class="{{ !$item->ativo ? 'bg-gray-100' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $item->id_estoque_item }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div
                                                    class="text-sm font-medium text-gray-900 {{ !$item->ativo ? 'line-through text-gray-500' : '' }}">
                                                    {{ $item->produto->descricao_produto ?? 'Produto
                                                    #'.$item->id_produto }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: {{ $item->id_produto }} {{ $item->produto->codigo_produto ? '|
                                                    Cód: '.$item->produto->codigo_produto : '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        @if($item->quantidade_atual <= $item->quantidade_minima &&
                                            $item->quantidade_atual > 0)
                                            <span class="font-medium text-yellow-600">{{
                                                number_format($item->quantidade_atual, 2, ',', '.') }}</span>
                                            @elseif($item->quantidade_atual <= 0) <span
                                                class="font-medium text-red-600">{{
                                                number_format($item->quantidade_atual, 2, ',', '.') }}</span>
                                                @else
                                                <span class="font-medium text-green-600">{{
                                                    number_format($item->quantidade_atual, 2, ',', '.') }}</span>
                                                @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                        {{ number_format($item->quantidade_minima, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if(!$item->ativo)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inativo
                                        </span>
                                        @elseif($item->quantidade_atual <= 0) <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Zerado
                                            </span>
                                            @elseif($item->quantidade_atual <= $item->quantidade_minima)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Estoque Baixo
                                                </span>
                                                @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Normal
                                                </span>
                                                @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->localizacao ?? 'Não definida' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            {{-- <a
                                                href="{{ route('admin.estoque.movimentacoes', [$estoque->id_estoque, $item->id_estoque_item]) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Movimentações">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </a>

                                            <button type="button"
                                                onclick="abrirModalAjuste({{ $item->id_estoque_item }}, {{ $item->quantidade_atual }})"
                                                class="text-yellow-600 hover:text-yellow-900"
                                                title="Ajuste de Inventário">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button> --}}

                                            <button type="button"
                                                onclick="abrirModalEditarItem({{ $item->id_estoque_item }}, {{ $item->quantidade_minima }}, {{ $item->quantidade_maxima ?? 'null' }}, '{{ $item->localizacao ?? '' }}', {{ $item->ativo ? 'true' : 'false' }})"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Editar Configuração">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </button>

                                            <form
                                                action="{{ route('admin.estoque.remover-item', [$estoque->id_estoque, $item->id_estoque_item]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Deseja realmente remover este item? Esta ação não poderá ser desfeita se o item tiver movimentações.');"
                                                class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Remover Item">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Nenhum item cadastrado neste estoque.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $itens->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Ajuste de Inventário -->
    <div class="fixed inset-0 overflow-y-auto hidden" id="modal-ajuste">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="" method="POST" id="form-ajuste">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Ajuste de Inventário
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Utilize este formulário para ajustar a quantidade em estoque deste item.
                                    </p>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="quantidade_atual_ajuste"
                                                class="block text-sm font-medium text-gray-700">Quantidade Atual</label>
                                            <input type="number" name="quantidade_atual" id="quantidade_atual_ajuste"
                                                step="0.01" required
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <p class="mt-1 text-xs text-gray-500" id="quantidade-atual-info">
                                                Quantidade registrada atualmente: <span
                                                    id="quantidade-atual-valor"></span>
                                            </p>
                                        </div>

                                        <div>
                                            <label for="motivo_ajuste"
                                                class="block text-sm font-medium text-gray-700">Motivo do Ajuste</label>
                                            <textarea name="motivo_ajuste" id="motivo_ajuste" rows="3" required
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                placeholder="Explique o motivo deste ajuste de inventário..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar Ajuste
                        </button>
                        <button type="button" onclick="fecharModalAjuste()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Item -->
    <div class="fixed inset-0 overflow-y-auto hidden" id="modal-editar-item">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="" method="POST" id="form-editar-item">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-edit">
                                    Editar Configurações do Item
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Atualize as configurações deste item de estoque.
                                    </p>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="quantidade_minima_edit"
                                                class="block text-sm font-medium text-gray-700">Quantidade
                                                Mínima</label>
                                            <input type="number" name="quantidade_minima" id="quantidade_minima_edit"
                                                min="0" step="0.01" required
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>

                                        <div>
                                            <label for="quantidade_maxima_edit"
                                                class="block text-sm font-medium text-gray-700">Quantidade
                                                Máxima</label>
                                            <input type="number" name="quantidade_maxima" id="quantidade_maxima_edit"
                                                min="0" step="0.01"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Deixe em branco para não definir um limite máximo.
                                            </p>
                                        </div>

                                        <div>
                                            <label for="localizacao_edit"
                                                class="block text-sm font-medium text-gray-700">Localização</label>
                                            <input type="text" name="localizacao" id="localizacao_edit" maxlength="100"
                                                placeholder="Ex: Prateleira A, Corredor 3"
                                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" name="ativo" id="ativo_edit" value="1"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="ativo_edit" class="ml-2 block text-sm text-gray-900">
                                                Item ativo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar Configurações
                        </button>
                        <button type="button" onclick="fecharModalEditarItem()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Modal de Ajuste de Inventário
        function abrirModalAjuste(idItem, quantidadeAtual) {
            const modal = document.getElementById('modal-ajuste');
            const form = document.getElementById('form-ajuste');
            const quantidadeInput = document.getElementById('quantidade_atual_ajuste');
            const quantidadeValor = document.getElementById('quantidade-atual-valor');
            
            // Configurar o formulário
            form.action = `/admin/estoque/{{ $estoque->id_estoque }}/${idItem}/ajuste-inventario`;
            quantidadeInput.value = quantidadeAtual;
            quantidadeValor.textContent = quantidadeAtual.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Mostrar o modal
            modal.classList.remove('hidden');
        }
        
        function fecharModalAjuste() {
            const modal = document.getElementById('modal-ajuste');
            modal.classList.add('hidden');
        }
        
        // Modal de Edição de Item
        function abrirModalEditarItem(idItem, quantidadeMinima, quantidadeMaxima, localizacao, ativo) {
            const modal = document.getElementById('modal-editar-item');
            const form = document.getElementById('form-editar-item');
            const quantidadeMinimaInput = document.getElementById('quantidade_minima_edit');
            const quantidadeMaximaInput = document.getElementById('quantidade_maxima_edit');
            const localizacaoInput = document.getElementById('localizacao_edit');
            const ativoCheckbox = document.getElementById('ativo_edit');
            
            // Configurar o formulário
            form.action = `/admin/{{ $estoque->id_estoque }}/itens/${idItem}`;
            quantidadeMinimaInput.value = quantidadeMinima;
            quantidadeMaximaInput.value = quantidadeMaxima !== 'null' ? quantidadeMaxima : '';
            localizacaoInput.value = localizacao;
            ativoCheckbox.checked = ativo;
            
            // Mostrar o modal
            modal.classList.remove('hidden');
        }
        
        function fecharModalEditarItem() {
            const modal = document.getElementById('modal-editar-item');
            modal.classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>