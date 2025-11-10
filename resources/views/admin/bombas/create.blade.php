<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Bomba') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.bombas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    Voltar
                </x-button-link>
                <x-help-icon title="Ajuda - Criação bombas"
                    content="Nesta tela você cadastra a bomba de combustível a ser utilizada. Preencha todos os campos obrigatórios com as informações. Após o preenchimento, clique em 'Salvar' para adicionar o novo registro ou 'Voltar' para retornar à lista de bombas." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('admin.bombas._form', [
                        'action' => route('admin.bombas.store'),
                        'method' => 'POST',
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>