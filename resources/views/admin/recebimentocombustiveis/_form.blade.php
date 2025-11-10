@php
    $isCreate = request()->routeIs('admin.recebimentocombustiveis.create');
@endphp
@if (session('error'))
    <div class="mb-4 bg-red-50 p-4 rounded">
        <p class="text-red-600">{{ session('error') }}</p>
    </div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form id="recebimentoCombustiveisForm" method="POST" action="{{ $action }}" class="space-y-4"
            x-data="recebimentoCombustiveisForm()" x-init="init()">
            @csrf
            @if ($method === 'PUT')
                @method('PUT')
            @endif

            <div class="flex justify-end items-center mb-6">
                <a href="{{ route('admin.recebimentocombustiveis.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Voltar
                </a>
            </div>
            {{-- campos --}}
            <div class="grid grid-cols-4 gap-4 md:grid-cols-4">
                {{-- código do pedido --}}

                <div class="col-span-2 flex gap-2 items-end">
                    <div class="w-full">
                        <label for="id_pedido">Código do pedido</label>
                        <input class="border w-full border-gray-300 rounded-md" type="text" name="id_pedido"
                            id="id_pedido" value="{{ old('id_pedido', $recebimentoCombustiveis->id_pedido ?? '') }}">
                    </div>
                    <div class="w-full">
                        <button type="button" id="botao-buscar-pedido"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            <div class="flex items-center">
                                <x-icons.magnifying-glass class="w-4 h-4 mr-2" />
                                Buscar Pedido
                            </div>
                        </button>
                    </div>
                </div>

                {{-- buscar pedido --}}

                {{-- filial --}}
                <div class="col-span-2">
                    <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione o Filial..."
                        :options="$filiais" onSelectCallback="atualizarDadosTanque" :selected="old('id_filial', $recebimentoCombustiveis->id_filial ?? '')"
                        asyncSearch="false" />
                </div>

                {{-- Número NF --}}
                <x-bladewind::input label="Número NF" name="numeronotafiscal" type="text"
                    selected_value="{{ old('numeronotafiscal', $recebimentoCombustiveis->numeronotafiscal ?? '') }}" />

                {{-- Chave NF --}}
                <x-bladewind::input label="Chave NF" name="chave_nf" type="text"
                    selected_value="{{ old('chave_nf', $recebimentoCombustiveis->chave_nf ?? '') }}" />

                {{-- N° NF2 --}}
                <x-bladewind::input label="N° NF2" name="numero_nf2" type="text"
                    selected_value="{{ old('numero_nf2', $recebimentoCombustiveis->numero_nf2 ?? '') }}" />

                {{-- Chave NF2 --}}
                <x-bladewind::input label="Chave NF2" name="chave_nf2" type="text"
                    selected_value="{{ old('chave_nf2', $recebimentoCombustiveis->chave_nf2 ?? '') }}" />

                {{-- N° NF3 --}}
                <x-bladewind::input label="N° NF3" name="numero_nf3" type="text"
                    selected_value="{{ old('numero_nf3', $recebimentoCombustiveis->numero_nf3 ?? '') }}" />

                {{-- Chave NF3 --}}
                <x-bladewind::input label="Chave NF3" name="chave_nf3" type="text"
                    selected_value="{{ old('chave_nf3', $recebimentoCombustiveis->chave_nf3 ?? '') }}" />

                {{-- N° NF4 --}}
                <x-bladewind::input label="N° NF4" name="numero_nf4" type="text"
                    selected_value="{{ old('numero_nf4', $recebimentoCombustiveis->numero_nf4 ?? '') }}" />

                {{-- Chave NF4 --}}
                <x-bladewind::input label="Chave NF4" name="chave_nf4" type="text"
                    selected_value="{{ old('chave_nf4', $recebimentoCombustiveis->chave_nf4 ?? '') }}" />

                {{-- fornecedor --}}
                <div>
                    <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                        placeholder="Selecione o Fornecedor..." :options="$fornecedores" :searchUrl="route('admin.recebimentocombustiveis.getFornecedores')"
                        onSelectCallback="atualizarDadosTanque" :selected="old('id_fornecedor', $recebimentoCombustiveis->id_fornecedor ?? '')" asyncSearch="true" />
                </div>

                {{-- tanque --}}
                <div>
                    <label for="id_tanque" class="block text-sm font-medium text-gray-700">Tanque</label>
                    <select name="id_tanque"
                        x-on:change="$dispatch('id_tanque:selected', { value: $event.target.value })"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="" x-text="id_tanque || 'Selecione o Tanque...'"></option>
                        <template x-for="tanque in tanques" :key="tanque.value">
                            <option :value="tanque.value" x-text="tanque.label"
                                :selected="tanque.value == '{{ old('id_tanque', $recebimentoCombustiveis->id_tanque ?? '') }}'">
                            </option>
                        </template>
                    </select>
                </div>

                {{-- Data de Entrada --}}
                <div>
                    <label for="data_entrada" class="block text-sm font-medium text-gray-700">Data de Entrada</label>
                    <input name="data_entrada" id="data_entrada" type="datetime-local"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="{{ old('data_entrada', $recebimentoCombustiveis->data_entrada ?? '') }}" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    {{-- Valor total --}}
                    <div>
                        <label for="preco_total_item" class="block text-sm font-medium text-gray-700">Valor
                            total</label>
                        <input name="preco_total_item" id="preco_total_item"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            value="{{ old('preco_total_item', $recebimentoCombustiveis->preco_total_item ?? '') }}">
                    </div>

                    {{-- Valor Unitário --}}
                    <div>
                        <label for="valor_unitario" class="block text-sm font-medium text-gray-700">Valor
                            Unitário</label>
                        <input name="valor_unitario" id="valor_unitario" type="text" readonly="true"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            value="{{ old('valor_unitario', $recebimentoCombustiveis->valor_unitario ?? '') }}">
                    </div>
                </div>

                {{-- Quantidade --}}
                <div>
                    <label for="quantidade" class="block text-sm font-medium text-gray-700">Quantidade</label>
                    <input name="quantidade" type="text" id="quantidade"
                        class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="{{ old('quantidade', $recebimentoCombustiveis->quantidade ?? '') }}"
                        onblur="onCalcularValorUnitario()">
                </div>

                {{-- Temperatura --}}
                <div>
                    <label for="temperatura_combustivel"
                        class="block text-sm font-medium text-gray-700">Temperatura</label>
                    <input name="temperatura_combustivel" id="temperatura_combustivel" type="text"
                        {{ $isCreate ? '' : 'readonly' }}
                        class="w-full border border-gray-300 {{ $isCreate ? '' : 'bg-gray-100' }} rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="{{ old('temperatura_combustivel', $recebimentoCombustiveis->temperatura_combustivel ?? '') }}">
                </div>

                {{-- Densidade --}}
                <div>
                    <label for="densidade_combustivel"
                        class="block text-sm font-medium text-gray-700">Densidade</label>
                    <input name="densidade_combustivel" id="densidade_combustivel" type="text"
                        {{ $isCreate ? '' : 'readonly' }}
                        class="w-full border border-gray-300 {{ $isCreate ? '' : 'bg-gray-100' }} rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        value="{{ old('densidade_combustivel', $recebimentoCombustiveis->densidade_combustivel ?? '') }}"
                        {{ $isCreate ? 'onblur="onDensidade()"' : '' }}>
                </div>

                {{-- Volume convertido --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="volume_convertido" class="block text-sm font-medium text-gray-700">Volume
                            convertido</label>
                        <input name="volume_convertido" id="volume_convertido" type="text" readonly="true"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            value="{{ old('volume_convertido', $recebimentoCombustiveis->volume_convertido ?? '') }}">
                    </div>

                    <div>
                        <x-forms.input name="received_volume" label="Volume Recebido" readonly
                            value="{{ old('received_volume', $recebimentoCombustiveis->received_volume ?? '') }}" />

                    </div>
                </div>
            </div>

    </div>

    <!-- Botões de Ação -->
    <div class="flex justify-left items-center gap-2 mt-6 p-6">
        <button type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            Salvar
        </button>

        <button type="button" id="botao-limpar"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Limpar Formulário
        </button>

        {{-- <a href="{{ route('admin.recebimentocombustiveis.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Voltar
        </a> --}}
    </div>
    </form>
</div>

@push('scripts')
    <script></script>
    <script>
        function recebimentoCombustiveisForm() {
            return {
                id_tanque: "",
                tanques: [], // Array para armazenar os tanques

                init() {
                    // Listener para atualizar os tanques quando uma filial for selecionada
                    window.addEventListener('id_fornecedor:selected', (event) => {
                        console.log('Fornecedor selecionado:', event.detail.value);
                        if (event.detail && event.detail.value) {
                            this.atualizarDadosTanque(event.detail.value);
                        }
                    });
                },

                atualizarDadosTanque(id) {
                    this.id_tanque = 'Carregando...'; // Mensagem temporária
                    this.tanques = []; // Limpar tanques anteriores

                    fetch('/admin/recebimentocombustiveis/get-tank-data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                fornecedor: id // CORREÇÃO: mudou de 'modelo' para 'filial'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.error) {
                                this.tanques = data.tanques || [];
                                this.id_tanque = this.tanques.length > 0 ? 'Selecionar um tanque...' :
                                    'Nenhum tanque encontrado';

                                console.log(
                                    `${data.total} tanques encontrados para esta filial`
                                ); // CORREÇÃO: template literal corrigido
                            } else {
                                console.error('Erro ao buscar dados do tanque:', data.error);
                                this.id_tanque = 'Erro ao carregar';
                                this.tanques = [];
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar dados do tanque:', error);
                            this.id_tanque = 'Erro ao carregar';
                            this.tanques = [];
                        });
                }
            };
        }
    </script>

    <script>
        // Função genérica para formatar números
        function formatarNumero(input, casasDecimais) {
            // Validação simples durante a digitação (sem formatação)
            input.addEventListener('input', function(e) {
                let valor = e.target.value;

                // Remove caracteres inválidos mas mantém números, vírgula e ponto
                let valorLimpo = valor.replace(/[^\d.,]/g, '');

                // Garante apenas uma vírgula ou ponto decimal
                let partes = valorLimpo.split(/[.,]/);
                if (partes.length > 2) {
                    valorLimpo = partes[0] + ',' + partes[1];
                }

                // Atualiza o valor apenas se houve mudança
                if (valorLimpo !== valor) {
                    let posicaoCursor = e.target.selectionStart;
                    e.target.value = valorLimpo;
                    e.target.setSelectionRange(posicaoCursor, posicaoCursor);
                }
            });

            // Formatação quando o campo perde o foco
            input.addEventListener('blur', function(e) {
                let valor = e.target.value;

                if (valor && valor.trim() !== '') {
                    // Remove caracteres não numéricos, exceto vírgula e ponto
                    valor = valor.replace(/[^\d.,]/g, '');

                    // Converte para número
                    let numero = parseFloat(valor.replace(/\./g, '').replace(',', '.'));

                    if (!isNaN(numero)) {
                        e.target.value = numero.toLocaleString('pt-BR', {
                            minimumFractionDigits: casasDecimais,
                            maximumFractionDigits: casasDecimais
                        });
                    }
                }
            });
        }

        // Aplicar formatação aos campos quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            const campoQuantidade = document.getElementById('quantidade');
            const campoPrecoTotal = document.getElementById('preco_total_item');
            const campoValorUnitario = document.getElementById('valor_unitario');
            const campoTemperatura = document.getElementById('temperatura_combustivel');
            const campoDensidade = document.getElementById('densidade_combustivel');

            if (campoQuantidade) formatarNumero(campoQuantidade, 2);
            if (campoPrecoTotal) formatarNumero(campoPrecoTotal, 2);
            if (campoValorUnitario) formatarNumero(campoValorUnitario,
                4); // Adicionar formatação para 4 casas decimais
            // Temperatura e Densidade não precisam de formatação pois são readonly e preenchidos pelo sistema
            //if (campoTemperatura) formatarNumero(campoTemperatura, 1);
            //if (campoDensidade) formatarNumero(campoDensidade, 4);
        });



        // Configurar event listeners =====================
        // Variável para verificar se está no modo de criação
        const isCreate = {{ $isCreate ? 'true' : 'false' }};

        document.getElementById('botao-buscar-pedido').addEventListener('click', buscarPedido);
        document.getElementById('botao-limpar').addEventListener('click', limparFormulario);

        function onCalcularValorUnitario() {
            const quantidade = document.getElementById('quantidade').value;
            const valorTotal = document.getElementById('preco_total_item').value;
            const valorUnitarioField = document.getElementById('valor_unitario');

            if (quantidade && valorTotal) {
                // Converter valores removendo formatação brasileira
                const qtdNumerico = parseFloat(quantidade.replace(/\./g, '').replace(',', '.'));
                const valorNumerico = parseFloat(valorTotal.replace(/\./g, '').replace(',', '.'));

                if (!isNaN(qtdNumerico) && !isNaN(valorNumerico) && qtdNumerico > 0) {
                    const valorUnitario = valorNumerico / qtdNumerico;
                    // Formatar para 4 casas decimais no padrão brasileiro
                    valorUnitarioField.value = valorUnitario.toLocaleString('pt-BR', {
                        minimumFractionDigits: 4,
                        maximumFractionDigits: 4
                    });
                }
            }
        }

        function onDensidade() {
            // Só calcula o volume convertido se estiver no modo de criação
            if (!isCreate) {
                return;
            }

            let densidade_inicial = document.getElementById('densidade_combustivel').value;
            let temperatura = document.getElementById('temperatura_combustivel').value;
            let quantidade = document.getElementById('quantidade').value;

            if (!densidade_inicial || !temperatura || !quantidade) {
                return;
            } else {
                // Função auxiliar para converter string para número
                // Se tem vírgula, é formato brasileiro (ex: 1.234,56)
                // Se só tem ponto, é formato americano ou decimal simples (ex: 0.83 ou 26.3)
                function parseNumero(valor) {
                    if (valor.includes(',')) {
                        // Formato brasileiro: remove pontos de milhar, troca vírgula por ponto
                        return parseFloat(valor.replace(/\./g, '').replace(',', '.'));
                    } else {
                        // Formato americano ou decimal simples: mantém o ponto
                        return parseFloat(valor);
                    }
                }

                densidade_inicial = parseNumero(densidade_inicial);
                temperatura = parseNumero(temperatura);
                quantidade = parseNumero(quantidade);

                if (densidade_inicial && temperatura && quantidade) {

                    let nova_densidade = densidade20(temperatura, densidade_inicial);

                    if (nova_densidade) {
                        let fatorcorrecao = fator_correcao(temperatura, nova_densidade);

                        let novo_volume = quantidade * fatorcorrecao;

                        // Formatar o volume convertido no padrão brasileiro
                        document.getElementById('volume_convertido').value = novo_volume.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        }


        function densidade20(tp, Dens) {
            const tb = [
                [-0.0045946489678832, 0.006123243179568, -0.0000317074831323, 0.0000548397230037, 0.498],
                [-0.0044617912555853, 0.004064744943305, -0.0000263476576878, 0.0000445167480908, 0.518],
                [-0.0042635157420811, 0.005464985535707, -0.0000263293867091, 0.0000438862463121, 0.539],
                [-0.0039313336083154, 0.0048491424883736, -0.0000171988380071, 0.0000271198135388, 0.559],
                [-0.0035459928199062, 0.0041555627486597, -0.000017408240554, 0.0000272052538293, 0.579],
                [-0.0044795785597695, 0.0057678078599348, -0.0000384017053043, 0.0000636945533674, 0.6],
                [-0.0024361018961719, 0.0023329279647329, -0.0000015650912583, 0.0000019239173808, 0.615],
                [-0.0022189302188433, 0.0019797818956931, -0.0000015669676937, 0.00000192696868, 0.635],
                [-0.0019375650211733, 0.0015367709455658, -0.0000015693987823, 0.0000019307964416, 0.655],
                [-0.0018211308776797, 0.0013590733713544, -0.000001570404812, 0.0000019323318076, 0.675],
                [-0.001761056212754, 0.0012701185634916, -0.000001570923877, 0.0000019331004067, 0.695],
                [-0.0018105498111601, 0.0013412880691687, -0.0000015704962359, 0.0000019324854785, 0.746],
                [-0.0022215907273459, 0.0018913202829246, -0.000001566944706, 0.0000019277330177, 0.766],
                [-0.001950066973645, 0.0015367709455658, -0.0000015692907613, 0.0000019307964416, 0.786],
                [-0.0017395987201258, 0.0012701185634916, -0.0000015711092769, 0.0000019331004067, 0.806],
                [-0.001523256792873, 0.001002829043573, -0.000015729785429, 0.0000019354098768, 0.826],
                [-0.0013028125169482, 0.0007349000995177, -0.0000015748832546, 0.0000019377248718, 0.846],
                [-0.0011210535017199, 0.0005200950155166, -0.0000015764537128, 0.000001939580859, 0.871],
                [-0.0009335584519317, 0.0003048780487805, -0.0000015780737322, 0.000001941440405, 0.896],
                [-0.0007238306025284, 0.0000712601611711, -0.0000015798858504, 0.000001943458941, 0.996],
                [-0.0009082062326932, 0.0002563514599689, 0.0000074474093761, -0.0000071188764479, 0.9999]
            ];

            const dT = tp - 20;
            const hyc = 1 - 0.000023 * dT - 0.00000002 * Math.pow(dT, 2);

            let d20 = 0;
            for (let x = 0; x < tb.length; x++) {
                d20 = Dens - tb[x][0] * dT - tb[x][2] * Math.pow(dT, 2);
                d20 = d20 / (1 + tb[x][1] * dT + tb[x][3] * Math.pow(dT, 2));
                d20 = d20 * hyc;
                if (d20 <= tb[x][4]) break;
            }

            d20 = d20 > 0.65 ? Number(d20.toFixed(4)) : Number(d20.toFixed(3));
            return d20;
        }


        function fator_correcao(tp, d20) {
            const tb = [
                [-0.0045946489678832, 0.006123243179568, -0.0000317074831323, 0.0000548397230037, 0.498],
                [-0.0044617912555853, 0.004064744943305, -0.0000263476576878, 0.0000445167480908, 0.518],
                [-0.0042635157420811, 0.005464985535707, -0.0000263293867091, 0.0000438862463121, 0.539],
                [-0.0039313336083154, 0.0048491424883736, -0.0000171988380071, 0.0000271198135388, 0.559],
                [-0.0035459928199062, 0.0041555627486597, -0.000017408240554, 0.0000272052538293, 0.579],
                [-0.0044795785597695, 0.0057678078599348, -0.0000384017053043, 0.0000636945533674, 0.6],
                [-0.0024361018961719, 0.0023329279647329, -0.0000015650912583, 0.0000019239173808, 0.615],
                [-0.0022189302188433, 0.0019797818956931, -0.0000015669676937, 0.00000192696868, 0.635],
                [-0.0019375650211733, 0.0015367709455658, -0.0000015693987823, 0.0000019307964416, 0.655],
                [-0.0018211308776797, 0.0013590733713544, -0.000001570404812, 0.0000019323318076, 0.675],
                [-0.001761056212754, 0.0012701185634916, -0.000001570923877, 0.0000019331004067, 0.695],
                [-0.0018105498111601, 0.0013412880691687, -0.0000015704962359, 0.0000019324854785, 0.746],
                [-0.0022215907273459, 0.0018913202829246, -0.000001566944706, 0.0000019277330177, 0.766],
                [-0.001950066973645, 0.0015367709455658, -0.0000015692907613, 0.0000019307964416, 0.786],
                [-0.0017395987201258, 0.0012701185634916, -0.0000015711092769, 0.0000019331004067, 0.806],
                [-0.001523256792873, 0.001002829043573, -0.000015729785429, 0.0000019354098768, 0.826],
                [-0.0013028125169482, 0.0007349000995177, -0.0000015748832546, 0.0000019377248718, 0.846],
                [-0.0011210535017199, 0.0005200950155166, -0.0000015764537128, 0.000001939580859, 0.871],
                [-0.0009335584519317, 0.0003048780487805, -0.0000015780737322, 0.000001941440405, 0.896],
                [-0.0007238306025284, 0.0000712601611711, -0.0000015798858504, 0.000001943458941, 0.996],
                [-0.0009082062326932, 0.0002563514599689, 0.0000074474093761, -0.0000071188764479, 0.9999],
            ];

            const dT = tp - 20;
            let x;

            for (x = 0; x < tb.length; x++) {
                if (d20 <= tb[x][4]) break;
            }

            // Verificar se chegamos ao final da tabela sem encontrar um valor adequado
            if (x >= tb.length) {
                // Duas opções: usar o último elemento da tabela ou retornar um valor padrão
                x = tb.length - 1; // Usar o último elemento como fallback
            }

            let fator = 1 + tb[x][1] * dT + tb[x][3] * Math.pow(dT, 2);
            fator = fator + (tb[x][0] * dT + tb[x][2] * Math.pow(dT, 2)) / d20;
            fator = Math.round(fator * 10000) / 10000;

            return fator;
        }

        //==============//
        function consultarPedido(pedidoId) {
            return new Promise((resolve, reject) => {
                fetch("{{ route('admin.recebimentocombustiveis.getPedido') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            pedidoId: pedidoId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            resolve(data);
                        } else {
                            alert(data.error);
                            reject(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar o pedido:', error);
                        alert("Erro ao buscar o pedido.");
                        reject(error);
                    });
            });
        }

        function pedidoJaBaixado(idPedido) {
            return new Promise((resolve, reject) => {
                fetch("{{ route('admin.recebimentocombustiveis.pedidoJaBaixado') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            idPedido: idPedido
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasOwnProperty('result')) {
                            resolve(data.result);
                        } else if (data.error) {
                            alert(data.error);
                            reject(data.error);
                        } else {
                            resolve(false);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao verificar status do pedido:', error);
                        alert("Erro ao verificar status do pedido.");
                        reject(error);
                    });
            });
        }

        // Altere a declaração da função para async
        async function buscarPedido() {
            const pedidoId = document.getElementById('id_pedido').value;

            if (!pedidoId) {
                alert('Atenção: Informar o número do pedido de compras para fazer o lançamento da NF.');
                return;
            }

            // Definir data e hora atual se o campo estiver vazio
            var input = document.getElementById('data_entrada');
            if (input && !input.value) {
                var now = new Date();
                var year = now.getFullYear();
                var month = String(now.getMonth() + 1).padStart(2, '0');
                var day = String(now.getDate()).padStart(2, '0');
                var hours = String(now.getHours()).padStart(2, '0');
                var minutes = String(now.getMinutes()).padStart(2, '0');
                // Formato: yyyy-MM-ddTHH:mm
                var formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
                input.value = formatted;
            }

            const data = await consultarPedido(pedidoId);

            //selecionar a matriz do pedido
            setSmartSelectValue('id_filial', data.id_filial, {
                createIfNotFound: false,
            });

            //Selecionar o fornecedor do pedido
            setSmartSelectValue('id_fornecedor', data.id_fornecedor, {
                createIfNotFound: true,
                tempLabel: data.nome_fornecedor || 'Fornecedor do Pedido',
                triggerEvents: true
            });

            // Formatar valor total no padrão brasileiro
            if (data.valor_total) {
                const valorTotal = parseFloat(data.valor_total);
                document.getElementById("preco_total_item").value = valorTotal.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Formatar quantidade no padrão brasileiro
            if (data.quantidade_produtos) {
                const quantidade = parseFloat(data.quantidade_produtos);
                document.getElementById("quantidade").value = quantidade.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Aguardar um pouco para os campos serem preenchidos antes de calcular
            setTimeout(() => {
                onCalcularValorUnitario();
            }, 100);
        }

        function onPedidoChange() {
            const idPedido = document.getElementById('id_pedido').value;

            if (!idPedido) return;

            fetch(window.routeVerificarPedido, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        idPedido: idPedido
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    if (data.result === true) {
                        alert('Este pedido já foi baixado no sistema.');
                    } else {
                        buscarPedido();
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar pedido:', error);
                });
        }

        function limparFormulario() {
            const form = document.getElementById('recebimentoCombustiveisForm');

            // Resetar formulário
            form.reset();

            // Limpar campos específicos que podem não ser resetados pelo reset() do formulário
            const camposParaLimpar = [
                'id_filial', 'numeronotafiscal', 'chave_nf',

                'numero_nf2', 'chave_nf2', 'numero_nf3', 'chave_nf3',
                'numero_nf4', 'chave_nf4', 'data_entrada',
                'preco_total_item', 'valor_unitario', 'quantidade',
                'temperatura_combustivel', 'densidade_combustivel',
                'volume_convertido'
            ];

            camposParaLimpar.forEach(campo => {

                const elemento = document.getElementById(campo);
                if (elemento) elemento.value = '';
            });
        }

        async function onPreencher() {
            const idPedido = document.getElementById("pedido_id").value;

            if (!idPedido) return;

            try {
                // Verificar se o pedido já foi baixado
                const jaBaixado = await pedidoJaBaixado(idPedido);

                if (jaBaixado === false) {
                    // Se não foi baixado, buscar detalhes do pedido
                    const data = await consultarPedido(idPedido);

                    if (data) {
                        // Preencher campos com os dados retornados
                        document.getElementById("id_filial").value = data.id_filial || '';
                        document.getElementById("data_entrada").value = data.data_entrada || '';
                        document.getElementById("preco_total_item").value = data.valor_total || '';
                        document.getElementById("quantidade").value = data.quantidade_produtos || '';
                        document.getElementById("id_fornecedor").value = data.id_fornecedor || '';

                        // Calcular valor unitário automaticamente
                        onCalcularValorUnitario();
                    }
                } else {
                    alert("Este pedido já foi baixado no sistema.");
                }
            } catch (error) {
                console.error("Erro ao processar pedido:", error);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Configurar as rotas para as chamadas AJAX
            window.routePedido = "{{ route('admin.recebimentocombustiveis.getPedido') }}";
            window.routeVerificarPedido = "{{ route('admin.recebimentocombustiveis.pedidoJaBaixado') }}";
            window.csrfToken = "{{ csrf_token() }}";

            // Inicializar os event listeners para os campos de cálculo
            const quantidade = document.getElementById('quantidade');
            const precoTotal = document.getElementById('preco_total_item');
            const temperatura = document.getElementById('temperatura_combustivel');
            const densidade = document.getElementById('densidade_combustivel');

            if (quantidade) {
                quantidade.addEventListener('input', function() {
                    onCalcularValorUnitario();
                    // Só chama onDensidade se estiver no modo create
                    if (isCreate) {
                        onDensidade();
                    }
                });
            }

            if (precoTotal) {
                precoTotal.addEventListener('input', onCalcularValorUnitario);
            }

            // Só adiciona listeners de temperatura e densidade se estiver no modo create
            if (isCreate) {
                if (temperatura) {
                    temperatura.addEventListener('input', onDensidade);
                }

                if (densidade) {
                    densidade.addEventListener('input', onDensidade);
                }
            }

            // Inicializar os botões
            const botaoBuscarPedido = document.getElementById('botao-buscar-pedido');
            const botaoLimpar = document.getElementById('botao-limpar');
            const inputPedido = document.getElementById('id_pedido');

            if (botaoBuscarPedido) {
                botaoBuscarPedido.addEventListener('click', buscarPedido);
            }

            if (botaoLimpar) {
                botaoLimpar.addEventListener('click', limparFormulario);
            }

            if (inputPedido) {
                inputPedido.addEventListener('change', onPedidoChange);
            }

            // Executar cálculos iniciais se houver dados
            onCalcularValorUnitario();
            // Só executa onDensidade inicial se estiver no modo create
            if (isCreate) {
                onDensidade();
            }
        });
    </script>
@endpush
