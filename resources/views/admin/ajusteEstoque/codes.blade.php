<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerar código') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-help-icon
                    title="Ajuda - Gerenciamento de Filiais"
                    content="Nesta tela você pode visualizar todas as filiais cadastradas. Utilize o botão 'Nova Filial' para adicionar um novo registro. Você pode editar ou excluir filiais existentes utilizando as ações disponíveis em cada linha da tabela."
                />
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-10 h-[250px] shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.ajusteEstoque.codeGenerate') }}" method="POST"
            x-data="{ isSubmitting: false }"
            @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
            @csrf
            <div class="flex mb-4 gap-2">
                <div class=w-full>
                    <x-forms.smart-select 
                        name="id_filial" 
                        label="Filial" 
                        placeholder="Selecione..."
                        :options="$filial"    
                        :selected="request('id_filial')"
                        asyncSearch="true"
                    />
                </div>
                <div class=w-full>
                    <x-forms.smart-select 
                        name="id_estoque" 
                        label="Fornecedor" 
                        placeholder="Selecione..."
                        :options="$estoque"    
                        :selected="request('id_estoque')"
                        asyncSearch="true"
                    />
                </div>
                <div class=w-full>
                    <x-forms.smart-select 
                        name="id_grupo" 
                        label="Grupo" 
                        placeholder="Selecione..."
                        :options="$grupoServicos"    
                        :selected="request('id_grupo')"
                        asyncSearch="true"
                    />
                </div>
                <div class=w-full>
                    <x-forms.smart-select 
                        name="id_produto" 
                        label="Produto" 
                        placeholder="Selecione..."
                        :options="$produto"    
                        :selected="request('id_produto')"
                        asyncSearch="true"
                    />
                </div>
                <div class=w-full>
                    <x-forms.smart-select 
                        name="tipo_codigo" 
                        label="Tipo do Código" 
                        placeholder="Selecione..."
                        :options="$tipoCodigo"    
                        :selected="request('tipo_codigo')"
                        asyncSearch="true"
                    />
                </div>
            </div>
            <button type="submit"
                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                Gerar
            </button>
        </form>

    </div>
</x-app-layout>
