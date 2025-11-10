<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Editar Solicitação de Compra') }}
            </h2>

        </div>
    </x-slot>

    @include('admin.compras.solicitacoes._form', [
        'action' => route('admin.compras.solicitacoes.update', $solicitacao->id_solicitacoes_compras),
        'method' => 'PUT',
        'solicitacao' => $solicitacao,
    ])
</x-app-layout>
