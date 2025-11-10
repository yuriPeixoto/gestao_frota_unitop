<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Filial: {{ $filial->name }}
            </h2>
            <x-help-icon
                title="Ajuda - Edição de Filial"
                content="Nesta tela você pode editar as informações de uma filial existente. Modifique os campos conforme necessário e clique em 'Atualizar' para salvar as alterações."
            />
        </div>
    </x-slot>

    @include('admin.filiais._form', ['filial' => $filial])
</x-app-layout>
