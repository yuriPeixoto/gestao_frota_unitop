<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ajuste Estoque') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-forms.button href="{{ route('admin.ajusteEstoque.create') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Ajuste
                </x-forms.button>
                <x-help-icon title="Ajuda - Gerenciamento de Filiais"
                    content="Nesta tela você pode visualizar todas as filiais cadastradas. Utilize o botão 'Nova Filial' para adicionar um novo registro. Você pode editar ou excluir filiais existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @include('admin.ajusteEstoque._search-form')
            @include('admin.ajusteEstoque._table')
        </div>
    </div>
</x-app-layout>
