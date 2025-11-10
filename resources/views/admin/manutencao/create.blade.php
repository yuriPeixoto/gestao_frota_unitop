<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Criar Manutenção') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Tipo de Categoria"
                    content="Nesta tela você pode visualizar todos os tipos de checklist. Utilize o botão 'Novo Tipo Checklist' para adicionar um novo registro. Você pode editar ou excluir tipos de checklist existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <form action="{{ route('admin.manutencoes.store') }}" method="POST"
        x-data="{ isSubmitting: false }"
        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
        @csrf
        @include('admin.manutencao._form')
    </form>
</x-app-layout>