<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Veículos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.veiculos.create')}}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Veiculo
                </a>
                <x-help-icon title="Ajuda - Gerenciamento de veículos"
                    content="Nesta tela você pode visualizar todos veículos cadastrados. Utilize o botão 'Novo Veículo' para adicionar um novo registro. Você pode editar ou excluir veiculos existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>
    <x-bladewind::notification />

    <div class="bg-white overflow-hidden p-4 shadow-sm sm:rounded-lg">
        @include('admin.veiculos._search-form')
        <div class="mt-4">
            @include('admin.veiculos._table')
        </div>
    </div>

    @push('scripts')
    @include('admin.veiculos._scripts')
    @endpush
</x-app-layout>