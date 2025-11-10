<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Ajuste Estoque') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-help-icon title="Ajuda - Criação de Tipo de Categoria"
                    content="Nesta tela você pode cadastrar um novo tipo de Categoria. Preencha todos os campos obrigatórios com as informações do tipo. Após o preenchimento, clique em 'Salvar' para adicionar o novo registro ou 'Voltar' para retornar à lista de dimensões." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white p-6  sm:rounded-lg">
                @csrf
                @include('admin.ajusteEstoque._form', [
                    'action' => route('admin.ajusteEstoque.update', $ajuste->id_acerto_estoque),
                    'ajuste' => $ajuste,
                    'method' => 'PUT',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
