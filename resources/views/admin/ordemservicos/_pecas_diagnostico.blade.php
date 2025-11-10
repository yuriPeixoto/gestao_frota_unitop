<div>
    {{-- fornecedor --}}
    <x-forms.smart-select name="id_fornecedor-pecas" label="Fornecedor" placeholder="Selecione o fornecedor..."
        onSelectCallback="atualizarPecasFornecedor" :options="$fornecedoresFrequentes ?? []" :searchUrl="route('admin.api.fornecedores.search')" :selected="old('id_fornecedor', $ordemServico->id_fornecedor ?? '')"
        asyncSearch="true" />
</div>

<div class="grid grid-cols-3 md:grid-cols-3 gap-2 mt-4">
    {{-- Produto --}}
    <div>
        <x-forms.smart-select name="id_produto" label="Produto" placeholder="Selecione o produto..."
            onSelectCallback="atualizarPecasProduto" :options="$produtosFrequentes ?? []" :searchUrl="route('admin.api.produto.search')" :selected="old('id_produto', $ordemServico->id_produto ?? '')"
            asyncSearch="true" />
    </div>

    <div>
        <label for="grupo_produto" class="block text-sm font-medium text-gray-700">
            Grupo Produto:
        </label>
        <input type="text" id="desc_grupo" name="desc_grupo" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->desc_grupo ?? '' }}">
    </div>

    <div>
        <label for="qtd_estoque" class="block text-sm font-medium text-gray-700">
            Qtde. Estoque:
        </label>
        <input type="text" id="qtd_estoque" name="qtd_estoque" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->qtd_estoque ?? '' }}">
    </div>
</div>
<div class="grid grid-cols-4 md:grid-cols-4 gap-2 mt-4">
    <div>
        <label for="quantidade" class="block text-sm font-medium text-gray-700">
            Quantidade:
        </label>
        <input type="number" id="quantidade" name="quantidade"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->id_unidade ?? '' }}">
    </div>
    <div>
        <label for="valor_pecas" class="block text-sm font-medium text-gray-700">
            Valor Peças:
        </label>
        <input type="text" id="valor_pecas" name="valor_pecas"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_pecas ?? '' }}">
    </div>
    <div>
        <label for="valor_desconto" class="block text-sm font-medium text-gray-700">
            Valor Desconto:
        </label>
        <input type="text" id="valor_desconto" name="valor_desconto" readonly
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_desconto ?? '' }}">
    </div>
    <div>
        <label for="valor_total_com_desconto" class="block text-sm font-medium text-gray-700">
            Valor Total com Descontos:
        </label>
        <input type="text" id="valor_total_com_desconto" name="valor_total_com_desconto" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_total_com_desconto ?? '' }}">
    </div>
</div>

<div class="flex justify-left mt-4">
    <button type="button" onclick="adicionarPecas()"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Adicionar
    </button>
</div>

<!-- Tabela de Itens -->
<div class="p-6 bg-white border-gray-200">
    <input type="hidden" name="tabelaPecas" id="tabelaPecas_json"
        value="{{ isset($tabelaPecas) ? json_encode($tabelaPecas) : '[]' }}">

    <input type="hidden" name="id_unidade" id="id_unidade" value="{{ $ordemServico->id_unidade ?? '' }}">
    <input type="hidden" name="descrUnidade" id="descrUnidade">

    @if (isset($tabelaPecas) && count($tabelaPecas) > 0)
        <div class="p-2">
            <x-forms.button onclick="onDeletarPecas()" type="danger">
                <x-icons.trash class="w-4 h-4 mr-2" />
                Excluir
            </x-forms.button>
        </div>
    @endif

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaPecasBody">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <!-- Checkbox Master -->
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    </th>
                    <th scope="col" class="py-3 px-6">Código Peças</th>
                    <th scope="col" class="py-3 px-6">Fornecedor</th>
                    <th scope="col" class="py-3 px-6">Produto</th>
                    <th scope="col" class="py-3 px-6">Unidade</th>
                    <th scope="col" class="py-3 px-6">Valor Unitário</th>
                    <th scope="col" class="py-3 px-6">Valor Desconto</th>
                    <th scope="col" class="py-3 px-6">Quantidade</th>
                    <th scope="col" class="py-3 px-6">Valor Total com Desconto</th>
                    <th scope="col" class="py-3 px-6">Numero da Nota Fiscal</th>
                    <th scope="col" class="py-3 px-6">Situação</th>
                </tr>
            </thead>
            <tbody id="tabelaPecasBody" class="bg-white divide-y divide-gray-200 text-sm">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
    @include('admin.ordemservicos._scripts')
@endpush
