<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Manutenção') }}: {{ $tipomanutencaoimobilizado->descricao }}
            </h2>
            <x-help-icon title="Ajuda - Edição de Manutenção"
                content="Nesta tela você pode editar as informações de uma manutenção existente. Modifique os campos conforme necessário e clique em 'Atualizar' para salvar as alterações." />
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.tipomanutencaoimobilizados.update', $tipomanutencaoimobilizado) }}"
                        method="POST"
                         x-data="{ isSubmitting: false }" 
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }"
                        >
                        @csrf
                        @method('PUT')
                        @include('admin.tipomanutencaoimobilizados._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>