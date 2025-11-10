<x-app-layout>

    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Lista de Cotações') }}
            </h2>
        </div>
    </x-slot>

    <div class="border-b border-gray-200 bg-white">
        <!-- Mensagens de Sucesso/Erro -->
        @if ($errors->any())
            <div class="mx-4 mb-4 mt-4 rounded bg-red-50 p-4">
                <ul class="list-inside list-disc text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Abas de Tipo de Solicitação -->
        <div class="border-b border-gray-200 px-4 pt-4">
            <nav class="flex space-x-8" aria-label="Abas">
                <a href="{{ route('admin.compras.cotacoes.index', array_merge(request()->except(['tipo_solicitacao', 'page']), [])) }}"
                    class="{{ !request('tipo_solicitacao') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Todas
                </a>
                <a href="{{ route('admin.compras.cotacoes.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '1'])) }}"
                    class="{{ request('tipo_solicitacao') == '1' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Produtos
                </a>
                <a href="{{ route('admin.compras.cotacoes.index', array_merge(request()->except(['page']), ['tipo_solicitacao' => '2'])) }}"
                    class="{{ request('tipo_solicitacao') == '2' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                    Serviços
                </a>
            </nav>
        </div>

        <!-- Formulário de Filtros -->
        <div class="p-4">
            @include('admin.compras.cotacoes._search-form')
        </div>

        <!-- Tabela de Cotações -->
        <div class="p-4">
            @include('admin.compras.cotacoes._table')
        </div>
    </div>

    <!-- Modais (se houver) -->
    {{-- @include('admin.compras.cotacoes._modals') --}}

    @push('scripts')
        @include('admin.compras.cotacoes._scripts')
    @endpush
</x-app-layout>
