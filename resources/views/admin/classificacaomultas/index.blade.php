<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Classificação Multas') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.classificacaomultas.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nova Classificação de Multa
                </x-button-link>
                <x-help-icon title="Ajuda - Cadastro de Classificação de multas"
                    content="Nesta tela você pode visualizar todos as multas cadastrados. Utilize o botão 'Nova Classificação Multa' para adicionar um novo registro. Você pode editar ou excluir multas existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            @if (session('notification'))
            <x-notification :notification="session('notification')" />
            @endif

            <div class="p-6 bg-white border-b border-gray-200 flex flex-col space-y-4">
                <!-- Search Form -->
                <div>
                    @include('admin.classificacaomultas._search-form')
                </div>

                <!-- Results Table -->
                <div>
                    @include('admin.classificacaomultas._table')
                </div>
            </div>
        </div>
    </div>

    @include('admin.classificacaomultas._scripts')
</x-app-layout>