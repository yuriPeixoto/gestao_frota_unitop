<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Tipo Manutenção') }}: {{ $tipomanutencoes->tipo_manutencao_descricao }}
            </h2>
            <x-help-icon title="Ajuda - Editar Tipo Manutenção"
                content="Nesta tela você pode editar o tipo selecionado. Preencha os campos e clique em salvar." />
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.tipomanutencoes.update', $tipomanutencoes) }}" method="POST"
                        x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        @method('PUT')
                        @include('admin.tipomanutencoes._form')

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>