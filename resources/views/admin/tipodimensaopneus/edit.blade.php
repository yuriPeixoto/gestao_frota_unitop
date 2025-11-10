<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Descrição') }}: {{ $tipodimensaopneus->descricao_pneu }}
            </h2>
            <x-help-icon
                title="Ajuda - Tipo de Dimensão"
                content="Nesta tela você pode visualizar todos os tipos de dimensão de pneus cadastrados. Utilize o botão 'Novo Tipo' para adicionar um novo registro. Você pode editar ou excluir tipos de dimensão existentes utilizando as ações disponíveis em cada linha da tabela."
            />
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.tipodimensaopneus.update', $tipodimensaopneus) }}" method="POST"
                         x-data="{ isSubmitting: false }" 
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }"
                    >
                        @csrf
                        @method('PUT')
                        @include('admin.tipodimensaopneus._form')
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>