@props(['role', 'permissions' => []])

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Cargo: {{ $role->name }}
            </h2>
            <x-help-icon
                title="Ajuda - Edição de Cargo"
                content="Nesta tela você pode editar as informações de um cargo existente. Modifique os campos conforme necessário e clique em 'Atualizar' para salvar as alterações."
            />
        </div>
    </x-slot>

    @include('admin.cargos._form', ['role' => $role])
</x-app-layout>
