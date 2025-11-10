@if (session('error'))
    <div class="alert-danger alert">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-4">
        <ul class="list-inside list-disc text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Alerta de edição em andamento --}}
<div id="alertaEdicaoPecas" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                <strong>Modo de edição ativo:</strong> Você está editando uma peça. Complete a edição clicando em
                "Adicionar" ou cancele para liberar o salvamento do formulário.
            </p>
        </div>
    </div>
</div>

{{--
<pre>{{ gettype($tabelaPecas) }}</pre> --}}
<div>
    {{-- fornecedor --}}
    <x-forms.smart-select name="id_fornecedor-pecas" label="Fornecedor" placeholder="Selecione o fornecedor..."
        onSelectCallback="atualizarPecasFornecedor" :options="$fornecedoresFrequentes ?? []" :searchUrl="route('admin.api.fornecedor.search')" :selected="old('id_fornecedor', $ordemServico->id_fornecedor ?? '')"
        asyncSearch="true" />
</div>

<div class="grid grid-cols-3 md:grid-cols-3 gap-2 mt-4">
    {{-- Produto --}}
    <div>
        <x-forms.smart-select name="id_produto" label="Produto" placeholder="Selecione o produto..."
            onSelectCallback="atualizarPecasProduto" :options="$produtosFrequentes ?? []" :searchUrl="route('admin.ordemservicos.getProdutosSearch', ['operacao' => 1])" :selected="old('id_produto', $ordemServico->id_produto ?? '')"
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
        <input type="text" id="valor_unitario_pecas" name="valor_unitario_pecas"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500
            sm:text-sm"
            value="{{ $ordemServico->valor_pecas ?? '' }}">
    </div>
    <div>
        <label for="valor_desconto" class="block text-sm font-medium text-gray-700">
            Valor Desconto:
        </label>
        <input type="text" id="valor_desconto" name="valor_desconto"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_desconto ?? '' }}">
    </div>
    <div>
        <label for="valor_total_com_desconto" class="block text-sm font-medium text-gray-700">
            Valor Total com Descontos:
        </label>
        <input type="text" id="valor_total_com_desconto_pecas" name="valor_total_com_desconto_pecas" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ $ordemServico->valor_total_com_desconto ?? '' }}">
    </div>
</div>

<div class="flex justify-left mt-4 gap-2">
    <button type="button" onclick="adicionarPecas()"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Adicionar
    </button>

    <button type="button" onclick="cancelarEdicaoPecas()" id="btnCancelarEdicaoPecas"
        class="hidden inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancelar Edição
    </button>
</div>

<!-- Tabela de Itens -->
<div class="p-6 bg-white border-gray-200">
    <input type="hidden" name="tabelaPecas" id="tabelaPecas_json"
        value="{{ isset($tabelaPecas) ? json_encode($tabelaPecas) : '[]' }}">

    <input type="hidden" name="id_unidade" id="id_unidade" value="{{ $ordemServico->id_unidade ?? '' }}">
    <input type="hidden" name="descrUnidade" id="descrUnidade">

    @if (isset($tabelaPecas) && count($tabelaPecas) > 0 && $itemDevolucao)
        <div class="p-2">
            <x-forms.button onclick="onDeletarPecas()" type="danger">
                <x-icons.trash class="w-4 h-4 mr-2" />
                Excluir
            </x-forms.button>
        </div>
    @endif

    <div class="col-span-full overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaPecasBody">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th class="px-6 py-4 whitespace-nowrap">
                        <!-- Checkbox Master -->
                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    </th>
                    <th scope="col" class="py-3 px-6">Ações</th>
                    <th scope="col" class="py-3 px-6">Código Peças</th>
                    <th scope="col" class="py-3 px-6">Fornecedor</th>
                    <th scope="col" class="py-3 px-6">Produto</th>
                    <th scope="col" class="py-3 px-6">Unidade</th>
                    <th scope="col" class="py-3 px-6">Valor Unitário</th>
                    <th scope="col" class="py-3 px-6">Valor Desconto</th>
                    <th scope="col" class="py-3 px-6">Quantidade</th>
                    <th scope="col" class="py-3 px-6">Valor Total com Desconto</th>
                    <th scope="col" class="py-3 px-6">Peça Finalizada</th>
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
    <script src="{{ asset('js/manutencao/ordemservico/pecas.js') }}"></script>
@endpush
