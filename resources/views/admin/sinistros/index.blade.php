<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Sinistros') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-buttons.button-link href="{{ route('admin.sinistros.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Sinistro
                </x-buttons.button-link>
                <x-icons.help-icon title="Ajuda - Gerenciamento de Sinistros"
                    content="Nesta tela você pode visualizar todos os modelos de veículos cadastrados. Utilize o botão 'Novo Sinistro' para adicionar um novo registro. Você pode editar ou excluir modelos de veículos existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.sinistros._search-form')

                <!-- Results Table -->
                <div class="mt-6 overflow-x-auto" id="results-table">

                    <div class="mt-6 overflow-x-auto">
                        @include('admin.sinistros._table')
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            @include('admin.sinistros._scripts')
        @endpush
</x-app-layout>
