<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Entrada de Nota Fiscal') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-help-icon title="Ajuda - Criação de novo Tipo de Orgão"
                    content="Nesta tela você pode cadastrar um novo tipo de Orgão. Preencha todos os campos obrigatórios com as informações. Após o preenchimento, clique em 'Salvar' para adicionar o novo registro ou 'Voltar' para retornar à listagem." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.notafiscalentrada.store') }}" method="POST" x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        @include('admin.notafiscalentrada._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
