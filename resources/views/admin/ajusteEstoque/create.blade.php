<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Novo Ajuste Estoque') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-help-icon title="Ajuda - Criação de Tipo de Categoria"
                    content="Nesta tela você pode cadastrar um novo tipo de Categoria. Preencha todos os campos obrigatórios com as informações do tipo. Após o preenchimento, clique em 'Salvar' para adicionar o novo registro ou 'Voltar' para retornar à lista de dimensões." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('admin.ajusteEstoque._form', [
                        'action' => route('admin.ajusteEstoque.store'), // ← Mudança aqui
                        'method' => 'POST',
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
