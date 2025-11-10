<x-app-layout>

    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="space-y-6">
                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 p-4 rounded">
                                <ul class="list-disc list-inside text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($necessarioAbrirOS)
                            <div class="mb-6 bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Atenção: Ordem de Serviço Necessária
                                        </h3>
                                        <p class="mt-1 text-sm text-yellow-700">
                                            Para realizar a movimentação de pneus, é necessário abrir uma ordem de
                                            serviço do tipo
                                            <strong>Borracharia</strong> com status <strong>Em Execução</strong>.
                                            Não há ordens de serviço disponíveis que atendam aos critérios
                                            necessários no momento.
                                        </p>
                                        <div class="mt-3">
                                            <a href="{{ route('admin.ordemservicos.create') }}"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-200 hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Abrir Nova Ordem de Serviço
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <h3 class="font-medium text-gray-800 mb-10 uppercase">Movimentação do Pneu</h3>
                        <div
                            class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center {{ $necessarioAbrirOS ? 'opacity-50 pointer-events-none' : '' }}">
                            <x-forms.smart-select name="id_ordem_servico"
                                placeholder="{{ $necessarioAbrirOS ? 'Nenhuma ordem de serviço disponível...' : 'Selecione a ordem de serviço...' }}"
                                searchable="true" label="Ordem de Serviço"
                                searchUrl="{{ route('admin.api.ordemservico.search') }}" :options="$ordensServico"
                                :disabled="$necessarioAbrirOS" />
                            <input type="hidden" label="Cód. Veículo" name="select_id" type="text" />
                            <x-forms.input label="Placa" name="placa" type="text" readonly />
                            <x-forms.input label="Tipo Equipamento" name="id_tipo_equipamento" readonly />
                            <x-forms.input label="Categoria" name="id_categoria" type="text" readonly />
                            <x-forms.input label="Modelo" name="id_modelo_veiculo" type="text" readonly />
                            <x-forms.input label="Chassi" name="chassi" type="text" readonly />
                            <x-forms.input label="Km Atual" name="km_atual" type="text" readonly />
                            <x-forms.smart-select name="id_pneu"
                                placeholder="{{ $necessarioAbrirOS ? 'Abra uma ordem de serviço primeiro...' : 'Selecione uma ordem de serviço primeiro...' }}"
                                label="Número de Fogo" :options="[]" :searchUrl="route('admin.api.pneu.search-by-os')" x-ref="selectPneu"
                                ::disabled="pneuSelectDisabled || {{ $necessarioAbrirOS ? 'true' : 'false' }}" />
                        </div>

                        <div>
                            <!-- Modal de remoção -->
                            <div id="modal"
                                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 10001; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                <h2>Informações do Pneu</h2>
                                <label for="kmRemovido">Km Removido:</label>
                                <x-bladewind::input type="number" id="kmRemovido" required />
                                <br>
                                <label for="sulcoRemovido">Sulco do Pneu:</label>
                                <x-bladewind::input type="number" id="sulcoRemovido" step="0.1" required />
                                <br>
                                <label for="destinacaoSolicitada">Destinação:</label>
                                <select id="destinacaoSolicitada" name="destinacaoSolicitada"
                                    class="w-full outline-none border-gray-300 rounded-md" required>
                                    <option value="">Selecione o Destino...</option>
                                    <option value="ENVIAR AO ESTOQUE">ENVIAR AO ESTOQUE</option>
                                    <option value="ENVIAR PARA MANUTENÇÃO">ENVIAR PARA MANUTENÇÃO</option>
                                </select>
                                <br>
                                <div class="items-end justify-center mt-4" style="paddingTop: 10px">
                                    <x-bladewind::button type="secondary" id="cancelar">Cancelar</x-bladewind::button>
                                    <x-bladewind::button id="confirmar">Confirmar</x-bladewind::button>
                                </div>
                            </div>

                            <!-- Modal de adição -->
                            <div id="modal-adicionar"
                                style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; z-index: 10001; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                <h2>Informações do Pneu Adicionado</h2>
                                <label for="kmAdicionado">Km Adicionado:</label>
                                <x-bladewind::input type="number" id="kmAdicionado" required />
                                <br>
                                <label for="sulcoAdicionado">Sulco do Pneu:</label>
                                <x-bladewind::input type="number" id="sulcoAdicionado" step="0.1" required />
                                <br>
                                <div class="items-end justify-center" style="paddingTop: 10px">
                                    <x-bladewind::button type="secondary"
                                        id="cancelarAdicionar">Cancelar</x-bladewind::button>
                                    <x-bladewind::button id="confirmarAdicionar">Confirmar</x-bladewind::button>
                                </div>
                            </div>

                            <!-- Overlay para modais -->
                            <div id="modal-overlay"
                                style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 10000;">
                            </div>

                            <div id="mostarDiv"
                                class="flex flex-col items-center justify-center min-h-screen p-4 {{ $necessarioAbrirOS ? 'opacity-30 pointer-events-none' : '' }}"
                                style="display: none;">
                                @if ($necessarioAbrirOS)
                                    <div
                                        class="absolute inset-0 bg-gray-200 bg-opacity-75 flex items-center justify-center z-50 rounded-lg">
                                        <div class="text-center p-6">
                                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-600 mb-2">Área Bloqueada</h3>
                                            <p class="text-gray-500">Abra uma ordem de serviço para usar esta
                                                funcionalidade</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Container Principal -->
                                <div class="flex flex-row items-start justify-center w-full max-w-4xl">
                                    <!-- Área para pneus avulsos -->
                                    <div id="pneusAvulsos" class="mr-8">
                                        <h3 class="font-medium text-gray-800 mb-4">Pneus para aplicação</h3>
                                        <div class="flex flex-col space-y-4" id="areaPneusAvulsos"></div>
                                    </div>

                                    <!-- SVG Responsivo -->
                                    <div class="flex-shrink-0 text-center">
                                        <svg id="caminhao"
                                            class="w-full h-auto max-w-md mx-auto min-w-[700px] min-h-[400px] max-h-[1200px]"
                                            viewBox="0 0 500 600"></svg>
                                    </div>

                                    <!-- Container dos Dropzones -->
                                    <div class="flex flex-col items-center space-y-4">
                                        <!-- Borracharia -->
                                        <div class="dropzone bg-orange-300 p-4 w-32 flex flex-col items-center justify-center hover:bg-orange-400 transition-colors cursor-pointer"
                                            data-tipo="BORRACHARIA">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                                            </svg>
                                            <div class="mt-2">Borracharia</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Legenda -->
                                <div id="legenda"
                                    style="position: fixed; bottom: 20px; left: 270px; background: white; padding: 15px; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                                    <h3>Legenda</h3>
                                    <ul style="list-style-type: none; padding: 0; margin: 0;">
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: black; margin-right: 10px;"></span>Sulco
                                            maior que 24</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: green; margin-right: 10px;"></span>Sulco
                                            entre 21 e 24</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: blue; margin-right: 10px;"></span>Sulco
                                            entre 16 e 20</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: yellow; margin-right: 10px;"></span>Sulco
                                            entre 11 e 15</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: red; margin-right: 10px;"></span>Sulco
                                            menor que 10</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: orange; margin-right: 10px;"></span>Pneu
                                            selecionado</li>
                                        <li><span
                                                style="display: inline-block; width: 20px; height: 20px; background-color: gray; margin-right: 10px;"></span>Sem
                                            pneu/sem informação sulco</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <!-- Botões posicionados no canto inferior direito -->
                        @if (!$necessarioAbrirOS)
                            <div class="fixed bottom-5 right-5 flex space-x-3 z-50">
                                <a href="{{ route('admin.movimentacaopneus.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    Cancelar
                                </a>

                                {{-- Formulário Finalizar Aplicação --}}
                                <form action="{{ route('admin.movimentacaopneus.finalizar-aplicacao') }}"
                                    method="POST" class="inline-block" id="formFinalizarAplicacao">
                                    @csrf
                                    <input type="hidden" name="id_ordem_servico" id="hiddenOrdemServico"
                                        value="">
                                    <button type="button" onclick="finalizarComValidacao()"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                        title="Finalizar a aplicação de pneus e concluir a ordem de serviço"
                                        id="btnFinalizarAplicacao">
                                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Finalizar Aplicação
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            // Verificar se é necessário abrir OS e desabilitar funcionalidades JavaScript
            const necessarioAbrirOS = @json($necessarioAbrirOS);

            if (necessarioAbrirOS) {
                // Desabilitar auto-save se estiver presente
                if (typeof autoSaveConfig !== 'undefined') {
                    autoSaveConfig.enabled = false;
                }

                // Interceptar tentativas de interação
                document.addEventListener('DOMContentLoaded', function() {
                    const mostarDiv = document.getElementById('mostarDiv');
                    if (mostarDiv) {
                        // Desabilitar todas as interações de drag and drop e cliques
                        mostarDiv.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);

                        mostarDiv.addEventListener('dragstart', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);

                        mostarDiv.addEventListener('drop', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);

                        mostarDiv.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }, true);
                    }
                });
            }
        </script>

        <script>
            // Variável global para armazenar a ordem de serviço selecionada
            window.ordemServicoAtual = null;

            // Script para capturar ID da ordem de serviço selecionada
            document.addEventListener('DOMContentLoaded', function() {
                const formFinalizar = document.getElementById('formFinalizarAplicacao');
                const hiddenOrdemServico = document.getElementById('hiddenOrdemServico');

                console.log('🔍 Elementos encontrados:', {
                    formFinalizar: !!formFinalizar,
                    hiddenOrdemServico: !!hiddenOrdemServico
                });

                // Interceptar quando uma ordem de serviço for selecionada
                // Isso vai capturar quando o smart-select for usado
                document.addEventListener('change', function(e) {
                    if (e.target && (e.target.name === 'id_ordem_servico' || e.target.getAttribute('name') ===
                            'id_ordem_servico')) {
                        window.ordemServicoAtual = e.target.value;
                        hiddenOrdemServico.value = e.target.value;
                        console.log('🎯 Ordem de serviço capturada via change:', e.target.value);
                    }
                });

                // Interceptar clicks em opções do smart-select
                document.addEventListener('click', function(e) {
                    if (e.target && e.target.getAttribute('data-value')) {
                        const dataValue = e.target.getAttribute('data-value');
                        if (/^\d+$/.test(dataValue)) {
                            window.ordemServicoAtual = dataValue;
                            hiddenOrdemServico.value = dataValue;
                            console.log('🎯 Ordem de serviço capturada via click:', dataValue);
                        }
                    }
                });

                // Função para buscar o valor da ordem de serviço por diferentes métodos
                function obterIdOrdemServico() {
                    // Primeiro, tentar usar a variável global
                    if (window.ordemServicoAtual) {
                        console.log('🔍 Método 1 - Variável global:', window.ordemServicoAtual);
                        return window.ordemServicoAtual;
                    }

                    let idOrdemServico = null;

                    // Método 2: Select tradicional
                    const selectTradicional = document.querySelector('select[name="id_ordem_servico"]');
                    if (selectTradicional && selectTradicional.value) {
                        idOrdemServico = selectTradicional.value;
                        console.log('🔍 Método 2 - Select tradicional:', idOrdemServico);
                    }

                    // Método 3: Input hidden do smart-select
                    const hiddenSmartSelect = document.querySelector(
                        'input[name="id_ordem_servico"]:not(#hiddenOrdemServico)');
                    if (!idOrdemServico && hiddenSmartSelect && hiddenSmartSelect.value) {
                        idOrdemServico = hiddenSmartSelect.value;
                        console.log('🔍 Método 3 - Hidden smart-select:', idOrdemServico);
                    }

                    return idOrdemServico;
                }

                // Validar antes do envio do formulário
                if (formFinalizar) {
                    formFinalizar.addEventListener('submit', function(e) {
                        const idOrdemServico = obterIdOrdemServico();

                        console.log('📤 Tentando finalizar com OS:', idOrdemServico);

                        if (!idOrdemServico) {
                            e.preventDefault();
                            alert('Por favor, selecione uma ordem de serviço antes de finalizar.');
                            return false;
                        }

                        // Atualizar campo hidden
                        hiddenOrdemServico.value = idOrdemServico;
                        console.log('✅ Campo hidden atualizado com:', idOrdemServico);
                    });
                }

                // Executar verificação inicial após um tempo para aguardar carregamento
                setTimeout(() => {
                    const idOrdemServico = obterIdOrdemServico();
                    if (idOrdemServico) {
                        hiddenOrdemServico.value = idOrdemServico;
                        window.ordemServicoAtual = idOrdemServico;
                        console.log('🔄 Inicialização: OS detectada automaticamente:', idOrdemServico);
                    }
                }, 2000);
            });

            // Função global para finalizar com validação forçada
            window.finalizarComValidacao = async function() {
                const hiddenOrdemServico = document.getElementById('hiddenOrdemServico');
                const formFinalizar = document.getElementById('formFinalizarAplicacao');
                const btnFinalizar = document.getElementById('btnFinalizarAplicacao');

                // Desabilitar botão para evitar cliques múltiplos
                if (btnFinalizar) {
                    btnFinalizar.disabled = true;
                    btnFinalizar.innerHTML =
                        '<svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Validando...';
                }

                try {
                    // 🔍 OBTER ID DO VEÍCULO
                    const inputVeiculo = document.querySelector('input[name="select_id"]');
                    const idVeiculo = inputVeiculo ? inputVeiculo.value : null;

                    if (!idVeiculo || idVeiculo.trim() === '') {
                        // Reabilitar botão
                        if (btnFinalizar) {
                            btnFinalizar.disabled = false;
                            btnFinalizar.innerHTML =
                                '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                        }
                        alert('❌ Por favor, selecione um veículo primeiro.');
                        return;
                    }

                    // 🔍 VALIDAR LOCALIZAÇÕES OBRIGATÓRIAS
                    console.log(`🔍 Validando localizações obrigatórias do veículo ${idVeiculo}...`);

                    if (typeof window.validarLocalizacoesObrigatorias === 'function') {
                        const validacao = await window.validarLocalizacoesObrigatorias(idVeiculo);

                        if (!validacao.valido) {
                            // Reabilitar botão
                            if (btnFinalizar) {
                                btnFinalizar.disabled = false;
                                btnFinalizar.innerHTML =
                                    '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                            }
                            alert(`❌ Não é possível finalizar a aplicação:\n\n${validacao.mensagem}`);
                            return;
                        }

                        console.log(
                            '✅ Validação de localizações passou - todas as posições obrigatórias estão preenchidas');
                    } else {
                        console.warn(
                            '⚠️ Função de validação de localizações não encontrada, continuando sem validação');
                    }

                    // 🎯 CONTINUAR COM VALIDAÇÃO DE ORDEM DE SERVIÇO
                    if (btnFinalizar) {
                        btnFinalizar.innerHTML =
                            '<svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Finalizando...';
                    }

                    // Usar a ordem de serviço já capturada
                    let idOrdemServico = window.ordemServicoAtual;

                    if (!idOrdemServico) {
                        // Tentar buscar automaticamente
                        const selectOrdemServico = document.querySelector('select[name="id_ordem_servico"]');
                        if (selectOrdemServico && selectOrdemServico.value) {
                            idOrdemServico = selectOrdemServico.value;
                        }
                    }

                    if (!idOrdemServico) {
                        // Verificar se há hidden input do smart-select
                        const hiddenSmartSelect = document.querySelector(
                            'input[name="id_ordem_servico"]:not(#hiddenOrdemServico)');
                        if (hiddenSmartSelect && hiddenSmartSelect.value) {
                            idOrdemServico = hiddenSmartSelect.value;
                        }
                    }

                    if (!idOrdemServico) {
                        // Se não encontrou, verificar se veículo foi carregado
                        const inputVeiculo = document.querySelector('input[name="select_id"]');
                        if (inputVeiculo && inputVeiculo.value && inputVeiculo.value.trim() !== '') {
                            console.log('⚠️ Veículo carregado mas OS não identificada - usando fallback do backend');
                            // Continuar sem OS - o backend vai usar fallback
                        } else {
                            // Reabilitar botão e mostrar erro
                            if (btnFinalizar) {
                                btnFinalizar.disabled = false;
                                btnFinalizar.innerHTML =
                                    '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                            }
                            alert('Por favor, selecione uma ordem de serviço primeiro.');
                            return;
                        }
                    }

                    // Preparar dados para envio
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'));
                    if (idOrdemServico) {
                        formData.append('id_ordem_servico', idOrdemServico);
                    }

                    console.log('✅ Enviando finalização via AJAX com OS:', idOrdemServico);

                    // Enviar via AJAX
                    fetch(formFinalizar.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async response => {
                            // Parse do JSON primeiro
                            const data = await response.json();

                            // Se não é sucesso, tratar como erro
                            if (!response.ok) {
                                console.log('🔍 Dados de erro do backend (finalizar):', data);
                                const errorMessage = data.message || data.error || 'Erro desconhecido';
                                throw new Error(errorMessage);
                            }

                            return data;
                        })
                        .then(data => {
                            console.log('📥 Resposta do backend:', data);

                            // Reabilitar botão
                            if (btnFinalizar) {
                                btnFinalizar.disabled = false;
                                btnFinalizar.innerHTML =
                                    '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                            }

                            if (data.success) {
                                // Sucesso
                                alert('✅ ' + (data.message || 'Aplicação de pneu finalizada com sucesso!'));

                                // Opcional: recarregar a página ou redirecionar
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                // Erro retornado pelo backend - sempre mostrar mensagem completa
                                const errorMessage = data.message || data.error || 'Erro desconhecido';
                                alert(errorMessage);
                            }
                        })
                        .catch(error => {
                            console.error('❌ Erro na requisição:', error);

                            // Reabilitar botão
                            if (btnFinalizar) {
                                btnFinalizar.disabled = false;
                                btnFinalizar.innerHTML =
                                    '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                            }

                            const errorMessage = error.message;

                            // ✅ Mostrar alerta visual para o usuário
                            alert(errorMessage);
                        });

                } catch (validationError) {
                    console.error('❌ Erro durante validação:', validationError);

                    // Reabilitar botão em caso de erro
                    if (btnFinalizar) {
                        btnFinalizar.disabled = false;
                        btnFinalizar.innerHTML =
                            '<svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Finalizar Aplicação';
                    }

                    alert('❌ Erro durante validação: ' + validationError.message);
                }
            };
        </script>

        <script src="{{ asset('js/pneus/movimentacaopneus/movimentacaopneu.js') }}"></script>
        <script src="{{ asset('js/pneus/movimentacaopneus/auto_save.js') }}"></script>
        {{-- Temporariamente desabilitado devido à mudança para ordem de serviço --}}
        {{-- <script src="{{ asset('js/pneus/movimentacaopneus/session_rest.js') }}"></script> --}}
    @endpush
</x-app-layout>
