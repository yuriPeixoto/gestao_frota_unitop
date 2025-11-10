<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="ipvaForm()">
                <form id="ipvaForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif


                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do IPVA</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="id_ipva_veiculo"
                                    class="block text-sm font-medium text-gray-700">Código</label>
                                <input type="text" id="id_ipva_veiculo" name="id_ipva_veiculo" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($ipvaveiculos) ? $ipvaveiculos->id_ipva_veiculo : '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Veículo (Placa)"
                                    placeholder="Selecione o veículo..." :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')"  
                                    :disabled="isset($ipvaveiculos)" :selected="old('id_veiculo', isset($ipvaveiculos) ? $ipvaveiculos->id_veiculo : '')"
                                    asyncSearch="true" required="true" onSelectCallback="atualizarDadosVeiculoCallback" />
                            </div>

                            <div>
                                <label for="renavam" class="block text-sm font-medium text-gray-700">RENAVAM</label>
                                <input type="text" id="renavam" name="renavam" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="renavam">
                            </div>
                            <div x-data="{ id_filial_veiculo: '{{ isset($filialVeiculo) ? $filialVeiculo : '' }}' }">
                                <label for="id_filial_veiculo" class="block text-sm font-medium text-gray-700">Filial do
                                    Veículo</label>
                                <input type="text" id="id_filial_veiculo" name="id_filial_veiculo" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="id_filial_veiculo">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="status_ipva" class="block text-sm font-medium text-gray-700">Status</label>
                                <input type="text" id="status_ipva" name="status_ipva" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('status_ipva', isset($ipvaveiculos) ? $ipvaveiculos->status_ipva : 'PENDENTE') }}">
                            </div>

                            <div>
                                <label for="quantidade_parcelas"
                                    class="block text-sm font-medium text-gray-700">Quantidade de Parcelas</label>
                                <input type="number" id="quantidade_parcelas" name="quantidade_parcelas" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('quantidade_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->quantidade_parcelas : 1) }}"
                                    min="1">
                            </div>

                            <div>
                                <label for="intervalo_parcelas"
                                    class="block text-sm font-medium text-gray-700">Intervalo entre Parcelas
                                    (dias)</label>
                                <select id="intervalo_parcelas" name="intervalo_parcelas" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <option value="7"
                                        {{ old('intervalo_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->intervalo_parcelas : '') == '7' ? 'selected' : '' }}>
                                        Semanal (7 dias)</option>
                                    <option value="30"
                                        {{ old('intervalo_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->intervalo_parcelas : '') == '30' ? 'selected' : '' }}>
                                        Mensal (30 dias)</option>
                                    <option value="60"
                                        {{ old('intervalo_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->intervalo_parcelas : '') == '60' ? 'selected' : '' }}>
                                        Bimestral (60 dias)</option>
                                    <option value="90"
                                        {{ old('intervalo_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->intervalo_parcelas : '') == '90' ? 'selected' : '' }}>
                                        Trimestral (90 dias)</option>
                                    <option value="180"
                                        {{ old('intervalo_parcelas', isset($ipvaveiculos) ? $ipvaveiculos->intervalo_parcelas : '') == '180' ? 'selected' : '' }}>
                                        Semestral (180 dias)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="ano_validade" class="block text-sm font-medium text-gray-700">Ano de
                                    Validade</label>
                                <input type="number" id="ano_validade" name="ano_validade" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('ano_validade', isset($ipvaveiculos) ? $ipvaveiculos->ano_validade : date('Y')) }}"
                                    min="2000" max="{{ date('Y') + 1 }}">
                            </div>

                            <div>
                                <label for="data_primeira_parcela" class="block text-sm font-medium text-gray-700">Data
                                    Primeira Parcela</label>
                                <input type="date" id="data_primeira_parcela" name="data_primeira_parcela" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('data_primeira_parcela', isset($ipvaveiculos) && $ipvaveiculos->data_primeira_parcela ? date('Y-m-d', strtotime($ipvaveiculos->data_primeira_parcela)) : date('Y-m-d')) }}">
                            </div>

                            <div>
                                <label for="data_base_vencimento" class="block text-sm font-medium text-gray-700">Data
                                    Base Vencimento</label>
                                <input type="date" id="data_base_vencimento" name="data_base_vencimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('data_base_vencimento', isset($ipvaveiculos) && $ipvaveiculos->data_base_vencimento ? date('Y-m-d', strtotime($ipvaveiculos->data_base_vencimento)) : '') }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
                            <div>
                                <label for="valor_previsto_ipva" class="block text-sm font-medium text-gray-700">Valor
                                    Previsto IPVA</label>
                                <input type="text" id="valor_previsto_ipva" name="valor_previsto_ipva"
                                    @input="formatarInputMonetarioGlobal($event, 'valor_previsto_ipva')"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="valorPrevistoIPVA">
                            </div>

                            <div>
                                <label for="valor_juros_ipva" class="block text-sm font-medium text-gray-700">Valor
                                    Juros IPVA</label>
                                <input type="text" id="valor_juros_ipva" name="valor_juros_ipva" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm "
                                    value="{{ old('valor_juros_ipva', isset($ipvaveiculos) ? $ipvaveiculos->valor_juros_ipva ?? '0,00' : '0,00') }}">
                            </div>

                            <div>
                                <label for="valor_desconto_ipva" class="block text-sm font-medium text-gray-700">Valor
                                    Desconto IPVA</label>
                                <input type="text" id="valor_desconto_ipva" name="valor_desconto_ipva" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm "
                                    value="{{ old('valor_desconto_ipva', isset($ipvaveiculos) ? $ipvaveiculos->valor_desconto_ipva ?? '0,00' : '0,00') }}">
                            </div>

                            <div>
                                <label for="valor_pago_ipva" class="block text-sm font-medium text-gray-700">Valor
                                    Pago IPVA</label>
                                <input type="text" id="valor_pago_ipva" name="valor_pago_ipva"
                                    @input="formatarInputMonetarioGlobal($event, 'valor_pago_ipva')" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="valorPagoIPVA">
                            </div>

                            <div>
                                <label for="data_pagamento_ipva" class="block text-sm font-medium text-gray-700">Data
                                    Pagamento IPVA</label>
                                <input type="date" id="data_pagamento_ipva" name="data_pagamento_ipva"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('data_pagamento_ipva', isset($ipvaveiculos) && $ipvaveiculos->data_pagamento_ipva ? date('Y-m-d', strtotime($ipvaveiculos->data_pagamento_ipva)) : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Form de Parcelas -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Parcelas de IPVA</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="numero_parcela" class="block text-sm font-medium text-gray-700">Número da
                                    Parcela</label>
                                <input type="number" id="numero_parcela" name="numero_parcela"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.numero_parcela" min="1">
                            </div>

                            <div>
                                <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data
                                    Vencimento</label>
                                <input type="date" id="data_vencimento" name="data_vencimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.data_vencimento">
                            </div>

                            <div>
                                <label for="valor_parcela" class="block text-sm font-medium text-gray-700">Valor
                                    Parcela</label>
                                <input type="text" id="valor_parcela" name="valor_parcela"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.valor_parcela"
                                    @input="formatarInputMonetario($event, 'valor_parcela')">
                            </div>

                            <div>
                                <label for="data_pagamento" class="block text-sm font-medium text-gray-700">Data
                                    Pagamento</label>
                                <input type="date" id="data_pagamento" name="data_pagamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.data_pagamento">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="valor_desconto" class="block text-sm font-medium text-gray-700">Valor
                                    Desconto</label>
                                <input type="text" id="valor_desconto" name="valor_desconto" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm "
                                    x-model="novoItem.valor_desconto">
                            </div>

                            <div>
                                <label for="valor_juros" class="block text-sm font-medium text-gray-700">Valor
                                    Juros</label>
                                <input type="text" id="valor_juros" name="valor_juros" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm "
                                    x-model="novoItem.valor_juros">
                            </div>

                            <div>
                                <label for="valor_pagamento" class="block text-sm font-medium text-gray-700">Valor
                                    Pagamento</label>
                                <input type="text" id="valor_pagamento" name="valor_pagamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.valor_pagamento"
                                    @input="formatarInputMonetario($event, 'valor_pagamento')">
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" id="adicionar-item" x-on:click="adicionarItem"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar Parcela
                            </button>
                            <button type="button" id="atualizar-item" x-on:click="atualizarItem"
                                class="inline-flex items-center px-4 py-2 border border-transparent hidden text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Atualiazar
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Parcelas -->
                    <div class="mt-6">
                        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">Nº Parcela</th>
                                        <th scope="col" class="py-3 px-6">Data Vencimento</th>
                                        <th scope="col" class="py-3 px-6">Valor Parcela</th>
                                        <th scope="col" class="py-3 px-6">Data Pagamento</th>
                                        <th scope="col" class="py-3 px-6">Valor Desconto</th>
                                        <th scope="col" class="py-3 px-6">Valor Juros</th>
                                        <th scope="col" class="py-3 px-6">Valor Pagamento</th>
                                        <th scope="col" class="py-3 px-6">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="py-3 px-6" x-text="item.numero_parcela"></td>
                                            <td class="py-3 px-6" x-text="formatarData(item.data_vencimento)"></td>
                                            <td class="py-3 px-6 valor_parcela"
                                                x-text="formatarValorExibicao(item.valor_parcela)"></td>
                                            <td class="py-3 px-6" x-text="formatarData(item.data_pagamento)"></td>
                                            <td class="py-3 px-6 valor_desconto"
                                                x-text="formatarValorExibicao(item.valor_desconto)"></td>
                                            <td class="py-3 px-6 valor_juros"
                                                x-text="formatarValorExibicao(item.valor_juros)"></td>
                                            <td class="py-3 px-6 valor_pagamento"
                                                x-text="formatarValorExibicao(item.valor_pagamento)"></td>
                                            <td class="py-3 px-6">
                                                <div class="flex space-x-2">
                                                    <button type="button" x-on:click="editarItem(index)"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" x-on:click="removerItem(index)"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                        <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhuma parcela
                                            adicionada</td>
                                    </tr>
                                    <tr class="bg-gray-100 border-b font-medium">
                                        <td colspan="2" class="py-3 px-6 text-right" x-init="calcularTotais()">
                                            Total:</td>
                                        <td class="py-3 px-6" x-text="formatarMoeda(totais.valorParcela)">
                                        </td>
                                        <td class="py-3 px-6"></td>
                                        <td class="py-3 px-6" x-text="formatarMoeda(totais.valorDesconto)"></td>
                                        <td class="py-3 px-6" x-text="formatarMoeda(totais.valorJuros)"></td>
                                        <td class="py-3 px-6" x-text="formatarMoeda(totais.valorPagamento)"></td>
                                        <td class="py-3 px-6"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-between space-x-4 mt-6">
                        @if (isset($ipvaveiculos) && $ipvaveiculos->id_ipva_veiculo)
                            <button type="button" id="gerar-parcelas"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Gerar Parcelas
                            </button>
                        @else
                            <div></div>
                        @endif

                        <div class="flex space-x-3">
                            <button type="button" x-on:click="limparFormulario"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Limpar Formulário
                            </button>

                            <a href="{{ route('admin.ipvaveiculos.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>

                    <!-- Campo Hidden para Parcelas -->
                    <input type="hidden" name="ipvaveiculosinput" x-model="JSON.stringify(items)" />
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.ipvaveiculos._scripts')
@endpush
