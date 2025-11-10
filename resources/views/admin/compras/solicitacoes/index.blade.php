<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Solicitações de Compra') }}
            </h2>
            <div class="flex items-center space-x-4">
                @can('create', App\Models\SolicitacaoCompra::class)
                    <a href="{{ route('admin.compras.solicitacoes.create') }}"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nova Solicitação
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="border-b border-gray-200 bg-white">
        <!-- Mensagens de Sucesso/Erro -->
        @if (session('success'))
            <div class="mx-4 mb-4 mt-4 border-l-4 border-green-500 bg-green-100 p-4 text-green-700" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-4 mb-4 mt-4 border-l-4 border-red-500 bg-red-100 p-4 text-red-700" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Abas de Tipo de Solicitação -->
        <div class="border-b border-gray-200 px-4 pt-4">
            <nav class="flex space-x-8" aria-label="Abas">
                <a href="{{ route('admin.compras.solicitacoes.index', array_merge(request()->except(['tipo_solicitacao', 'page']), [])) }}"
                    class="{{ !request('tipo_solicitacao') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Todas
                </a>
                <a href="{{ route('admin.compras.solicitacoes.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '1'])) }}"
                    class="{{ request('tipo_solicitacao') == '1' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Produtos
                </a>
                <a href="{{ route('admin.compras.solicitacoes.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '2'])) }}"
                    class="{{ request('tipo_solicitacao') == '2' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Serviços
                </a>
            </nav>
        </div>

        <!-- Formulário de Filtros -->
        <div class="p-4">
            @include('admin.compras.solicitacoes._search-form')
        </div>

        <!-- Tabela de Solicitações -->
        <div class="p-4">
            @include('admin.compras.solicitacoes._table')
        </div>
    </div>
    </div>

    <!-- Modais -->
    @include('admin.compras.solicitacoes._modals')

    @push('scripts')
        @include('admin.compras.solicitacoes._scripts')
    @endpush
</x-app-layout>
