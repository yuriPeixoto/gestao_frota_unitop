<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Abastecimento Manual') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Edição de Abastecimento Manual"
                    content="Nesta tela você pode editar um abastecimento manual existente. Altere os campos necessários e salve as modificações." />
            </div>
        </div>
    </x-slot>

    @include('admin.abastecimentosfaturamento._form', [
        'action' => route('admin.abastecimentosfaturamento.update', $abastecimento->id_abastecimento),
        'method' => 'PUT',
        'abastecimento' => $abastecimento,
    ])
</x-app-layout>
