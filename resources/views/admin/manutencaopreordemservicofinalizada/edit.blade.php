<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pré-O.S') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Cadastro de Pré-O.S"
                    content="Nesta tela você pode verificar os cadastro e serviços de Pré OS." />
            </div>
        </div>
    </x-slot>

    @include('admin.manutencaopreordemservicofinalizada._form', [
        'action' => route('admin.manutencaopreordemservicofinalizada.update', $preOrdemFinalizada->id_pre_os),
        'method' => 'PUT',
        'preOrdemFinalizada' => $preOrdemFinalizada,
    ])
</x-app-layout>
