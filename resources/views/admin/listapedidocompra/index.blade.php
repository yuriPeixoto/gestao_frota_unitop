<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lista de Pedidos de Compras') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div></div>
                <x-help-icon title="Ajuda - Gerenciamento de veículos"
                    content="Nesta tela você pode visualizar todos veículos cadastrados. Utilize o botão 'Novo Veículo' para adicionar um novo registro. Você pode editar ou excluir veiculos existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>
    <x-bladewind::notification />

    <div class="bg-white overflow-hidden p-4 shadow-sm sm:rounded-lg">
        @include('admin.listapedidocompra._search-form')
        <div class="mt-4">
            @include('admin.listapedidocompra._table')
        </div>
    </div>

    @push('scripts')
    @include('admin.listapedidocompra._scripts')
    @endpush
</x-app-layout>