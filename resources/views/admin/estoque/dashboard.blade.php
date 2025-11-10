<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Dashboard de Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">

                <a href="{{ route('admin.estoque.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 font-medium text-white transition-colors duration-150 hover:bg-indigo-700">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Estoque
                </a>
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="absolute right-0 mt-2 w-64 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="truncate text-sm font-medium leading-5 text-gray-900">Ajuda - Gestão de
                                    Estoque</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar o status do estoque, consultar itens com estoque
                                    baixo,
                                    realizar movimentações e verificar os pedidos pendentes relacionados ao estoque.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-6">
            <!-- Cards de Status -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-red-100 p-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Itens com Estoque Baixo</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $itensBaixoEstoque ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-blue-100 p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Solicitações Pendentes</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $solicitacoesPendentes ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-yellow-100 p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Pedidos Pendentes</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $pedidosPendentes ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-green-100 p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-500">Total de Estoques</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $estoques->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <a href="{{ route('admin.estoque.estoque-baixo') }}"
                    class="rounded-lg border bg-white p-4 shadow-sm transition-colors duration-150 hover:bg-gray-50">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-red-100 p-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-md font-medium text-gray-900">Verificar Estoque Baixo</h3>
                            <p class="text-sm text-gray-500">Ver todos os itens com estoque abaixo do mínimo</p>
                        </div>
                    </div>
                </a>

                <form action="{{ route('admin.estoque.visualizar-transferencia') }}" method="POST"
                    class="cursor-pointer rounded-lg border bg-white p-4 shadow-sm transition-colors duration-150 hover:bg-gray-50"
                    onclick="event.target.closest('form').submit()">
                    @csrf
                    <div class="flex items-center">
                        <div class="flex-shrink-0 rounded-full bg-yellow-100 p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-md font-medium text-gray-900">Visualizar Transferências</h3>
                            <p class="text-sm text-gray-500">Visualizar todos os itens em transferência</p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filtros -->
            <form method="GET" action="{{ route('admin.estoque.index') }}" class="mb-6 rounded-lg bg-gray-50 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label for="descricao_estoque" class="block text-sm font-medium text-gray-700">Descrição</label>
                        <input type="text" name="descricao_estoque" id="descricao_estoque"
                            value="{{ request('descricao_estoque') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
                        <select name="id_filial" id="id_filial"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todas as Filiais</option>
                            @foreach ($filiais as $filial)
                            <option value="{{ $filial['value'] }}" {{ request('id_filial')==$filial['value']
                                ? 'selected' : '' }}>
                                {{ $filial['label'] }}
                            </option>
                            @endforeach
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
                        <a href="{{ route('admin.estoque.index') }}"
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

            <!-- Status dos Pedidos em Tempo Real -->
            <div class="mb-6">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Status de Pedidos</h3>
                <div class="rounded-lg bg-gray-50 p-4" id="status-pedidos">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Solicitações Pendentes -->
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="border-b border-blue-100 bg-blue-50 px-4 py-3 font-medium text-blue-700">
                                Solicitações Pendentes
                            </div>
                            <div class="p-4">
                                @if ($solicitacoesPendentes > 0)
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-gray-600">Novas</span>
                                    <span class="font-medium">{{ SolicitacaoCompra::where('status', 'nova')->count()
                                        }}</span>
                                </div>
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-gray-600">Em Análise</span>
                                    <span class="font-medium">{{ SolicitacaoCompra::where('status',
                                        'em_analise')->count() }}</span>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.compras.solicitacoes.index') }}"
                                        class="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <span>Ver todas</span>
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @else
                                <p class="py-4 text-center text-gray-500">Nenhuma solicitação pendente</p>
                                @endif
                            </div>
                        </div>

                        <!-- Pedidos Aguardando Aprovação -->
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="border-b border-yellow-100 bg-yellow-50 px-4 py-3 font-medium text-yellow-700">
                                Pedidos Aguardando Aprovação
                            </div>
                            <div class="p-4">
                                @if ($pedidosPendentes > 0)
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-gray-600">Rascunho</span>
                                    <span class="font-medium">{{ PedidoCompra::where('status', 'rascunho')->count()
                                        }}</span>
                                </div>
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-gray-600">Aguardando Aprovação</span>
                                    <span class="font-medium">{{ PedidoCompra::where('status',
                                        'aguardando_aprovacao')->count() }}</span>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.compras.pedidos.index') }}"
                                        class="flex items-center text-sm text-yellow-600 hover:text-yellow-800">
                                        <span>Ver todos</span>
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @else
                                <p class="py-4 text-center text-gray-500">Nenhum pedido aguardando aprovação</p>
                                @endif
                            </div>
                        </div>

                        <!-- Itens com Estoque Baixo -->
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="border-b border-red-100 bg-red-50 px-4 py-3 font-medium text-red-700">
                                Itens com Estoque Baixo
                            </div>
                            <div class="p-4">
                                @if ($itensBaixoEstoque > 0)
                                <ul class="divide-y divide-gray-200">
                                    @php
                                    $itensAmostra = App\Models\EstoqueItem::estoqueBaixo()
                                    ->with(['produto', 'estoque'])
                                    ->limit(3)
                                    ->get();
                                    @endphp

                                    @foreach ($itensAmostra as $item)
                                    <li class="py-2">
                                        <div class="text-sm">
                                            <p class="truncate font-medium text-gray-800">
                                                {{ $item->produto->descricao_produto ?? 'Produto #' . $item->id_produto
                                                }}
                                            </p>
                                            <p class="text-gray-500">
                                                Atual: <span class="font-medium text-red-600">{{ $item->quantidade_atual
                                                    }}</span>
                                                /
                                                Min: {{ $item->quantidade_minima }}
                                            </p>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ route('admin.estoque.estoque-baixo') }}"
                                        class="flex items-center text-sm text-red-600 hover:text-red-800">
                                        <span>Ver todos</span>
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @else
                                <p class="py-4 text-center text-gray-500">Nenhum item com estoque baixo</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listagem de Estoques -->
            <div>
                <h3 class="mb-4 text-lg font-medium text-gray-900">Estoques</h3>
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    ID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Descrição
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Filial
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Itens
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($estoques as $estoque)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $estoque->id_estoque }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $estoque->descricao_estoque }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $estoque->filial->name ?? 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $estoque->itens_count ?? '0' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php
                                    $itensBaixo = App\Models\EstoqueItem::where(
                                    'id_estoque',
                                    $estoque->id_estoque,
                                    )
                                    ->whereRaw('quantidade_atual <= quantidade_minima') ->count();
                                        @endphp

                                        @if ($itensBaixo > 0)
                                        <span
                                            class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">
                                            {{ $itensBaixo }} itens com estoque baixo
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                            Estoque normal
                                        </span>
                                        @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.estoque.show', $estoque->id_estoque) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.estoque.itens', $estoque->id_estoque) }}"
                                            class="text-green-600 hover:text-green-900"
                                            title="Vincular Itens ao Tipo de Estoque">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </a>

                                        <a href="{{route('admin.estoque.transferir', $estoque->id_estoque)}}"
                                            class="text-yellow-600 hover:text-yellow-900" title="Transferir">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.estoque.edit', $estoque->id_estoque) }}"
                                            class="text-gray-600 hover:text-gray-900" title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum estoque cadastrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $estoques->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Função para atualizar o status dos pedidos em tempo real (exemplo)
            function atualizarStatusPedidos() {
                // Aqui poderíamos implementar um AJAX para buscar dados atualizados
                // periodicamente ou usar WebSockets para atualizações em tempo real
                console.log('Atualizando status de pedidos...');

                // Exemplo de AJAX:
                /*
                fetch('/admin/api/status-pedidos')
                    .then(response => response.json())
                    .then(data => {
                        // Atualizar os números no painel
                        const elementoSolicitacoes = document.getElementById('count-solicitacoes');
                        if (elementoSolicitacoes) {
                            elementoSolicitacoes.textContent = data.solicitacoesPendentes;
                        }

                        // E assim por diante para os outros contadores
                    })
                    .catch(error => console.error('Erro ao atualizar status:', error));
                */
            }

            // Atualizar a cada 60 segundos
            setInterval(atualizarStatusPedidos, 60000);

            // Chamar uma vez quando a página carrega
            document.addEventListener('DOMContentLoaded', atualizarStatusPedidos);
    </script>
    @endpush
</x-app-layout>