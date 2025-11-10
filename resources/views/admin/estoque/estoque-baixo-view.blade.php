<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Itens com Estoque Baixo') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.estoque.dashboard') }}"
                    class="inline-flex items-center rounded-md bg-gray-500 px-4 py-2 font-medium text-white transition-colors duration-150 hover:bg-gray-600">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-6">
            <!-- Alerta de Atenção -->
            <div class="mb-6 border-l-4 border-yellow-400 bg-yellow-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Esta página mostra todos os produtos da sua filial com suas informações de estoque.
                            Use os filtros para buscar produtos específicos e o campo <strong>Ordem</strong> para
                            organizar a lista.
                            Use o botão "Gerar Solicitações de Compra" para criar solicitações automaticamente para
                            itens com estoque baixo.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <form method="GET" action="{{ route('admin.estoque.estoque-baixo') }}"
                class="mb-6 rounded-lg bg-gray-50 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div>
                        <label for="id_estoque" class="block text-sm font-medium text-gray-700">Estoque</label>
                        <select name="id_estoque" id="id_estoque"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos os Estoques</option>
                            @foreach (\App\Models\Estoque::all() as $estoque)
                                <option value="{{ $estoque->id_estoque }}"
                                    {{ request('id_estoque') == $estoque->id_estoque ? 'selected' : '' }}>
                                    {{ $estoque->descricao_estoque }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id_produto" class="block text-sm font-medium text-gray-700">Produto</label>
                        <input type="text" name="id_produto" id="id_produto" value="{{ request('id_produto') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="ID ou código do produto">
                    </div>

                    <div>
                        <label for="ordem" class="block text-sm font-medium text-gray-700">Ordem</label>
                        <select name="ordem" id="ordem"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="desc" {{ request('ordem', 'desc') == 'desc' ? 'selected' : '' }}>Menor para
                                Maior</option>
                            <option value="asc" {{ request('ordem') == 'asc' ? 'selected' : '' }}>Maior para
                                Menor
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Filtrar
                        </button>
                    </div>

                    <div class="flex items-end">
                        <a href="{{ route('admin.estoque.estoque-baixo') }}"
                            class="inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpar
                        </a>
                    </div>
                </div>
            </form>

            <!-- Lista de Produtos com Informações de Estoque -->
            <div class="relative overflow-x-auto">
                @if ($itensBaixoEstoque->count() > 0)
                    <div class="mb-4 text-sm text-gray-600">
                        Mostrando {{ $itensBaixoEstoque->count() }} de {{ $itensBaixoEstoque->total() }} produto(s)
                        com
                        estoque baixo
                    </div>
                @endif

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Estoque
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                Qnt. Atual
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                Qnt. Mínima
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                Qnt. Máxima
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                Situação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($itensBaixoEstoque as $item)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->descricao_produto ?? 'Produto #' . $item->id_produto }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $item->id_produto }}
                                                {{ $item->codigo_produto ? ' | Cód: ' . $item->codigo_produto : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $item->estoque->descricao_estoque ?? 'N/A' }}
                                    <div class="text-xs">Estoque Atual</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                    <span
                                        class="font-medium text-red-600">{{ number_format($item->estoque_atual ?? 0, 2, ',', '.') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                                    {{ number_format($item->estoque_minimo ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                                    {{ $item->estoque_maximo ? number_format($item->estoque_maximo, 2, ',', '.') : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    @php
                                        $estoqueAtual = $item->estoque_atual ?? 0;
                                        $estoqueMinimo = $item->estoque_minimo ?? 0;
                                        $percentual = $estoqueMinimo > 0 ? ($estoqueAtual / $estoqueMinimo) * 100 : 0;
                                        $corBarra =
                                            $percentual < 50
                                                ? 'bg-red-500'
                                                : ($percentual < 80
                                                    ? 'bg-yellow-500'
                                                    : 'bg-green-500');
                                        $diferenca = $item->diferenca_estoque ?? 0;
                                    @endphp
                                    <div class="mb-1 h-2.5 w-full rounded-full bg-gray-200">
                                        <div class="{{ $corBarra }} h-2.5 rounded-full"
                                            style="width: {{ min($percentual, 100) }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ number_format($percentual, 0) }}% do mínimo
                                        @if ($diferenca > 0)
                                            <br><span class="text-red-600">Falta:
                                                {{ number_format($diferenca, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $itensBaixoEstoque->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
