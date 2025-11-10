<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Meta Tipo Equipamento: ' . $metaTipoEquipamentos->id_meta) }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Edição de Meta Tipo Equipamento"
                    content="Nesta tela você pode editar uma Meta Tipo Equipamento existente. Altere os campos necessários e salve as modificações." />
            </div>
        </div>
    </x-slot>

    @include('admin.metatipoequipamentos._form', [
        'action' => route('admin.metatipoequipamentos.update', $metaTipoEquipamentos->id_meta),
        'method' => 'PUT',
        'metaTipoEquipamentos' => $metaTipoEquipamentos,
    ])
</x-app-layout>
