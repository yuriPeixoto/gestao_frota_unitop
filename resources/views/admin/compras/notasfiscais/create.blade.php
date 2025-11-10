<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Nota Fiscal Avulsa') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.compras.dashboard') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.chevron-left class="h-4 w-4 mr-2" /> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <form id="notaFiscalForm" method="POST" action="{{ route('admin.compras.avulsas.store') }}"
                    class="space-y-6" x-data="notaFiscalForm()">
                    @csrf

                    <!-- Mensagens de erro -->
                    @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm leading-5 font-medium text-red-800">
                                    Erro ao processar formulário:
                                </p>
                                <ul class="mt-1 text-sm leading-5 text-red-700">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Sessão para mensagens de sucesso ou erro -->
                    @if (session('success'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm leading-5 text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm leading-5 text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Seção de Pedido -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informações do Pedido</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2 flex space-x-2">
                                <div class="flex-grow">
                                    <label for="numero_do_pedido" class="block text-sm font-medium text-gray-700">Número
                                        do Pedido</label>
                                    <input type="text" id="numero_do_pedido" name="numero_do_pedido" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ old('numero_do_pedido') }}" x-model="numeroPedido">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="buscarPedido()"
                                        class="mb-1 inline-flex items-center py-2 px-4 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <div x-show="erroPedido" class="md:col-span-3">
                                <div class="bg-red-50 border-l-4 border-red-500 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm leading-5 text-red-800" x-text="mensagemErroPedido"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção da Nota Fiscal -->
                    <div class="bg-gray-50 p-4 rounded-lg">

                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informações da Nota Fiscal</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="w-full">
                                <label for="id_fornecedor" class="block text-sm font-medium text-gray-700">
                                    Fornecedor
                                </label>
                                <x-forms.smart-select name="id_fornecedor" placeholder="Selecione o fornecedor..."
                                    :options="$fornecedores" :searchUrl="route('admin.api.fornecedores.search')"
                                    :selected="old('id_fornecedor')" asyncSearch="true" required="true"
                                    class="w-full" />
                            </div>



                            <div>
                                <label for="numero_nf" class="block text-sm font-medium text-gray-700">Número da
                                    NF</label>
                                <input type="number" id="numero_nf" name="numero_nf" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('numero_nf') }}">
                            </div>

                            <div>
                                <label for="serie_nf" class="block text-sm font-medium text-gray-700">Série</label>
                                <input type="number" id="serie_nf" name="serie_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('serie_nf') }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="chave_nf" class="block text-sm font-medium text-gray-700">Chave da
                                    NF</label>
                                <input type="text" id="chave_nf" name="chave_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    maxlength="50" value="{{ old('chave_nf') }}">
                            </div>

                            <div>
                                <label for="data_emissao" class="block text-sm font-medium text-gray-700">Data de
                                    Emissão</label>
                                <input type="date" id="data_emissao" name="data_emissao" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('data_emissao', date('Y-m-d')) }}">
                            </div>

                            <div>
                                <label for="valor_total_nf" class="block text-sm font-medium text-gray-700">Valor
                                    Total</label>
                                <input type="text" id="valor_total_nf" name="valor_total_nf" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_total_nf') }}" x-bind:value="valorTotalFormatado"
                                    x-on:input="formatarValor($event.target, 'valorTotal')"
                                    x-on:blur="formatarValor($event.target, 'valorTotal', true)">
                            </div>

                            <div>
                                <label for="valor_pecas" class="block text-sm font-medium text-gray-700">Valor de
                                    Peças</label>
                                <input type="text" id="valor_pecas" name="valor_pecas"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_pecas') }}" x-bind:value="valorPecasFormatado"
                                    x-on:input="formatarValor($event.target, 'valorPecas')"
                                    x-on:blur="formatarValor($event.target, 'valorPecas', true)">
                            </div>
                        </div>


                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="limparFormulario"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.trash class="h-4 w-4 mr-2" />
                            Limpar
                        </button>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.check class="h-4 w-4 mr-2" />
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Inicializa o componente de nota fiscal
        const notaFiscalApp = {
            ...notaFiscalForm(),
            
            init() {
                this.setupFornecedorMonitor();
                this.setupVeiculoMonitor();
            },
            
            setupFornecedorMonitor() {
                const fornecedorField = document.querySelector('[name="id_fornecedor"]');
                if (!fornecedorField) return;

                // Observar mutações no DOM para detectar mudanças no select
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            console.log('Fornecedor alterado via DOM:', fornecedorField.value);
                            this.onFornecedorChange(fornecedorField.value);
                        }
                    });
                });

                observer.observe(fornecedorField, {
                    attributes: true,
                    attributeFilter: ['value']
                });
            },
            
            setupVeiculoMonitor() {
                const veiculoField = document.querySelector('[name="id_veiculo"]');
                if (!veiculoField) return;

                let lastValue = veiculoField.value;
                
                setInterval(() => {
                    if (veiculoField.value !== lastValue) {
                        lastValue = veiculoField.value;
                        console.log('Veículo alterado:', lastValue);
                        this.onVeiculoChange(lastValue);
                    }
                }, 500);
            },
            
            onFornecedorChange(idFornecedor) {
                // Lógica para quando o fornecedor mudar
            },
            
            onVeiculoChange(idVeiculo) {
                if (!idVeiculo) return;
                
                fetch(`/admin/calibragempneus/pneus-veiculo/${idVeiculo}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Dados dos pneus recebidos:', data);
                        popularTabelaPneus(data);
                    })
                    .catch(err => console.error('Erro ao buscar pneus do veículo:', err));
            }
        };
        
        // Inicializa o monitoramento
        notaFiscalApp.init();
            });

            function notaFiscalForm() {
                return {
                    numeroPedido: "{{ old('numero_do_pedido') }}",
                    valorTotal: "{{ old('valor_total_nf', '0,00') }}",
                    valorPecas: "{{ old('valor_pecas', '0,00') }}",
                    valorTotalFormatado: "{{ old('valor_total_nf', '0,00') }}",
                    valorPecasFormatado: "{{ old('valor_pecas', '0,00') }}",
                    fornecedor: "{{ old('id_fornecedor') }}",
                    pedidoCarregado: false,
                    erroPedido: false,
                    mensagemErroPedido: '',
                    
                    async buscarPedido() {
                        if (!this.numeroPedido) {
                            this.erroPedido = true;
                            this.mensagemErroPedido = 'Informe o número do pedido';
                            return;
                        }
                        
                        this.erroPedido = false;
                        this.mensagemErroPedido = '';

                        try {
                            const response = await fetch(`{{ route('admin.compras.avulsas.notasfiscais.avulsas.buscar-pedido') }}?numero_pedido=${this.numeroPedido}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (!response.ok) {
                                // Se o status for 403, significa que é pedido tipo Ordem de Serviço
                                if (response.status === 403) {
                                    const errData = await response.json();
                                    this.erroPedido = true;
                                    this.mensagemErroPedido = errData.error || 'Pedido do tipo Ordem de Serviço, não permitido.';
                                    this.limparCamposNotaFiscal();
                                    return;
                                }
                                throw new Error('Erro na requisição');
                            }

                            const data = await response.json();
                            console.log('Dados recebidos:', data);
                            
                            if (data.pedido) {
                                if (data.fornecedor) {
                                    await this.preencherFornecedor(data.fornecedor.id_fornecedor, data.fornecedor.nome_fornecedor);
                                    this.forcarAtualizacaoSelect('id_fornecedor', data.fornecedor.id_fornecedor);
                                }
                                if (data.nota_fiscal) {
                                    this.preencherCamposNotaFiscal(data.nota_fiscal);
                                } else {
                                    this.limparCamposNotaFiscal();
                                }
                                this.pedidoCarregado = true;
                            } else if(data.error) {
                                this.erroPedido = true;
                                this.mensagemErroPedido = data.error;
                                this.limparCamposNotaFiscal();
                            }
                        } catch (error) {
                            console.error('Erro ao buscar pedido:', error);
                            this.erroPedido = true;
                            this.mensagemErroPedido = 'Erro ao buscar pedido. Verifique o console para mais detalhes.';
                            this.limparCamposNotaFiscal();
                        }
                    },


                    forcarAtualizacaoSelect(name, value) {
                        // Método nuclear para garantir que o select mostre o valor correto
                        const select = document.querySelector(`select[name="${name}"]`);
                        if (!select) return;

                        // 1. Disparar todos os eventos possíveis
                        select.value = value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        select.dispatchEvent(new Event('focus', { bubbles: true }));
                        select.dispatchEvent(new Event('blur', { bubbles: true }));

                        // 2. Se for um select customizado (como o TomSelect)
                        if (select.tomselect) {
                            select.tomselect.setValue(value, true);
                            select.tomselect.refreshOptions(false);
                        }

                        // 3. Se estiver usando Alpine.js
                        if (select._x_model) {
                            select._x_model.set(value);
                        }

                        // 4. Disparar evento customizado para smart-select
                        window.dispatchEvent(new CustomEvent('smart-select:force-update', {
                            detail: { name, value }
                        }));

                        console.log(`Forçada atualização do select ${name} para valor`, value);
                    },

                    async preencherFornecedor(id, nome) {
                        try {
                            // 1. Tentar via evento customizado primeiro
                            const success = await this.tentarPreencherViaEvento(id, nome);
                            if (success) return;
                            
                            // 2. Tentar via API do componente
                            if (await this.tentarPreencherViaAPI(id, nome)) return;
                            
                            // 3. Fallback DOM
                            if (await this.tentarPreencherViaDOM(id, nome)) return;
                            
                            // 4. Último recurso
                            this.preencherFornecedorNuclear(id, nome);
                            
                        } catch (e) {
                            console.error('Erro ao preencher fornecedor:', e);
                            this.preencherFornecedorNuclear(id, nome);
                        }
                    },

                    async tentarPreencherViaEvento(id, nome) {
                        try {
                            // Disparar evento específico do smart-select
                            window.dispatchEvent(new CustomEvent('smart-select:set-value', {
                                detail: {
                                    name: 'id_fornecedor',
                                    value: id,
                                    label: nome,
                                    object: { value: id, label: nome }
                                }
                            }));
                            
                            // Disparar evento genérico
                            window.dispatchEvent(new CustomEvent('option-selected', {
                                detail: {
                                    targetId: 'id_fornecedor',
                                    value: id,
                                    label: nome,
                                    object: { value: id, label: nome }
                                }
                            }));

                            await new Promise(resolve => setTimeout(resolve, 100));
                            return true;
                        } catch (e) {
                            console.warn('Falha ao preencher via evento:', e);
                            return false;
                        }
                    },

                    async tentarPreencherViaAPI(id, nome) {
                        try {
                            if (this.$refs.fornecedorSelect && this.$refs.fornecedorSelect.setValue) {
                                await this.$refs.fornecedorSelect.setValue(id, nome);
                                await new Promise(resolve => setTimeout(resolve, 50));
                                return true;
                            }
                        } catch (e) {
                            console.warn('Falha ao preencher via API do componente:', e);
                        }
                        return false;
                    },

                    async tentarPreencherViaDOM(id, nome) {
                        try {
                            const selectReal = document.querySelector('select[name="id_fornecedor"]');
                            if (!selectReal) return false;

                            const estavaDesabilitado = selectReal.disabled;
                            if (estavaDesabilitado) {
                                selectReal.disabled = false;
                                await this.$nextTick();
                            }

                            let option = Array.from(selectReal.options).find(opt => opt.value == id);
                            if (!option) {
                                option = new Option(nome, id, true, true);
                                selectReal.add(option);
                            } else {
                                option.selected = true;
                            }

                            selectReal.value = id;
                            selectReal.dispatchEvent(new Event('change', { bubbles: true }));
                            selectReal.dispatchEvent(new Event('input', { bubbles: true }));

                            if (selectReal._x_model && typeof selectReal._x_model.set === 'function') {
                                selectReal._x_model.set(id);
                            }

                            if (estavaDesabilitado) {
                                selectReal.disabled = true;
                            }
                            
                            await new Promise(resolve => setTimeout(resolve, 50));
                            return true;
                        } catch (e) {
                            console.warn('Falha ao preencher via manipulação DOM:', e);
                            return false;
                        }
                    },

                    preencherCamposNotaFiscal(notaFiscal) {
                        document.getElementById('numero_nf').value = notaFiscal.numero_nf || '';
                        document.getElementById('serie_nf').value = notaFiscal.serie_nf || '';
                        document.getElementById('chave_nf').value = notaFiscal.chave_nf || '';
                        document.getElementById('data_emissao').value = notaFiscal.data_emissao || "{{ date('Y-m-d') }}";

                        this.valorTotal = notaFiscal.valor_total_nf ? parseFloat(notaFiscal.valor_total_nf).toFixed(2).replace('.', ',') : '0,00';
                        this.valorPecas = notaFiscal.valor_pecas ? parseFloat(notaFiscal.valor_pecas).toFixed(2).replace('.', ',') : '0,00';

                        this.valorTotalFormatado = this.valorTotal;
                        this.valorPecasFormatado = this.valorPecas;
                    },

                    limparCamposNotaFiscal() {
                        document.getElementById('numero_nf').value = '';
                        document.getElementById('serie_nf').value = '';
                        document.getElementById('chave_nf').value = '';
                        document.getElementById('data_emissao').value = "{{ date('Y-m-d') }}";

                        this.valorTotal = '0,00';
                        this.valorPecas = '0,00';
                        this.valorTotalFormatado = '0,00';
                        this.valorPecasFormatado = '0,00';
                    },

                    formatarValor(el, prop, isFinal = false) {
                        let value = el.value.replace(/\D/g, '');
                        if (value === '') value = '0';

                        const valueNumber = parseInt(value) / 100;
                        this[prop] = valueNumber.toString().replace('.', ',');

                        if (isFinal) {
                            el.value = valueNumber.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            this[prop + 'Formatado'] = el.value;
                        } else {
                            el.value = value;
                        }
                    },

                    limparFormulario() {
                        if (!confirm('Tem certeza que deseja limpar todos os dados do formulário?')) return;

                        this.numeroPedido = '';
                        this.limparCamposNotaFiscal();
                        this.pedidoCarregado = false;
                        this.erroPedido = false;
                        this.mensagemErroPedido = '';

                        const event = new CustomEvent('reset-smart-select', {
                            detail: { name: 'id_fornecedor' }
                        });
                        window.dispatchEvent(event);
                    },

                    preencherFornecedorNuclear(id, nome) {
                        // Método de último recurso para preencher o fornecedor
                        console.warn('Usando método nuclear para preencher fornecedor');
                        
                        // 1. Tentar encontrar o select real
                        const selectReal = document.querySelector('select[name="id_fornecedor"]');
                        if (selectReal) {
                            selectReal.value = id;
                            selectReal.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        
                        // 2. Tentar encontrar o input de exibição
                        const displayInput = document.querySelector('input[name="id_fornecedor"]');
                        if (displayInput) {
                            displayInput.value = nome;
                            displayInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        
                        // 3. Disparar eventos customizados
                        window.dispatchEvent(new CustomEvent('smart-select:set-value', {
                            detail: {
                                name: 'id_fornecedor',
                                value: id,
                                label: nome
                            }
                        }));
                    }
                };
            }

            // Função para popular tabela de pneus (mantida do seu código original)
            function popularTabelaPneus(data) {
                // Implemente conforme sua necessidade
            }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração do monitoramento do select
            const selectName = 'id_fornecedor';
            let ultimoValorConhecido = null;
            
            // Função para forçar a atualização completa do select
            function forcarAtualizacaoSelect(value, label) {
                // 1. Tentar via TomSelect (se for o caso)
                const tomSelect = document.querySelector(`select[name="${selectName}"]`)?.tomselect;
                if (tomSelect) {
                    tomSelect.setValue(value, true);
                    tomSelect.refreshOptions();
                    return true;
                }
                
                // 2. Atualizar o select nativo
                const select = document.querySelector(`select[name="${selectName}"]`);
                if (select) {
                    select.value = value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // 3. Atualizar inputs associados (comum em selects customizados)
                document.querySelectorAll(`input[name="${selectName}"], input[name="${selectName}_display"]`).forEach(input => {
                    input.value = label || value;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });
                
                // 4. Disparar eventos customizados
                window.dispatchEvent(new CustomEvent('smart-select:update', {
                    detail: { name: selectName, value, label }
                }));
                
                console.log('Forçada atualização do select', selectName, 'para valor', value);
                return true;
            }
            
            // Função para substituir nuclearmente o select
            function substituirSelect() {
                const container = document.querySelector(`[name="${selectName}"]`).closest('.select-container, .relative');
                if (!container) return false;
                
                const novoContainer = container.cloneNode(true);
                container.parentNode.replaceChild(novoContainer, container);
                console.log('Select substituído nuclearmente');
                return true;
            }
            
            // Monitoramento periódico
            setInterval(() => {
                const select = document.querySelector(`select[name="${selectName}"]`);
                if (!select) return;
                
                // Verificar se o valor atual não está sendo exibido
                const valorAtual = select.value;
                const displayValue = document.querySelector(`input[name="${selectName}_display"]`)?.value || '';
                
                if (valorAtual && valorAtual !== ultimoValorConhecido && !displayValue.includes(valorAtual)) {
                    console.log('Valor não está visível - forçando atualização...');
                    
                    // Primeira tentativa: atualização normal
                    if (!forcarAtualizacaoSelect(valorAtual)) {
                        // Segunda tentativa: substituição nuclear
                        setTimeout(() => {
                            substituirSelect();
                            forcarAtualizacaoSelect(valorAtual);
                        }, 300);
                    }
                    
                    ultimoValorConhecido = valorAtual;
                }
            }, 500); // Verifica a cada 500ms
            
            // Disparar manualmente quando o pedido for carregado
            window.buscarPedidoSuccess = function(data) {
                if (data.fornecedor) {
                    setTimeout(() => {
                        forcarAtualizacaoSelect(data.fornecedor.id_fornecedor, data.fornecedor.nome_fornecedor);
                    }, 300);
                }
            };
        });
    </script>
    @endpush
</x-app-layout>