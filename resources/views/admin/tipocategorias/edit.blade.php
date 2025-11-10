<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Descrição') }}: {{ $tipocategoria->descricao_categoria }}
            </h2>
            <x-help-icon title="Ajuda - Editar Descrição"
                content="Nesta tela você pode editar o tipo selecionado. Preencha os campos e clique em salvar." />
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.tipocategorias.update', $tipocategoria) }}" method="POST"
                        x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        @method('PUT')
                        @include('admin.tipocategorias._form')

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>