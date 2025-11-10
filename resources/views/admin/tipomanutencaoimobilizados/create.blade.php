<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Novo Tipo') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.tipomanutencaoimobilizados.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    Voltar
                </x-button-link>
                <x-help-icon title="Ajuda - Criação de Tipo de Manutenção de Imobilizados"
                    content="Nesta tela você pode cadastrar um novo tipo. Preencha todos os campos obrigatórios com as informações do tipo. Após o preenchimento, clique em 'Salvar' para adicionar o novo tipo de manutenção ou 'Voltar' para retornar à lista de tipos." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.tipomanutencaoimobilizados.store') }}" method="POST"
                         x-data="{ isSubmitting: false }" 
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }"
                        >
                        @csrf
                        @include('admin.tipomanutencaoimobilizados._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>