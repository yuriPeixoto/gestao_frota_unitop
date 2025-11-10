<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de Meta por Tipo de Equipamento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Cadastro de Meta por Tipo de Equipamento"
                    content="Nesta tela você pode cadastrar uma nova meta por tipo de equipamento. Preencha todos os campos obrigatórios na parte superior e adicione um ou mais abastecimentos na seção abaixo." />
            </div>
        </div>
    </x-slot>

    @include('admin.metatipoequipamentos._form', [
        'action' => route('admin.metatipoequipamentos.store'),
        'method' => 'POST',
    ])
</x-app-layout>
