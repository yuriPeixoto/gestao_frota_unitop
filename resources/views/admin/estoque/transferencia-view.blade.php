<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Transferências de Estoque') }}
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

            <!-- Abas -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('admin.estoque.visualizar-transferencia', ['tab' => 'transferidos'] + request()->query()) }}"
                        class="@if (!isset($activeTab) || $activeTab === 'transferidos') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                        Itens Transferidos
                    </a>
                    <a href="{{ route('admin.estoque.visualizar-transferencia', ['tab' => 'recebidos'] + request()->query()) }}"
                        class="@if (isset($activeTab) && $activeTab === 'recebidos') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap border-b-2 px-1 py-2 text-sm font-medium">
                        Itens Recebidos
                    </a>
                </nav>
            </div>

            <!-- Filtros -->
            <form method="GET" action="{{ route('admin.estoque.visualizar-transferencia') }}"
                class="mb-6 rounded-lg bg-gray-50 p-4">
                <input type="hidden" name="tab" value="{{ $activeTab ?? 'transferidos' }}">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <x-forms.smart-select name="id_protudos" label="Descrição Produto"
                            placeholder="Selecione o produto..." :options="$produtos" :selected="request('id_protudos')" />
                    </div>

                    <div>
                        <x-forms.smart-select name="filial_transferencia" label="Filial"
                            placeholder="Selecione a filial..." :options="$filiais" :selected="request('filial_transferencia')" />
                    </div>

                </div>

                <div class="mt-4 flex justify-between">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.estoque.visualizar-transferencia', ['tab' => $activeTab ?? 'transferidos']) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-icons.trash class="mr-2 h-4 w-4" />
                            Limpar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <x-icons.magnifying-glass class="mr-2 h-4 w-4" />
                            Buscar
                        </button>
                    </div>
                </div>
            </form>

            <!-- Conteúdo das Abas -->
            @if (!isset($activeTab) || $activeTab === 'transferidos')
                @include('admin.estoque.transferencia._tab_transferidos')
            @else
                @include('admin.estoque.transferencia._tab_recebidos')
            @endif
        </div>
    </div>
</x-app-layout>
