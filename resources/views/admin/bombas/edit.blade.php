<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Bomba') }}: {{ $bomba->descricao_bomba }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.bombas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    Voltar
                </x-button-link>
                <x-help-icon title="Ajuda - Editar Bomba"
                    content="Nesta tela você pode editar a bomba selecionada. Preencha os campos e clique em salvar." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('admin.bombas._form', [
                        'action' => route('admin.bombas.update', $bomba->id_bomba),
                        'method' => 'PUT',
                        'bomba' => $bomba,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>