<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Estoque') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.estoque.dashboard') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 font-medium text-white transition-colors duration-150 hover:bg-indigo-700">

                    Painel de Controle
                </a>
                <x-forms.button href="{{ route('admin.estoque.create') }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Estoque
                </x-forms.button>
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela você pode visualizar todos os tipos de checklist. Utilize o botão 'Novo Tipo Checklist' para adicionar um novo registro. Você pode editar ou excluir tipos de checklist existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @include('admin.estoque._search-form')
            @include('admin.estoque._table')
        </div>
    </div>

</x-app-layout>