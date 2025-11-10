<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex gap-2 mb-5">
            <div class="w-64 mt-5">
                <x-forms.input label="Número do Pedido" name="id_pedido_compras" id="id_pedido_compra_b" readonly
                    value="{{ old('id_pedido_compras', $nfEntrada->id_pedido_compras ?? '') }}" />
            </div>
            <div class="mt-12">
                <x-forms.button onclick="buscaPedido()" disabled>
                    Buscar Pedido
                </x-forms.button>
            </div>
        </div>

        <div class="text-lg font-semibold mb-5">
            <h1>Dados do Fornecedor</h1>
            <hr>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-forms.input label="Código Entrada" name="id_nota_fiscal_entrada" readonly
                value="{{ old('id_nota_fiscal_entrada', $nfEntrada->id_nota_fiscal_entrada ?? '') }}" />

            <x-forms.input label="CNPJ" id="cnpj" name="cnpj" readonly
                value="{{ old('cnpj', $nfEntrada->cnpj ?? '') }}" />

            <x-forms.input label="Código Fornecedor" name="id_fornecedor" id="id_fornecedor" readonly
                value="{{ old('id_fornecedor', $nfEntrada->id_fornecedor ?? '') }}" />

            <x-forms.input label="Nome Empresa" name="nome_empresa" id="nome_empresa" readonly
                value="{{ old('nome_empresa', $nfEntrada->nome_empresa ?? '') }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-2">
            <x-forms.input label="Endereço" name="endereco" id="endereco" readonly
                value="{{ old('endereco', $nfEntrada->endereco ?? '') }}" />

            <x-forms.input label="Bairro" name="bairro" id="bairro" readonly
                value="{{ old('bairro', $nfEntrada->bairro ?? '') }}" />

            <x-forms.input label="Número" name="numero" id="numero" readonly
                value="{{ old('numero', $nfEntrada->numero ?? '') }}" />

            <x-forms.smart-select label="Municipio" name="nome_municipio" :disabled="true" :options="$municipios"
                :selected="old('nome_municipio', $nfEntrada->nome_municipio ?? '')" />

            <x-forms.smart-select label="Estado" name="uf" :disabled="true" :options="$estados" :selected="old('uf', $nfEntrada->uf ?? '')" />

            <x-forms.input label="CEP" name="cep" readonly value="{{ old('cep', $nfEntrada->cep ?? '') }}" />
        </div>

        <div class="text-lg font-semibold mb-5 mt-5">
            <h1>Informação Nota Fiscal</h1>
            <hr>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-7 gap-4 mt-2">
            <div class="col-span-2">
                <x-forms.input label="Chave Nota Fiscal" name="chave_nf_entrada" id="chave_nf_entrada" readonly
                    placeholder="Faça a leitura ou digite a chave da NF-e" maxlength="44" pattern="[0-9]{44}"
                    value="{{ old('chave_nf_entrada', $nfEntrada->chave_nf_entrada ?? '') }}" />
            </div>
            <x-forms.input label="Código Nota Fiscal" name="cod_nota_fiscal" readonly
                value="{{ old('cod_nota_fiscal', $nfEntrada->cod_nota_fiscal ?? '') }}" />

            <x-forms.input label="Número Nota Fiscal" name="numero_nota_fiscal" id="numero_nota_fiscal" readonly
                value="{{ old('numero_nota_fiscal', $nfEntrada->numero_nota_fiscal ?? '') }}" />

            <x-forms.input label="Natureza Operação" name="natureza_operacao" readonly
                value="{{ old('natureza_operacao', $nfEntrada->natureza_operacao ?? '') }}" />


            <x-forms.input type="datetime-local" label="Data de Emissão" name="data_emissao" readonly
                value="{{ old('data_emissao', $nfEntrada->data_emissao ?? '') }}" />

            <x-forms.input type="datetime-local" label="Data de Saída" name="data_saida" readonly
                value="{{ old('data_saida', $nfEntrada->data_saida ?? '') }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-2">
            <x-forms.input label="Valor Nota Fiscal" name="valor_nota_fiscal" readonly
                value="{{ old('valor_nota_fiscal', $nfEntrada->valor_nota_fiscal ?? '') }}" />

            <x-forms.input label="Valor Desconto" name="valor_desconto_nfe" readonly
                value="{{ old('valor_desconto', $nfEntrada->valor_desconto ?? '') }}" />

            <x-forms.input label="Valor Frete" name="valor_frete" readonly
                value="{{ old('valor_frete', $nfEntrada->valor_frete ?? '') }}" />

            <x-forms.smart-select label="Filial" name="id_filial" :disabled="true" :options="$filial"
                :selected="old('id_filial', $nfEntrada->id_filial ?? '')" />

            <div class="relative inline-flex flex-col mt-1">
                <span class="text-sm font-medium text-gray-700">Aplicar NF para rateio?</span>

                <div class="flex">
                    <div class="relative">
                        <input type="radio" name="aplica_rateio" value="1" id="aplica_rateio_sim" disabled
                            class="sr-only peer" @checked(old('aplica_rateio', isset($nfEntrada) ? $nfEntrada->aplica_rateio : null) == 1)>
                        <label for="aplica_rateio_sim"
                            class="w-16 h-10 border-2 border-gray-300 rounded-md flex items-center justify-center cursor-pointer text-sm font-medium text-gray-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 hover:bg-gray-50 transition-colors">
                            SIM
                        </label>
                    </div>

                    <div class="relative">
                        <input type="radio" name="aplica_rateio" value="0" id="aplica_rateio_nao" disabled
                            class="sr-only peer" @checked(old('aplica_rateio', isset($nfEntrada) ? $nfEntrada->aplica_rateio : null) == 0)>
                        <label for="aplica_rateio_nao"
                            class="w-16 h-10 border-2 border-gray-300 rounded-md flex items-center justify-center cursor-pointer text-sm font-medium text-gray-500 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 hover:bg-gray-50 transition-colors">
                            NÃO
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="text-lg font-semibold mt-10">PRODUTOS</h1>
        <hr class="border-gray-300">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
            <x-forms.input label="Código de Produto" name="cod_produto" readonly value="{{ old('cod_produto') }}" />

            <x-forms.input label="Nome Produto" name="nome_produto" readonly value="{{ old('nome_produto') }}" />

            <x-forms.input label="NCM" name="ncm" readonly value="{{ old('ncm') }}" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-2">
            <x-forms.input label="Unidade" readonly name="unidade" value="{{ old('unidade') }}" />

            <x-forms.input label="Quantidade Produtos" name="quantidade_produtos" readonly
                value="{{ old('quantidade_produtos') }}" />

            <x-forms.input label="Quantidade Devolução" name="quantidade_devolucao"
                value="{{ old('quantidade_devolucao') }}" />

            <x-forms.input label="Valor Unitário" readonly name="valor_unitario"
                value="{{ old('valor_unitario') }}" />

            <x-forms.input label="Valor Total" readonly name="valor_total" value="{{ old('valor_total') }}" />

            <x-forms.input label="Valor Total com Desconto" readonly name="valor_desconto_produtos"
                id="campo_valor_desconto" value="{{ old('valor_desconto_produtos') }}" />

            <x-forms.button onclick="adicionarNfProdutos()">
                Adicionar Produto
            </x-forms.button>
        </div>

        <div class="p-6 bg-white border-gray-200 mt-5">
            <!-- Campo hidden para armazenar os pneus Itens -->
            <input type="hidden" name="nfeProdutos" id="nfeProdutos_json"
                value="{{ isset($nfeProdutos) ? json_encode($nfeProdutos) : '[]' }}">

            <input type="hidden" name="id_nota_fiscal_produtos" id="id_nota_fiscal_produtos">
            <input type="hidden" name="devolucao" id="devolucao">

            <div class="col-span-full">
                <table
                    class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelanfeProdutosBody">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Inclusão
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Alteração
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código do Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nome do Produto
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NCM
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unidade
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade de Produtos
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantidade Devolução
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelanfeProdutosBody" class="bg-white divide-y divide-gray-200">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-right space-x-3 col-span-full mt-5">
            <!-- Botão Enviar -->
            <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                <template x-if="!isSubmitting">
                    <span>{{ isset($nfEntrada) ? 'Atualizar' : 'Salvar' }}</span>
                </template>
                <template x-if="isSubmitting">
                    <span>{{ isset($nfEntrada) ? 'Atualizando...' : 'Salvando...' }}</span>
                </template>
            </button>

            <x-forms.button href="{{ route('admin.notafiscalentrada.index') }}" type="secondary" variant="outlined">
                Cancelar
            </x-forms.button>

            @if (isset($nfEntrada) && $nfEntrada->processada && !$nfEntrada->apuracao_saldo)
                <x-forms.button onclick="refreshEstoque({{ $nfEntrada->id_nota_fiscal_entrada }})" type="secondary"
                    variant="outlined">
                    <x-icons.refresh class="h-4 w-4 mr-2" />
                    Atualiza Estoque
                </x-forms.button>
            @endif
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/notafiscalentrada/notafiscalProdutos.js') }}"></script>
    @include('admin.notafiscalentrada._scripts')
@endpush
