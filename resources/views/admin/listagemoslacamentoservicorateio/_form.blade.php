<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="manutencaoServico()">
                <form method="POST" action="{{ $action }}" class="space-y-4" id="manutencaoServico">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Campo oculto para os serviços -->
                    <input type="hidden" name="servicos" x-model="JSON.stringify(items)"
                        value="{{ json_encode($cadastros->servicos ?? []) }}">
                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="id_nota_fiscal_servico" class="block text-sm font-medium text-gray-700">Cód.
                                    Nota Fiscal de Serviço</label>
                                <input type="text" id="id_nota_fiscal_servico" name="id_nota_fiscal_servico" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->id_nota_fiscal_servico ?? '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_fornecedor" label="Fornecedor" :options="$fornecedoresFrequentes"
                                    :searchUrl="route('admin.fornecedores.search')" :selected="old('id_municipio', $cadastros->id_fornecedor ?? '')" asyncSearch="true" />
                            </div>

                            <div>
                                <label for="data_servico" class="block text-sm font-medium text-gray-700">Data
                                    Recebimento</label>
                                <input type="date" id="data_servico" name="data_servico"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required value="{{ old('data_servico', $cadastros->data_servico ?? '') }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <div>
                                <label for="numero_serie" class="block text-sm font-medium text-gray-700">Número de
                                    Série</label>
                                <input type="number" id="numero_serie" name="numero_serie"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->numero_serie ?? '' }}">
                            </div>

                            <div>
                                <label for="numero_nota_fiscal" class="block text-sm font-medium text-gray-700">Número
                                    da Nota Fiscal</label>
                                <input type="number" id="numero_nota_fiscal" name="numero_nota_fiscal"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->numero_nota_fiscal ?? '' }}">
                            </div>

                            <div>
                                <label for="valor_total_servico" class="block text-sm font-medium text-gray-700">Valor
                                    Total Serviço</label>
                                <input id="valor_total_servico" name="valor_total_servico" data-mask="valor"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->valor_total_servico ?? '' }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Aplicar Rateio</label>
                                <div class="mt-1 inline-flex border border-gray-300 rounded-lg overflow-hidden">
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="rateio_nf" value="true" class="hidden peer"
                                            {{ old('rateio_nf', $cadastros->rateio_nf ?? '') == '1' ? 'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Sim</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="rateio_nf" value="false" class="hidden peer"
                                            {{ old('rateio_nf', $cadastros->rateio_nf ?? '') == '0' ? 'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Não</span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                            <div>
                                <x-forms.smart-select name="id_servico" label="Servicos" :options="$servicosFrequentes"
                                    :searchUrl="route('admin.servicos.search')" :selected="old('id_servico', $cadastros->id_servico ?? '')" asyncSearch="true" x-model="id_servico" />
                            </div>

                            <div>
                                <label for="quantidade"
                                    class="block text-sm font-medium text-gray-700">Quantidade</label>
                                <input type="number" id="quantidade" name="quantidade" @blur="calcularTotal()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->quantidade ?? '' }}">
                            </div>

                            <div>
                                <label for="valor_produto" class="block text-sm font-medium text-gray-700">Valor
                                    Produto</label>
                                <input id="valor_produto" name="valor_produto"
                                    @input="formatarValorProduto($event); calcularTotal()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->valor_produto ?? '' }}">
                            </div>

                            <div>
                                <label for="total_produto" class="block text-sm font-medium text-gray-700">Total
                                    Produto</label>
                                <input id="total_produto" name="total_produto" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $cadastros->total_produto ?? '' }}">
                            </div>

                            <div class="flex justify-start mt-4">
                                <button type="button" @click="adicionarItem()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>

                        </div>

                        <!-- Tabela de Itens -->
                        <div class="mt-6">
                            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th scope="col" class="py-3 px-6">Data inclusão</th>
                                            <th scope="col" class="py-3 px-6">Serviço Realizado</th>
                                            <th scope="col" class="py-3 px-6">Quantidade</th>
                                            <th scope="col" class="py-3 px-6">Valor Produto</th>
                                            <th scope="col" class="py-3 px-6">Total Produto</th>
                                            <th scope="col" class="py-3 px-6">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-3 px-6" x-text="formatarData(item.data_inclusao)"></td>
                                                <td class="py-3 px-6" x-text="item.servico.descricao_servico"></td>
                                                <td class="py-3 px-6" x-text="item.quantidade"></td>
                                                <td class="py-3 px-6" x-text="item.valor_produto"></td>
                                                <td class="py-3 px-6" x-text="item.total_produto"></td>
                                                <td class="py-3 px-6">
                                                    <div class="flex space-x-2">
                                                        <button type="button" @click="editarItem(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click="removerItem(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="items.length === 0" class="bg-white border-b">
                                            <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhum item
                                                adicionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" @click="limparFormulario()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </button>

                        <a href="{{ route('admin.listagemoslacamentoservicorateio.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.listagemoslacamentoservicorateio._scripts')
@endpush
