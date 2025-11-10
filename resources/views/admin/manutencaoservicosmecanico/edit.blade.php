<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assumir serviço #' . $manutancaoServicoMec->id_servico_mecanico) }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Assumir Serviço"
                    content="Nesta tela você pode assumir o serviço. Altere os campos necessários e salve as modificações." />
            </div>
        </div>
    </x-slot>

    @include('admin.manutencaoservicosmecanico._form', [
        'action' => route('admin.manutencaoservicosmecanico.update', $manutancaoServicoMec->id_servico_mecanico),
        'method' => 'PUT',
        'manutancaoServicoMec' => $manutancaoServicoMec,
    ])
</x-app-layout>
