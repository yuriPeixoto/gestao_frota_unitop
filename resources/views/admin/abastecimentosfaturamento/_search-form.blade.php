<form method="GET" action="{{ route('admin.abastecimentosfaturamento.index') }}" class="space-y-4"
    hx-get="{{ route('admin.abastecimentosfaturamento.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
        <h3 class="text-lg font-medium text-blue-800 mb-2">Leitura de Chave NF</h3>
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="chave_nf_scanner" class="block text-sm font-medium text-gray-700 mb-1">
                    Escaneie o código de barras da Chave NF
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="text" id="chave_nf_scanner"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300"
                        placeholder="Posicione o leitor sobre o código de barras..." autocomplete="off">
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Ou digite manualmente o código da Chave NF e pressione Enter
                </p>
            </div>
            <div class="flex items-end">
                <button type="button" id="btn-limpar-selecoes"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Limpar seleções
                </button>
            </div>
        </div>
        <div id="chave-nf-message" class="mt-2 text-sm"></div>
    </div>

    {{-- Indicador de seleções ativas - Versão melhorada com Alpine.js --}}
    <div id="selecoes-ativas" x-data="{
        selectedCount: 0,
        checkCount() {
            try {
                const storedData = localStorage.getItem('selectedRows');
                const parsedData = storedData ? JSON.parse(storedData) : [];
                this.selectedCount = Array.isArray(parsedData) ? parsedData.length : 0;
            } catch (e) {
                console.error('Erro ao verificar seleções:', e);
                this.selectedCount = 0;
            }
        }
    }" x-init="checkCount();
    setInterval(() => checkCount(), 500);
    $watch('selectedCount', value => console.log('Valor de selectedCount mudou:', value));
    window.addEventListener('storage-update', () => { checkCount(); });" @storage-change.window="checkCount()"
        @storage.window="checkCount()" class="bg-indigo-50 border border-indigo-200 rounded-md p-3 mb-4"
        x-show="selectedCount > 0" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center text-indigo-800 mb-2 sm:mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="`${selectedCount} item(s) selecionado(s) em todas as páginas`"></span>
            </div>
            <div class="flex space-x-2">
                <button type="button"
                    @click="localStorage.removeItem('selectedRows'); checkCount(); window.dispatchEvent(new Event('storage-update'));"
                    class="inline-flex items-center px-3 py-1 text-xs border border-indigo-300 rounded-md text-indigo-700 bg-white hover:bg-indigo-50">
                    Limpar seleções
                </button>
                <button type="button"
                    @click="
                        if (selectedCount > 0) {
                            const ids = JSON.parse(localStorage.getItem('selectedRows')).join(',');
                            window.location.href = `/admin/abastecimentosfaturamento/create?ids=${ids}`;
                        }
                    "
                    class="inline-flex items-center px-3 py-1 text-xs border border-transparent rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <x-icons.clipboard-document-check class="w-4 h-4 mr-1" />
                    Faturar selecionados
                </button>
            </div>
        </div>
    </div>

    {{-- Usar o mesmo padrão de grid que funcionou em outros módulos --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="cod_transacao" label="Cód. Transação" value="{{ request('cod_transacao') }}"
                placeholder="Digite um ou mais códigos separados por vírgula" />
            <p class="mt-1 text-xs text-gray-500">
                Você pode buscar múltiplos códigos separando-os por vírgula (ex: 1001, 1002, 1003)
            </p>
        </div>

        <div>
            <x-forms.input name="chave_nf" label="Chave NF" value="{{ request('chave_nf') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nf" label="Número NF" value="{{ request('numero_nf') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_vencimento_nf" label="Data Vencimento NF"
                value="{{ request('data_vencimento_nf') }}" />
        </div>

        <div>
            <x-forms.input name="valor_nf" label="Valor NF" value="{{ request('valor_nf') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.smart-select name="posto_abastecimento" label="Posto Abastecimento"
                placeholder="Selecione o posto..." :options="$postosFrequentes ?? []" :searchUrl="route('admin.api.fornecedores.search')" :selected="request('posto_abastecimento')"
                asyncSearch="true" minSearchLength="2" />
        </div>

        <div>
            <x-forms.smart-select name="placa" label="Placa" placeholder="Selecione a placa..." :options="$placasFrequentes ?? []"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('placa')" asyncSearch="true" minSearchLength="2" />
        </div>

        <div>
            <x-forms.smart-select name="tipo_combustivel" label="Tipo Combustível" placeholder="Selecione o tipo..."
                :options="$tiposCombustivel ?? []" :selected="request('tipo_combustivel')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.input name="cnpj" label="CNPJ" value="{{ request('cnpj') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input type="date" name="data_abastecimento" label="Data Abastecimento"
                value="{{ request('data_abastecimento') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.abastecimentosfaturamento.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>

<!-- Modal para Busca Rápida por Códigos -->
{{-- <div x-data="buscaRapidaPorCodigos()" x-cloak>
    <!-- Botão para abrir o modal -->
    <button type="button" @click="abrirModal"
        class="fixed bottom-4 right-4 z-50 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Busca por Códigos
    </button>

    <!-- Modal -->
    <div x-show="modalAberto" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay de fundo -->
            <div x-show="modalAberto" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="fecharModal"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true">
            </div>

            <!-- Centralizador do modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Conteúdo do Modal -->
            <div x-show="modalAberto" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button @click="fecharModal" type="button"
                        class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Fechar</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Busca Rápida por Códigos de Transação
                        </h3>
                        <div class="mt-4">
                            <label for="codigos_transacao" class="block text-sm font-medium text-gray-700">
                                Códigos de Transação
                            </label>
                            <textarea id="codigos_transacao" x-model="codigosInput" rows="4"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Digite um código por linha ou separados por vírgula"></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Você pode colar vários códigos de transação separados por vírgula, espaço ou nova linha
                            </p>
                        </div>

                        <!-- Resultados da busca -->
                        <div class="mt-4" x-show="resultados.length > 0 || resultadosNaoEncontrados.length > 0">
                            <div class="border rounded-md p-3 bg-gray-50">
                                <h4 class="font-semibold text-sm text-gray-700 mb-2">Resultados da Busca</h4>

                                <!-- Códigos encontrados -->
                                <div x-show="resultados.length > 0" class="mb-3">
                                    <p class="text-sm text-green-600 font-medium">
                                        <span x-text="resultados.length"></span> código(s) encontrado(s):
                                    </p>
                                    <ul class="mt-1 pl-5 text-sm text-gray-600 list-disc">
                                        <template x-for="(item, index) in resultados" :key="index">
                                            <li class="truncate" x-text="item"></li>
                                        </template>
                                    </ul>
                                </div>

                                <!-- Códigos não encontrados -->
                                <div x-show="resultadosNaoEncontrados.length > 0">
                                    <p class="text-sm text-red-600 font-medium">
                                        <span x-text="resultadosNaoEncontrados.length"></span> código(s) não
                                        encontrado(s):
                                    </p>
                                    <ul class="mt-1 pl-5 text-sm text-gray-600 list-disc">
                                        <template x-for="(item, index) in resultadosNaoEncontrados" :key="index">
                                            <li class="truncate" x-text="item"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem de erro -->
                        <div class="mt-3" x-show="erro" x-transition>
                            <div class="rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800" x-text="erro"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="buscarCodigos" :disabled="processando || codigosInput.trim() === ''"
                        :class="{ 'opacity-50 cursor-not-allowed': processando || codigosInput.trim() === '' }"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg x-show="processando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Buscar
                    </button>
                    <button type="button" @click="aplicarFiltro" :disabled="processando || resultados.length === 0"
                        :class="{ 'opacity-50 cursor-not-allowed': processando || resultados.length === 0 }"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Aplicar na Busca
                    </button>
                    <button type="button" @click="fecharModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<script>
    function buscaRapidaPorCodigos() {
        return {
            modalAberto: false,
            codigosInput: '',
            resultados: [],
            resultadosNaoEncontrados: [],
            processando: false,
            erro: '',

            abrirModal() {
                this.modalAberto = true;
                this.codigosInput = '';
                this.resultados = [];
                this.resultadosNaoEncontrados = [];
                this.erro = '';

                // Focar no campo de texto quando o modal abrir
                setTimeout(() => {
                    document.getElementById('codigos_transacao')?.focus();
                }, 100);
            },

            fecharModal() {
                this.modalAberto = false;
            },

            buscarCodigos() {
                // Verificar se há códigos para buscar
                if (this.codigosInput.trim() === '') {
                    this.erro = 'Por favor, digite pelo menos um código de transação para buscar.';
                    return;
                }

                this.processando = true;
                this.erro = '';
                this.resultados = [];
                this.resultadosNaoEncontrados = [];

                // Processar a entrada para extrair códigos
                const codigos = this.processarEntradaCodigos();

                // Verificar se há códigos após o processamento
                if (codigos.length === 0) {
                    this.erro = 'Nenhum código válido foi encontrado na entrada.';
                    this.processando = false;
                    return;
                }

                // Fazer a requisição ao servidor
                fetch('{{ route('admin.api.abastecimentosfaturamento.buscar-por-codigos') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            codigos: codigos
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.resultados = data.codigos_encontrados || [];
                            this.resultadosNaoEncontrados = data.codigos_nao_encontrados || [];

                            if (this.resultados.length === 0) {
                                this.erro = 'Nenhum abastecimento encontrado para os códigos fornecidos.';
                            }
                        } else {
                            this.erro = data.message || 'Erro ao buscar abastecimentos.';
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        this.erro = 'Erro de comunicação com o servidor. Tente novamente.';
                    })
                    .finally(() => {
                        this.processando = false;
                    });
            },

            processarEntradaCodigos() {
                // Processar a entrada para extrair códigos (separados por vírgula, espaço ou nova linha)
                const entrada = this.codigosInput.trim();
                let codigos = [];

                // Primeiro dividir por novas linhas
                const linhas = entrada.split(/\n/);

                // Para cada linha, dividir por vírgulas ou espaços
                linhas.forEach(linha => {
                    const codigosLinha = linha.split(/[,\s]+/).filter(codigo => codigo.trim() !== '');
                    codigos = [...codigos, ...codigosLinha];
                });

                // Remover duplicatas
                codigos = [...new Set(codigos)];

                return codigos;
            },

            aplicarFiltro() {
                if (this.resultados.length > 0) {
                    // Atualizar o campo de busca no formulário principal
                    const codigoTransacaoInput = document.querySelector('input[name="cod_transacao"]');
                    if (codigoTransacaoInput) {
                        codigoTransacaoInput.value = this.resultados.join(', ');

                        // Submeter o formulário
                        codigoTransacaoInput.closest('form').submit();
                    }

                    this.fecharModal();
                }
            }
        };
    }
</script>

{{-- Scripts para processamento da chave NF --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referências aos elementos
        const chaveNfInput = document.getElementById('chave_nf_scanner');
        const messageElement = document.getElementById('chave-nf-message');
        const limparSelecoesBtn = document.getElementById('btn-limpar-selecoes');

        // Configuração do token CSRF para requisições AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Função para mostrar mensagens
        function showMessage(message, type = 'info') {
            messageElement.textContent = message;
            messageElement.className = 'mt-2 text-sm';

            switch (type) {
                case 'success':
                    messageElement.classList.add('text-green-600');
                    break;
                case 'error':
                    messageElement.classList.add('text-red-600');
                    break;
                default:
                    messageElement.classList.add('text-blue-600');
            }

            // Limpar a mensagem após alguns segundos
            setTimeout(() => {
                messageElement.textContent = '';
                messageElement.className = 'mt-2 text-sm';
            }, 5000);
        }

        // Função para processar a chave NF
        async function processarChaveNf(chaveNf) {
            if (!chaveNf || chaveNf.trim() === '') {
                showMessage('Por favor, escaneie ou digite uma chave NF válida.', 'error');
                return;
            }

            try {
                // Mostrar estado de carregamento
                chaveNfInput.disabled = true;
                showMessage('Processando...', 'info');

                // Fazer a requisição para o backend
                const response = await fetch(
                    '{{ route('admin.abastecimentosfaturamento.processar-chave-nf') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            chave_nf: chaveNf
                        })
                    });

                const data = await response.json();

                // Processar resposta
                if (data.success) {
                    showMessage(`Abastecimento encontrado! Código: ${data.cod_transacao}`, 'success');

                    // Atualizar o contador no indicador de seleções, se estiver usando Alpine.js
                    if (window.Alpine) {
                        const alpineElement = document.querySelector('[x-data]');
                        if (alpineElement && alpineElement.__x) {
                            alpineElement.__x.updateElement();
                        }
                    }

                    // Triggar o evento de atualização do armazenamento
                    window.dispatchEvent(new CustomEvent('storage-update'));

                    // Opcional: Recarregar a tabela de resultados
                    if (window.htmx) {
                        htmx.trigger('#results-table', 'htmx:refresh');
                    }
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                console.error('Erro ao processar chave NF:', error);
                showMessage('Erro de comunicação com o servidor. Tente novamente.', 'error');
            } finally {
                // Restaurar estado do input
                chaveNfInput.disabled = false;
                chaveNfInput.value = '';
                chaveNfInput.focus();
            }
        }

        // Event listener para o input de chave NF
        chaveNfInput.addEventListener('keypress', function(e) {
            // Processa quando pressionar Enter
            if (e.key === 'Enter') {
                e.preventDefault();
                processarChaveNf(this.value);
            }
        });

        // Event listener para o botão de limpar seleções
        limparSelecoesBtn.addEventListener('click', async function() {
            try {
                const response = await fetch(
                    '{{ route('admin.abastecimentosfaturamento.limpar-selecoes') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                const data = await response.json();

                if (data.success) {
                    showMessage('Seleções limpas com sucesso.', 'success');

                    // Limpar seleções do localStorage
                    localStorage.removeItem('selectedRows');

                    // Disparar evento para atualizar a UI
                    window.dispatchEvent(new CustomEvent('storage-update'));

                    // Opcional: Recarregar a tabela de resultados
                    if (window.htmx) {
                        htmx.trigger('#results-table', 'htmx:refresh');
                    }
                } else {
                    showMessage('Erro ao limpar seleções.', 'error');
                }
            } catch (error) {
                console.error('Erro ao limpar seleções:', error);
                showMessage('Erro de comunicação com o servidor. Tente novamente.', 'error');
            }
        });

        // Focando o input automaticamente quando a página carrega
        setTimeout(() => {
            chaveNfInput.focus();
        }, 500);
    });
</script>

{{-- Script de suporte para indicador de seleção --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar estado inicial e configurar indicador de seleção
        function updateSelectionIndicator() {
            try {
                const selectedRows = JSON.parse(localStorage.getItem('selectedRows') || '[]');
                const indicatorElement = document.getElementById('selecoes-ativas');

                if (indicatorElement) {
                    if (selectedRows.length > 0) {
                        indicatorElement.style.display = 'block';
                    } else {
                        indicatorElement.style.display = 'none';
                    }
                }
            } catch (e) {
                console.error('Erro ao atualizar indicador:', e);
            }
        }

        // Executar imediatamente e periodicamente
        updateSelectionIndicator();
        setInterval(updateSelectionIndicator, 500);

        // Escutar por alterações no armazenamento
        window.addEventListener('storage', updateSelectionIndicator);
        window.addEventListener('storage-update', updateSelectionIndicator);
        window.addEventListener('storage-change', updateSelectionIndicator);
    });
</script>
