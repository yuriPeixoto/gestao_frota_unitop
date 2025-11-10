// ========================================
// 🎯 CONFIGURAÇÕES DE POSICIONAMENTO DOS PNEUS
// ========================================
// Altere estes valores para ajustar as posições dos pneus na tela
const POSICOES_PNEUS = {
    // Eixos com 2 pneus (1D, 1E, 2D, 2E, etc.)
    DOIS_PNEUS: {
        ESQUERDO: 200,    // Posição X do pneu esquerdo (1E, 2E, etc.)
        DIREITO: 360      // Posição X do pneu direito (1D, 2D, etc.)
    },

    // Eixos com 4 pneus (2DE, 2DI, 2EE, 2EI, etc.)
    QUATRO_PNEUS: {
        ESQUERDO_EXTERNO: 160,   // Posição X do pneu esquerdo externo (2DE, 3DE, etc.)
        ESQUERDO_INTERNO: 220,   // Posição X do pneu esquerdo interno (2DI, 3DI, etc.) 
        DIREITO_INTERNO: 350,    // Posição X do pneu direito interno (2EE, 3EE, etc.)
        DIREITO_EXTERNO: 410     // Posição X do pneu direito externo (2EI, 3EI, etc.)
    },

    // Estepes (E1, E2, etc.)
    ESTEPES: {
        E1: { X: 480, Y: 310 },  // Posição do estepe 1
        E2: { X: 540, Y: 310 }   // Posição do estepe 2 
    }
};

const config = {
    eixoHeight: 180,
    pneuWidth: 40,
    pneuHeight: 80,
    spacing: 30,
    startY: 80
};

// ================================
// Logger condicional (Option C)
// - Define window.__LOG_DEBUG__ automaticamente para true em localhost/file:,
//   mas permite sobrescrever em runtime (ex.: window.__LOG_DEBUG__ = true).
// - Quando false, suprime console.debug e console.groupCollapsed para
//   evitar poluição de logs em produção.
// ================================
(function () {
    try {
        if (typeof window.__LOG_DEBUG__ === 'undefined') {
            // Habilitar debug por padrão em dev (localhost ou file:)
            var host = (typeof location !== 'undefined' && location.hostname) ? location.hostname : '';
            window.__LOG_DEBUG__ = /(^localhost$|^127\.0\.0\.1$)/.test(host) || (typeof location !== 'undefined' && location.protocol === 'file:');
        }

        if (!window.__LOG_DEBUG__ && typeof console !== 'undefined') {
            // armazenar referências originais (caso precise restaurar)
            try { console._orig_debug = console.debug; } catch (e) { }
            try { console._orig_groupCollapsed = console.groupCollapsed; } catch (e) { }

            // noop para suprimir mensagens
            var noop = function () { };

            try { console.debug = noop; } catch (e) { }
            try { console.groupCollapsed = noop; } catch (e) { }
        }
    } catch (e) {
        // não propagar erros do wrapper de logging
    }
})();

let formattedData = null;
let selectedPneu = null;
let dadosArray = null;
let trocaEmAndamento = false;
let currentDropZone = null;
let selectedPneu1 = null;
let selectedPneu2 = null;
let pneuSelecionadoParaTroca = null;
let veiculoPossuiTracao = false; // Informação se o veículo possui tração

// ========================================
// �️ MAPEAMENTO DINÂMICO DE LOCALIZAÇÕES PARA POSIÇÕES
// ========================================
let localizacaoParaPosicao = {};

// ========================================
// �🛡️ CONTROLE DE ESTADO DE APLICAÇÃO DE PNEUS
// ========================================

// Set para controlar pneus que estão sendo processados (evita duplicatas)
let pneusEmProcessamento = new Set();

// Map para armazenar timestamps das últimas operações (evita spam)
let ultimasOperacoesPneus = new Map();

// Tempo mínimo entre operações no mesmo pneu (em ms)
const TEMPO_MINIMO_ENTRE_OPERACOES = 2000; // 2 segundos

/**
 * Verifica se um pneu pode ser processado (não está em uso ou foi processado recentemente)
 * @param {string|number} pneuId - ID do pneu
 * @returns {boolean} - true se pode processar, false caso contrário
 */
function pneuPodeSerProcessado(pneuId) {
    const pneuIdStr = String(pneuId);

    // Verificar se está sendo processado
    if (pneusEmProcessamento.has(pneuIdStr)) {
        console.warn(`⚠️ Pneu ${pneuId} já está sendo processado - operação bloqueada`);
        return false;
    }

    // Verificar tempo mínimo entre operações
    const ultimaOperacao = ultimasOperacoesPneus.get(pneuIdStr);
    if (ultimaOperacao) {
        const tempoDecorrido = Date.now() - ultimaOperacao;
        if (tempoDecorrido < TEMPO_MINIMO_ENTRE_OPERACOES) {
            console.warn(`⚠️ Pneu ${pneuId} processado há ${tempoDecorrido}ms - aguardando intervalo mínimo`);
            return false;
        }
    }

    return true;
}

/**
 * Marca um pneu como sendo processado
 * @param {string|number} pneuId - ID do pneu
 */
function marcarPneuComoProcessando(pneuId) {
    const pneuIdStr = String(pneuId);
    pneusEmProcessamento.add(pneuIdStr);
    ultimasOperacoesPneus.set(pneuIdStr, Date.now());
}

/**
 * Marca um pneu como finalizado (não mais em processamento)
 * @param {string|number} pneuId - ID do pneu
 */
function liberarPneuProcessamento(pneuId) {
    const pneuIdStr = String(pneuId);
    pneusEmProcessamento.delete(pneuIdStr);
}

/**
 * Limpa todos os pneus do processamento (usar em caso de reset)
 */
function limparTodosProcessamentos() {
    pneusEmProcessamento.clear();
    ultimasOperacoesPneus.clear();
}

// ========================================
// 🔍 FUNÇÕES DE VERIFICAÇÃO DE PNEUS PENDENTES
// ========================================

/**
 * Verifica a quantidade de pneus que ainda estão pendentes de aplicação
 * @returns {Object} Status com contagens e informações sobre pneus pendentes
 */
function verificarPneusPendentes() {
    const idOrdemServico = document.querySelector('[name="id_ordem_servico"]')?.value;

    if (!idOrdemServico) {
        console.warn('⚠️ Nenhuma ordem de serviço selecionada');
        return {
            pendentes: 0,
            total: 0,
            disponiveis: 0,
            avulsos_pendentes: 0,
            em_processamento: 0,
            aplicados: 0,
            mensagem: 'Nenhuma ordem de serviço selecionada'
        };
    }

    // Verificação iniciada para ordem de serviço

    // Contar pneus disponíveis no smart-select (ainda não aplicados)
    let pneusDisponiveis = 0;
    const selectPneu = document.querySelector('[name="id_pneu"]');

    if (selectPneu) {
        // elemento select encontrado

        // Para smart-select com Alpine.js
        if (selectPneu._x_dataStack?.[0]?.options) {
            const options = selectPneu._x_dataStack[0].options;
            // Filtrar opções válidas (excluir opção padrão)
            const opcoesValidas = options.filter(option =>
                option.value && option.value !== '' && option.value !== 'null'
            );
            pneusDisponiveis = opcoesValidas.length;
        } else {
            // Para select tradicional - fallback
            const options = selectPneu.options;
            if (options) {
                const opcoesValidas = Array.from(options).filter(option =>
                    option.value && option.value !== '' && option.value !== 'null'
                );
                pneusDisponiveis = opcoesValidas.length;
            }
        }
    } else {
        console.warn('⚠️ Elemento select não encontrado!');
    }

    // Contar pneus avulsos não aplicados (que estão na área de pneus avulsos mas não foram aplicados)
    const pneusAvulsosElements = document.querySelectorAll('.pneu-avulso:not(.aplicado)');
    const pneusAvulsosPendentes = pneusAvulsosElements.length;

    // Contar pneus em processamento
    const pneusEmProcessamentoCount = pneusEmProcessamento.size;

    // Contar pneus aplicados no veículo (pneus com ID válido nas posições do veículo)
    const pneusAplicadosElements = document.querySelectorAll('.pneu[data-id]:not([data-id="null"]):not([data-id=""]):not(.espaco-vazio)');
    const pneusAplicados = pneusAplicadosElements.length;

    const totalPendentes = pneusDisponiveis + pneusAvulsosPendentes;
    // total de pendentes calculado

    // Incluir aviso se há pneus em processamento
    let mensagem = '';
    if (pneusEmProcessamentoCount > 0) {
        if (totalPendentes > 0) {
            mensagem = `${totalPendentes} pneu(s) ainda precisam ser aplicados (${pneusEmProcessamentoCount} em processamento)`;
        } else {
            mensagem = `${pneusEmProcessamentoCount} pneu(s) sendo processado(s)`;
        }
    } else {
        mensagem = totalPendentes > 0 ?
            `${totalPendentes} pneu(s) ainda precisam ser aplicados` :
            'Todos os pneus foram aplicados';
    }

    // mensagem gerada

    const resultado = {
        pendentes: totalPendentes,
        disponiveis: pneusDisponiveis,
        avulsos_pendentes: pneusAvulsosPendentes,
        em_processamento: pneusEmProcessamentoCount,
        aplicados: pneusAplicados,
        total: totalPendentes + pneusAplicados,
        mensagem: mensagem
    };
    return resultado;
}/**
 * Valida se todos os pneus foram aplicados antes de permitir o salvamento
 * @returns {Object} Status de validação com mensagem
 */
function validarTodosPneusAplicados() {
    const status = verificarPneusPendentes();


    if (status.pendentes > 0 || status.em_processamento > 0) {
        console.warn('⚠️ Encontrados pneus pendentes ou em processamento');
        const detalhes = [];
        if (status.disponiveis > 0) {
            detalhes.push(`• ${status.disponiveis} pneu(s) disponível(veis) no select`);
        }
        if (status.avulsos_pendentes > 0) {
            detalhes.push(`• ${status.avulsos_pendentes} pneu(s) avulso(s) pendente(s)`);
        }
        if (status.em_processamento > 0) {
            detalhes.push(`• ${status.em_processamento} pneu(s) sendo processado(s)`);
        }

        const mensagemCompleta = `⚠️ ATENÇÃO: ${status.mensagem}\n\n` +
            `Detalhes:\n` +
            detalhes.join('\n') + '\n' +
            `• ${status.aplicados} pneu(s) já aplicado(s)\n\n` +
            `${status.em_processamento > 0 ?
                'RECOMENDAÇÃO: Aguarde o processamento dos pneus finalizar antes de salvar.\n\n' :
                ''}Deseja continuar mesmo assim?`;

        console.warn('📝 Mensagem de validação (falhou)');

        return {
            valido: false,
            mensagem: mensagemCompleta
        };
    }

    const mensagemSucesso = `✅ Todos os pneus foram aplicados! (${status.aplicados} pneu(s) aplicado(s))`;

    return {
        valido: true,
        mensagem: mensagemSucesso
    };
}/**
 * Exibe no console o status detalhado de pneus pendentes (função auxiliar para debug)
 */
function exibirStatusPneusPendentes() {
    const status = verificarPneusPendentes();

    // Fornece um log resumido de status quando chamado
    console.debug('🔍 STATUS PNEUS PENDENTES', status);
    return status;
}

/**
 * Função para resetar estado em caso de emergência (disponível globalmente)
 */
function resetarSistemaMovimentacao() {
    console.warn('🚨 RESET DE EMERGÊNCIA DO SISTEMA DE MOVIMENTAÇÃO');

    // Limpar todos os processamentos
    limparTodosProcessamentos();

    // Limpar cache de pneus removidos
    if (typeof pneusRemovidosDasOpcoes !== 'undefined') {
        pneusRemovidosDasOpcoes.clear();
    }

    // Limpar dados do sistema
    dadosArray = null;
    formattedData = null;
    selectedPneu = null;
    pneuSelecionadoParaTroca = null;

    console.debug('✅ Sistema resetado com sucesso');

    // Exibir status após reset
    exibirStatusPneusPendentes();
}

// Expor função globalmente para debug
window.resetarSistemaMovimentacao = resetarSistemaMovimentacao;
window.exibirStatusPneusPendentes = exibirStatusPneusPendentes;
window.verificarPneusPendentes = verificarPneusPendentes; if (window.movimentacaoInitialized) {
    console.warn('⚠️ MovimentacaoPneus já inicializado, evitando duplicação');
} else {
    window.movimentacaoInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {
        // Aguardar outros scripts carregarem
        setTimeout(() => {
            inicializarSistemaMovimentacao();
        }, 300);
    });

    function inicializarSistemaMovimentacao() {
        try {
            // Verificar se elementos existem
            const elementosEssenciais = [
                '[name="select_id"]',
                '[name="id_tipo_equipamento"]',
                '[name="id_categoria"]',
                '[name="id_modelo_veiculo"]',
                '[name="chassi"]',
                '[name="km_atual"]'
            ];

            const faltando = elementosEssenciais.filter(sel => !document.querySelector(sel));

            if (faltando.length > 0) {
                console.warn('⚠️ Elementos faltando:', faltando);
                // Tentar novamente
                setTimeout(inicializarSistemaMovimentacao, 500);
                return;
            }

            // Obter referências dos elementos
            const idPlacaInput = document.querySelector('[name="select_id"]');
            const placa = document.querySelector('[name="placa"]');
            const idTipoEquipamento = document.querySelector('[name="id_tipo_equipamento"]');
            const idCategoria = document.querySelector('[name="id_categoria"]');
            const idModeloVeiculo = document.querySelector('[name="id_modelo_veiculo"]');
            const chassi = document.querySelector('[name="chassi"]');
            const kmAtual = document.querySelector('[name="km_atual"]');

            // Event listener para seleção de ordem de serviço - CORRIGIDO
            configurarListenerOrdemServico(idPlacaInput, idTipoEquipamento, idCategoria, idModeloVeiculo, chassi, kmAtual);

            // Event listener para seleção de pneu avulso
            configurarListenerPneu();

            // Event listeners para modais
            setupModalEventListeners();

            // Event listener para botão de salvar
            configurarListenerSalvar();

        } catch (error) {
            console.error('❌ Erro na inicialização:', error);
            setTimeout(inicializarSistemaMovimentacao, 1000);
        }
    }

    // ==========================================
    // 3. NOVA FUNÇÃO PARA CONFIGURAR LISTENER DE VEÍCULO
    // ==========================================

    function configurarListenerOrdemServico(idPlacaInput, idTipoEquipamento, idCategoria, idModeloVeiculo, chassi, kmAtual) {

        // Método 1: onSmartSelectChange (se disponível)
        if (typeof window.onSmartSelectChange === 'function') {

            window.onSmartSelectChange('id_ordem_servico', function (ordemServico) {
                processarSelecaoOrdemServico(ordemServico.value, idPlacaInput, idTipoEquipamento, idCategoria, idModeloVeiculo, chassi, kmAtual);
            });
        } else {
            console.warn('⚠️ onSmartSelectChange não disponível, usando listener direto');
        }

        // Método 2: Listener direto (sempre configurar como backup)
        setTimeout(() => {
            const selectElement = document.querySelector('[name="id_ordem_servico"]');
            if (selectElement) {
                selectElement.addEventListener('change', function (event) {
                    processarSelecaoOrdemServico(event.target.value, idPlacaInput, idTipoEquipamento, idCategoria, idModeloVeiculo, chassi, kmAtual);
                });
            }
        }, 500);
    }

    // ==========================================
    // 4. NOVA FUNÇÃO CENTRALIZADA PARA PROCESSAR SELEÇÃO
    // ==========================================

    async function processarSelecaoOrdemServico(ordemServicoId, idPlacaInput, idTipoEquipamento, idCategoria, idModeloVeiculo, chassi, kmAtual) {

        if (!ordemServicoId) {
            clearFormData();
            return;
        }

        // ✅ Ao trocar a ordem de serviço, resetar qualquer estado relacionado a pneus
        // para evitar que pneus selecionados para aplicação em outra OS permaneçam
        // disponíveis/selecionados no novo veículo.
        try {
            if (typeof resetEstadoPneusParaNovaOS === 'function') {
                resetEstadoPneusParaNovaOS();
            }
        } catch (e) {
            console.warn('⚠️ Falha ao resetar estado de pneus ao trocar OS:', e);
        }

        try {
            // Mostrar loading se disponível
            if (typeof showNotification === 'function') {
                showNotification('Carregando dados da ordem de serviço...', 'processing', 0);
            }

            const response = await fetch('movimentacaopneus/get-ordemservico-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ ordem_servico: ordemServicoId }),
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            // Remover loading
            document.querySelectorAll('.notification').forEach(n => {
                if (n.textContent.includes('Carregando')) {
                    n.remove();
                }
            });

            if (!data.error) {
                // Preencher campos do formulário
                idPlacaInput.value = data.id_veiculo || '';
                placa.value = data.placa || '';
                idTipoEquipamento.value = data.id_tipo_equipamento || '';
                idCategoria.value = data.id_categoria || '';
                idModeloVeiculo.value = data.id_modelo_veiculo || '';
                chassi.value = data.chassi || '';
                kmAtual.value = data.km_atual || '';

                // Armazenar informação de tração do veículo
                veiculoPossuiTracao = data.is_possui_tracao === true || data.is_possui_tracao === 'true' || data.is_possui_tracao === 1 || data.is_possui_tracao === '1';
                console.debug(`🚗 Veículo possui tração: ${veiculoPossuiTracao ? 'SIM' : 'NÃO'}`);

                const tipoEquipamentoPneus = data.tipoEquipamentoPneus;

                if (!tipoEquipamentoPneus) {
                    throw new Error('tipoEquipamentoPneus não encontrado na resposta');
                }

                if (!validateTipoEquipamentoData(tipoEquipamentoPneus)) {
                    throw new Error('Estrutura dos dados do tipo de equipamento inválida');
                }

                // Configurar dados globais
                formattedData = tipoEquipamentoPneus;

                // ✅ DEBUG: Mostrar localizações dinâmicas carregadas
                if (formattedData.localizacoesDisponiveis) {
                    console.debug('🎯 LOCALIZAÇÕES DINÂMICAS carregadas:', formattedData.localizacoesDisponiveis);
                    Object.keys(formattedData.localizacoesDisponiveis).forEach(eixoIndex => {
                        const eixo = parseInt(eixoIndex) + 1;
                        const localizacoes = formattedData.localizacoesDisponiveis[eixoIndex].map(l => l.localizacao);
                        console.debug(`   Eixo ${eixo}: ${localizacoes.join(', ')}`);
                    });

                    // ✅ DESABILITADA TEMPORARIAMENTE: Correção 3/4 para testar posições DE/DI/EE/EI
                    const precisaCorrecao = false; // TEMPORARIAMENTE DESABILITADA
                    /*
                    const precisaCorrecao = formattedData.id_tipo_equipamento === '3/4' ||
                        formattedData.id_categoria?.includes('ACCELO') ||
                        formattedData.id_categoria?.includes('M.BENZ') ||
                        (formattedData.eixos === 2 && formattedData.pneus_por_eixo && formattedData.pneus_por_eixo[1] === 4) ||
                        // ✅ FORÇAR CORREÇÃO se eixo 2 tem localizações DE/DI/EE/EI
                        (formattedData.localizacoesDisponiveis[1] &&
                            formattedData.localizacoesDisponiveis[1].some(l => ['2DE', '2DI', '2EE', '2EI'].includes(l.localizacao)));
                    */

                    if (precisaCorrecao) {
                        console.warn('⚠️ APLICANDO CORREÇÃO ESPECÍFICA para veículo 3/4 com desenho de eixos incorreto no banco');
                        console.debug('🔍 Dados do veículo:', {
                            id_tipo_equipamento: formattedData.id_tipo_equipamento,
                            id_categoria: formattedData.id_categoria,
                            eixos: formattedData.eixos,
                            pneus_por_eixo: formattedData.pneus_por_eixo,
                            eixo2_localizacoes: formattedData.localizacoesDisponiveis[1]?.map(l => l.localizacao)
                        });

                        // Corrigir segundo eixo: 2DE/2DI/2EE/2EI → 2D/2E
                        if (formattedData.localizacoesDisponiveis[1]) {
                            const eixo2Original = formattedData.localizacoesDisponiveis[1].map(l => l.localizacao);
                            console.debug(`   🔧 Eixo 2 ANTES da correção: ${eixo2Original.join(', ')}`);

                            // Substituir por localizações corretas de 2 pneus
                            formattedData.localizacoesDisponiveis[1] = [
                                { localizacao: '2D', x: 0, y: 0 },
                                { localizacao: '2E', x: 0, y: 0 }
                            ];

                            const eixo2Corrigido = formattedData.localizacoesDisponiveis[1].map(l => l.localizacao);
                            console.debug(`   ✅ Eixo 2 APÓS correção: ${eixo2Corrigido.join(', ')}`);
                        }

                        // ✅ CORRIGIR TAMBÉM OS PNEUS APLICADOS
                        if (formattedData.pneusAplicadosFormatados && Array.isArray(formattedData.pneusAplicadosFormatados)) {
                            const mapeamentoCorrecao = {
                                '2DE': '2D',
                                '2DI': '2D', // Ambos 2DE e 2DI viram 2D
                                '2EE': '2E',
                                '2EI': '2E'  // Ambos 2EE e 2EI viram 2E
                            };

                            formattedData.pneusAplicadosFormatados.forEach(pneu => {
                                if (mapeamentoCorrecao[pneu.localizacao]) {
                                    console.debug(`   🔄 Corrigindo pneu ${pneu.id_pneu}: ${pneu.localizacao} → ${mapeamentoCorrecao[pneu.localizacao]}`);
                                    pneu.localizacao = mapeamentoCorrecao[pneu.localizacao];
                                }
                            });
                        }

                        // Corrigir primeiro eixo: remover E2 se existir
                        if (formattedData.localizacoesDisponiveis[0]) {
                            const eixo1Original = formattedData.localizacoesDisponiveis[0].map(l => l.localizacao);
                            console.debug(`   🔧 Eixo 1 ANTES da correção: ${eixo1Original.join(', ')}`);

                            // Manter apenas 1D, 1E, E1 (remover E2)
                            formattedData.localizacoesDisponiveis[0] = formattedData.localizacoesDisponiveis[0].filter(l =>
                                ['1D', '1E', 'E1'].includes(l.localizacao)
                            );

                            const eixo1Corrigido = formattedData.localizacoesDisponiveis[0].map(l => l.localizacao);
                            console.debug(`   ✅ Eixo 1 APÓS correção: ${eixo1Corrigido.join(', ')}`);
                        }

                        console.debug('✅ CORREÇÃO 3/4 aplicada com sucesso!');
                    }
                } else {
                    console.warn('⚠️ Nenhuma localização dinâmica encontrada - usando fallback');
                }

                // ✅ APLICAR CORREÇÕES BASEADAS NA CATEGORIA DO VEÍCULO
                formattedData = aplicarCorrecoesCategoria(formattedData, data.id_categoria, 'carregamento_inicial');

                renderizarCaminhao(formattedData);

                // Habilitar e configurar select de pneu com os pneus da requisição
                habilitarSelectPneu(ordemServicoId, data.pneusRequisicao);

                // Mostrar sucesso
                if (typeof showNotification === 'function') {
                    showNotification('Ordem de serviço carregada com sucesso!', 'success');
                }

            } else {
                throw new Error(data.error);
            }

        } catch (error) {
            console.error('❌ Erro ao buscar dados da ordem de serviço:', error);

            if (typeof showNotification === 'function') {
                showNotification(`Erro: ${error.message}`, 'error');
            } else {
                alert(`Erro ao buscar dados da ordem de serviço: ${error.message}`);
            }
        }
    }

    // ==========================================
    // 5. OUTRAS FUNÇÕES DE CONFIGURAÇÃO
    // ==========================================

    function configurarListenerPneu() {

        if (typeof window.onSmartSelectChange === 'function') {
            window.onSmartSelectChange('id_pneu', function (pneu) {
                processarSelecaoPneu(pneu.value);
            });
        }

        // Backup listener
        setTimeout(() => {
            const selectPneu = document.querySelector('[name="id_pneu"]');
            if (selectPneu) {
                selectPneu.addEventListener('change', function (event) {
                    if (event.target.value) {
                        processarSelecaoPneu(event.target.value);
                    }
                });
            }
        }, 500);
    }

    async function processarSelecaoPneu(pneuId) {
        if (!pneuId) return;

        // 🛡️ VERIFICAR SE PNEU PODE SER PROCESSADO
        if (!pneuPodeSerProcessado(pneuId)) {
            console.warn(`🚫 Seleção bloqueada: Pneu ${pneuId} não pode ser processado no momento`);
            return;
        }

        // 🔒 MARCAR PNEU COMO PROCESSANDO
        marcarPneuComoProcessando(pneuId);

        try {
            const response = await fetch('movimentacaopneus/get-pneu-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ pneu: pneuId }),
            });

            const data = await response.json();

            if (!data.error) {
                criarPneuAvulso(data.id_pneu, data.sulco, data.tipo_pneu || null);

                // ✅ PROTEÇÃO: Remover pneu das opções apenas se não foi removido antes
                if (!pneusRemovidosDasOpcoes.has(String(pneuId))) {
                    removerPneuDasOpcoes(pneuId);
                }
            } else {
                alert(data.error);
            }
        } catch (error) {
            console.error('Erro ao buscar dados do pneu:', error);
            alert('Erro ao buscar dados do pneu');
        } finally {
            // 🔓 SEMPRE LIBERAR O PNEU DO PROCESSAMENTO
            liberarPneuProcessamento(pneuId);
        }
    }

    function configurarListenerSalvar() {
        const idDobotao = document.getElementById('idDobotao');
        if (idDobotao) {
            idDobotao.addEventListener('click', function (e) {
                e.preventDefault();
                enviarDadosParaBackend();
            });
        }
    }

    // ==========================================
    // 6. FUNÇÃO PARA HABILITAR SELECT DE PNEU
    // ==========================================

    function habilitarSelectPneu(ordemServicoId, pneusRequisicao) {
        const selectPneu = document.querySelector('[name="id_pneu"]');

        if (!selectPneu) {
            console.warn('⚠️ Select de pneu não encontrado');
            return;
        }

        // Habilitar o select
        selectPneu.disabled = false;
        selectPneu.removeAttribute('disabled');

        // Tentar remover classes que podem estar desabilitando
        selectPneu.classList.remove('disabled');

        // Se é um smart-select do Alpine.js, tentar acessar os dados do Alpine
        if (selectPneu._x_dataStack) {
            try {
                const alpineData = selectPneu._x_dataStack[0];
                if (alpineData && typeof alpineData.disabled !== 'undefined') {
                    alpineData.disabled = false;
                    console.debug('✅ Alpine disabled definido como false');
                }
            } catch (e) {
                console.warn('⚠️ Erro ao acessar dados do Alpine:', e);
            }
        }

        // Tentar controlar a variável pneuSelectDisabled do form
        const form = selectPneu.closest('form');
        if (form && form._x_dataStack) {
            try {
                const formData = form._x_dataStack[0];
                if (formData && typeof formData.pneuSelectDisabled !== 'undefined') {
                    formData.pneuSelectDisabled = false;
                    console.debug('✅ pneuSelectDisabled definido como false');
                }
            } catch (e) {
                console.warn('⚠️ Erro ao acessar dados do form Alpine:', e);
            }
        }

        // Usar as funções globais do Smart-Select para adicionar opções
        if (pneusRequisicao && pneusRequisicao.length > 0) {
            // Usar a função global updateSmartSelectOptions
            if (typeof window.updateSmartSelectOptions === 'function') {
                const success = window.updateSmartSelectOptions('id_pneu', pneusRequisicao, false);
                if (!success) {
                    console.warn('⚠️ Falha ao usar updateSmartSelectOptions');
                }
            } else {
                console.warn('⚠️ Função updateSmartSelectOptions não disponível');
            }
        } else {

            // Limpar o smart-select se não há pneus
            if (typeof window.clearSmartSelect === 'function') {
                window.clearSmartSelect('id_pneu');
                console.debug('✅ Smart-select limpo');
            }
        }

        // COMENTADO - Select2 não é usado com smart-select
        /*
        // Verificar se é um select2 e atualizar
        if (typeof $ !== 'undefined' && typeof $(selectPneu).select2 === 'function') {
            // Limpar opções existentes
            $(selectPneu).empty();

            // Adicionar opção padrão
            $(selectPneu).append(new Option('Selecione o número de fogo...', '', true, true));

            // Adicionar pneus da requisição
            if (pneusRequisicao && pneusRequisicao.length > 0) {
                pneusRequisicao.forEach(pneu => {
                    $(selectPneu).append(new Option(pneu.label, pneu.value, false, false));
                });
            }

            // Atualizar configuração do select2 para incluir ordem de serviço na busca
            $(selectPneu).select2({
                ajax: {
                    url: 'admin/movimentacaopneus/api/pneu/search-by-os',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term,
                            id_ordem_servico: ordemServicoId, // Incluir ordem de serviço na busca
                            limit: 20
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.data ? data.data.map(item => ({
                                id: item.value,
                                text: item.label,
                                data: item
                            })) : []
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1,
                placeholder: 'Digite para buscar...',
                allowClear: true
            });

            // Trigger change para atualizar interface
            $(selectPneu).trigger('change');
        }
        */
    }

    // ==========================================
    // 7. FUNÇÃO clearFormData CORRIGIDA
    // ==========================================

    function clearFormData() {

        const fields = [
            '[name="select_id"]',
            '[name="id_tipo_equipamento"]',
            '[name="id_categoria"]',
            '[name="id_modelo_veiculo"]',
            '[name="chassi"]',
            '[name="km_atual"]'
        ];

        fields.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) field.value = '';
        });

        // Desabilitar e limpar select de pneu
        const selectPneu = document.querySelector('[name="id_pneu"]');
        if (selectPneu) {
            selectPneu.disabled = true;
            selectPneu.value = '';

            // Controlar variável do Alpine.js para desabilitar
            const form = selectPneu.closest('form');
            if (form && form._x_dataStack) {
                try {
                    const formData = form._x_dataStack[0];
                    if (formData && typeof formData.pneuSelectDisabled !== 'undefined') {
                        formData.pneuSelectDisabled = true;
                        console.debug('✅ pneuSelectDisabled definido como true (desabilitado)');
                    }
                } catch (e) {
                    console.warn('⚠️ Erro ao desabilitar via Alpine:', e);
                }
            }

            // Usar a função global para limpar o smart-select
            if (typeof window.clearSmartSelect === 'function') {
                window.clearSmartSelect('id_pneu');
                console.debug('✅ Smart-select limpo via clearSmartSelect');
            }

            // Se for select2, limpar e desabilitar (comentado - não usado)
            /*
            if (typeof $ !== 'undefined' && typeof $(selectPneu).select2 === 'function') {
                $(selectPneu).empty().append(new Option('Selecione uma ordem de serviço primeiro...', '', true, true));
                $(selectPneu).prop('disabled', true).trigger('change');
            }
            */
        }

        formattedData = null;

        const svg = document.getElementById('caminhao');
        if (svg) svg.innerHTML = '';

        const mostarDiv = document.getElementById('mostarDiv');
        if (mostarDiv) mostarDiv.style.display = 'none';

        const areaPneusAvulsos = document.getElementById('areaPneusAvulsos');
        if (areaPneusAvulsos) areaPneusAvulsos.innerHTML = '';
    }

}

function setupModalEventListeners() {
    // Modal de remoção
    const confirmarBtn = document.getElementById('confirmar');
    const cancelarBtn = document.getElementById('cancelar');
    const confirmarAdicionarBtn = document.getElementById('confirmarAdicionar');
    const cancelarAdicionarBtn = document.getElementById('cancelarAdicionar');
    const overlay = document.getElementById('modal-overlay');

    if (confirmarBtn) {
        confirmarBtn.addEventListener('click', () => {
            const kmRemovido = document.getElementById('kmRemovido').value;
            const sulcoRemovido = document.getElementById('sulcoRemovido').value;
            const destinacaoSolicitada = document.getElementById('destinacaoSolicitada').value;

            console.log('🔍 Valores capturados do modal:', {
                kmRemovido,
                sulcoRemovido,
                destinacaoSolicitada
            });

            if (kmRemovido && sulcoRemovido && destinacaoSolicitada) {
                console.log('✅ Todos os campos preenchidos, processando...');
                moverPneuParaDrop(currentDropZone, kmRemovido, sulcoRemovido, destinacaoSolicitada);
                fecharModal();
            } else {
                console.error('❌ Campos faltando:', {
                    kmRemovido: !!kmRemovido,
                    sulcoRemovido: !!sulcoRemovido,
                    destinacaoSolicitada: !!destinacaoSolicitada
                });
                alert('Por favor, preencha todos os campos.');
            }
        });
    }

    if (cancelarBtn) {
        cancelarBtn.addEventListener('click', fecharModal);
    }

    if (confirmarAdicionarBtn) {
        confirmarAdicionarBtn.addEventListener('click', () => {
            const kmAdicionado = document.getElementById('kmAdicionado').value;
            const sulcoAdicionado = document.getElementById('sulcoAdicionado').value;

            if (kmAdicionado && sulcoAdicionado && selectedPneu) {
                selectedPneu.dataset.kmAdicionado = kmAdicionado;
                selectedPneu.dataset.sulcoAdicionado = sulcoAdicionado;

                // ✅ ATUALIZAR COR DO SVG BASEADA NO SULCO INFORMADO
                const sulcoNumerico = parseFloat(sulcoAdicionado);
                const novaCor = determinarCorPorSulco(sulcoNumerico);
                const novoSVGPath = getSVGPath(novaCor);

                // ✅ VERIFICAR SE É IMG (HTML) OU IMAGE (SVG) E ATUALIZAR CORRETAMENTE
                if (selectedPneu.tagName.toLowerCase() === 'img') {
                    // Para elementos IMG (pneus avulsos)
                    selectedPneu.src = novoSVGPath;
                    selectedPneu.setAttribute('data-original-svg', novoSVGPath);
                } else {
                    // Para elementos IMAGE (SVG - pneus no caminhão)
                    selectedPneu.setAttribute('href', novoSVGPath);
                    selectedPneu.setAttribute('data-original-svg', novoSVGPath);
                }

                // Marcar como pronto para aplicação
                selectedPneu.classList.add('pronto-aplicacao');
                selectedPneu.style.border = '3px solid #10B981'; // Verde

                fecharModalAdicionar();
            } else {
                alert('Por favor, preencha todos os campos.');
            }
        });
    }

    if (cancelarAdicionarBtn) {
        cancelarAdicionarBtn.addEventListener('click', fecharModalAdicionar);
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            fecharModal();
            fecharModalAdicionar();
        });
    }
}

function validateTipoEquipamentoData(data) {
    if (!data || typeof data !== 'object') {
        console.error('❌ Dados não são um objeto válido');
        return false;
    }

    if (typeof data.eixos !== 'number' || data.eixos <= 0) {
        console.error('❌ Propriedade "eixos" inválida:', data.eixos);
        return false;
    }

    if (!Array.isArray(data.pneus_por_eixo)) {
        console.error('❌ Propriedade "pneus_por_eixo" deve ser um array:', data.pneus_por_eixo);
        return false;
    }

    // Ajustar o array para ter o tamanho correto
    if (data.pneus_por_eixo.length > data.eixos) {
        data.pneus_por_eixo = data.pneus_por_eixo.slice(0, data.eixos);
    }

    while (data.pneus_por_eixo.length < data.eixos) {
        data.pneus_por_eixo.push(0);
    }

    // Verificar e corrigir valores null ou inválidos
    for (let i = 0; i < data.pneus_por_eixo.length; i++) {
        let numPneus = data.pneus_por_eixo[i];

        if (numPneus === null || numPneus === undefined || typeof numPneus !== 'number' || isNaN(numPneus) ||
            numPneus < 0) {
            data.pneus_por_eixo[i] = 0;
        }
    }

    return true;
}

function clearFormData() {
    const fields = [
        '[name="select_id"]',
        '[name="id_tipo_equipamento"]',
        '[name="id_categoria"]',
        '[name="id_modelo_veiculo"]',
        '[name="chassi"]',
        '[name="km_atual"]'
    ];

    fields.forEach(selector => {
        const field = document.querySelector(selector);
        if (field) field.value = '';
    });

    formattedData = null;

    const svg = document.getElementById('caminhao');
    if (svg) svg.innerHTML = '';

    const mostarDiv = document.getElementById('mostarDiv');
    if (mostarDiv) mostarDiv.style.display = 'none';

    // Limpar pneus avulsos
    const areaPneusAvulsos = document.getElementById('areaPneusAvulsos');
    if (areaPneusAvulsos) areaPneusAvulsos.innerHTML = '';


    // Garantir que estados relacionados a pneus também sejam limpos
    try {
        // Limpar seleção global
        selectedPneu = null;
        selectedPneu1 = null;
        selectedPneu2 = null;
        pneuSelecionadoParaTroca = null;

        // Limpar cache de pneus removidos (permitir que os pneus voltem a estar disponíveis)
        if (typeof limparCachePneusRemovidos === 'function') {
            limparCachePneusRemovidos();
        } else if (typeof pneusRemovidosDasOpcoes !== 'undefined' && pneusRemovidosDasOpcoes instanceof Set) {
            pneusRemovidosDasOpcoes.clear();
        }

        // Remover quaisquer marcas visuais de seleção
        document.querySelectorAll('.pneu-avulso-container').forEach(cont => {
            cont.style.border = '2px solid transparent';
            cont.style.backgroundColor = 'transparent';
            const status = cont.querySelector('.status-text');
            if (status) {
                status.textContent = 'Clique para ativar';
                status.style.color = '#6B7280';
                status.style.fontWeight = 'normal';
            }
        });

    } catch (e) {
        console.warn('⚠️ Erro ao limpar estados de pneus em clearFormData:', e);
    }
    // 🧹 LIMPAR TODOS OS PROCESSAMENTOS DE PNEUS
    limparTodosProcessamentos();
}

// ✅ FUNÇÃO PARA APLICAR CORREÇÕES BASEADAS NA CATEGORIA DO VEÍCULO
function aplicarCorrecoesCategoria(dadosOriginais, categoria, origem = 'desconhecida') {
    console.debug(`🔧 Aplicando correções para categoria: ${categoria} (origem: ${origem})`);

    // Fazer uma cópia dos dados para não alterar o original
    const dadosCorrigidos = { ...dadosOriginais };

    // Converter categoria para uppercase para comparação mais robusta
    const categoriaUpper = (categoria || '').toUpperCase();

    // ✅ CORREÇÕES ESPECÍFICAS PARA VEÍCULOS UTILITÁRIOS
    if (categoriaUpper.includes('STRADA') || categoriaUpper.includes('UTILITARIO') || categoriaUpper.includes('FIAT')) {
        console.debug('🚐 Detectado VEÍCULO UTILITÁRIO - aplicando correções específicas');

        if (dadosCorrigidos.pneus_por_eixo && dadosCorrigidos.pneus_por_eixo.length > 0) {
            // Para utilitários, todos os eixos normalmente têm 2 pneus
            dadosCorrigidos.pneus_por_eixo = dadosCorrigidos.pneus_por_eixo.map(qtd => {
                if (qtd === 4) {
                    console.debug('🔧 Corrigindo eixo de utilitário: 4 → 2 pneus');
                    return 2;
                }
                return qtd;
            });

            console.debug('✅ Configuração corrigida para utilitário:', {
                eixos: dadosCorrigidos.eixos,
                pneus_por_eixo: dadosCorrigidos.pneus_por_eixo
            });
        }
    }

    // ✅ CORREÇÕES ESPECÍFICAS PARA CAVALO MECÂNICO
    else if (categoriaUpper.includes('CAVALO') || categoriaUpper.includes('TOCO')) {
        console.debug('🐎 Detectado CAVALO MECÂNICO - aplicando correções específicas');

        if (dadosCorrigidos.pneus_por_eixo && dadosCorrigidos.pneus_por_eixo.length > 0) {
            // Para cavalos mecânicos, o primeiro eixo sempre tem 2 pneus (direção)
            if (dadosCorrigidos.pneus_por_eixo[0] !== 2) {
                console.debug('🔧 Corrigindo primeiro eixo de cavalo mecânico para 2 pneus');
                dadosCorrigidos.pneus_por_eixo[0] = 2;
            }

            console.debug('✅ Configuração corrigida para cavalo mecânico:', {
                eixos: dadosCorrigidos.eixos,
                pneus_por_eixo: dadosCorrigidos.pneus_por_eixo
            });
        }
    }

    // ✅ CORREÇÕES ESPECÍFICAS PARA MOTOCICLETA
    else if (categoriaUpper.includes('MOTO') || categoriaUpper.includes('MCF')) {
        console.debug('🏍️ Detectado MOTOCICLETA - aplicando correções específicas');

        if (dadosCorrigidos.pneus_por_eixo && dadosCorrigidos.pneus_por_eixo.length > 0) {
            // Para motocicletas, cada eixo tem 1 pneu (configuração já correta)
            console.debug('✅ Configuração de motocicleta mantida:', {
                eixos: dadosCorrigidos.eixos,
                pneus_por_eixo: dadosCorrigidos.pneus_por_eixo
            });
        }
    }

    // ✅ OUTRAS CATEGORIAS (manter configuração original com log)
    else {
        console.debug('ℹ️ Categoria não requer correção específica:', categoria);
    }

    // ✅ PRESERVAR CATEGORIA NOS DADOS CORRIGIDOS
    dadosCorrigidos.id_categoria = categoria;

    return dadosCorrigidos;
}

function renderizarCaminhao(formattedData) {

    if (formattedData && formattedData.type && formattedData.target) {
        console.error('❌ ERRO: renderizarCaminhao foi chamada com um Event ao invés de dados!');
        return;
    }

    const svg = document.getElementById('caminhao');
    if (!svg) {
        console.error('❌ ERRO: Elemento SVG com id "caminhao" não encontrado!');
        return;
    }

    if (!validateTipoEquipamentoData(formattedData)) {
        console.error('❌ ERRO: Dados inválidos para renderização');
        return;
    }

    // ✅ CONFIGURAÇÕES COM CONTROLE INDIVIDUAL DOS EIXOS E DIMENSÕES DINÂMICAS
    const layoutConfig = {
        // ✅ DIMENSÕES DINÂMICAS BASEADAS NO NÚMERO DE EIXOS
        svgWidth: 600,
        svgHeight: Math.max(600, 200 + (formattedData.eixos * (140 + 15)) + 150), // Cálculo preciso: margem inicial + (altura do eixo + espaçamento) * número de eixos + margem para textos e estepes
        centerX: 300,

        // ✅ POSICIONAMENTO VERTICAL OTIMIZADO PARA MÚLTIPLOS EIXOS
        startY: 80,
        eixoHeight: 140, // Altura fixa adequada para múltiplos eixos
        spacing: 15, // Espaçamento fixo otimizado

        // Tamanhos dos pneus
        pneuWidth: 40,
        pneuHeight: 80,

        // ✅ POSICIONAMENTO HORIZONTAL USANDO VARIÁVEIS CONFIGURÁVEIS
        // Primeiro eixo (2 pneus)
        primeiroEixo: {
            esquerdaX: POSICOES_PNEUS.DOIS_PNEUS.ESQUERDO, // Posição do pneu esquerdo (1E)
            direitaX: POSICOES_PNEUS.DOIS_PNEUS.DIREITO    // Posição do pneu direito (1D)
        },

        // Eixos com 4 pneus - USANDO VARIÁVEIS CONFIGURÁVEIS
        quartroEixos: {
            esquerdaExternaX: POSICOES_PNEUS.QUATRO_PNEUS.ESQUERDO_EXTERNO,  // 2DE, 3DE, etc.
            esquerdaInternaX: POSICOES_PNEUS.QUATRO_PNEUS.ESQUERDO_INTERNO,  // 2DI, 3DI, etc.
            direitaInternaX: POSICOES_PNEUS.QUATRO_PNEUS.DIREITO_INTERNO,    // 2EE, 3EE, etc.
            direitaExternaX: POSICOES_PNEUS.QUATRO_PNEUS.DIREITO_EXTERNO     // 2EI, 3EI, etc.
        },

        // Configurações de texto
        textoOffsetY: 9, // Distância do texto abaixo do pneu
        textoFontSize: 9,

        // ✅ CONFIGURAÇÕES PARA ESTEPES - USANDO VARIÁVEIS CONFIGURÁVEIS
        estepes: {
            E1: {
                x: POSICOES_PNEUS.ESTEPES.E1.X, // Posição X do estepe 1
                y: POSICOES_PNEUS.ESTEPES.E1.Y  // Posição Y do estepe 1
            },
            E2: {
                x: POSICOES_PNEUS.ESTEPES.E2.X, // Posição X do estepe 2
                y: POSICOES_PNEUS.ESTEPES.E2.Y  // Posição Y do estepe 2
            }
        },

        // ✅ CONFIGURAÇÕES BASE PARA LINHAS DOS EIXOS - dentro dos quadrados dos pneus
        linhasEixos: {
            // Para eixos com 1 pneu (motocicletas) - linha pequena dentro do pneu
            umPneu: { inicio: 275, fim: 325 },
            // Para eixos com 2 pneus - linha entre os pneus
            doisPneus: { inicio: 220, fim: 380 },
            // Para eixos com 4 pneus - linha dentro dos pneus externos
            quatroPneus: { inicio: 180, fim: 420 }
        },

        // Margens para estepes
        margemLateral: 50
    };

    // ✅ LOG DE DEBUG PARA CONFIGURAÇÃO DO VEÍCULO
    console.debug('🚛 Renderizando veículo com configuração:', {
        eixos: formattedData.eixos,
        pneus_por_eixo: formattedData.pneus_por_eixo,
        total_pneus: formattedData.pneus_por_eixo.reduce((sum, count) => sum + count, 0),
        categoria: formattedData.id_categoria || 'não informada',
        dimensoes: {
            largura: `${layoutConfig.svgWidth}px`,
            altura: `${layoutConfig.svgHeight}px`,
            eixoHeight: `${layoutConfig.eixoHeight}px`,
            spacing: `${layoutConfig.spacing}px`
        }
    });

    // ✅ VALIDAÇÃO CRÍTICA: Verificar se categoria + eixos batem com correção esperada
    const categoriaUpper = (formattedData.id_categoria || '').toUpperCase();
    if ((categoriaUpper.includes('STRADA') || categoriaUpper.includes('UTILITARIO') || categoriaUpper.includes('FIAT'))
        && formattedData.pneus_por_eixo && formattedData.pneus_por_eixo.some(p => p === 4)) {
        console.error('🚨 ERRO CRÍTICO: Veículo utilitário ainda tem eixos com 4 pneus!', {
            categoria: formattedData.id_categoria,
            pneus_por_eixo: formattedData.pneus_por_eixo,
            deveria_ser: '[2,2]'
        });
    }

    // Ajustar tamanho do SVG dinamicamente
    svg.setAttribute('viewBox', `0 0 ${layoutConfig.svgWidth} ${layoutConfig.svgHeight}`);
    console.debug(`📐 ViewBox ajustado para: 0 0 ${layoutConfig.svgWidth} ${layoutConfig.svgHeight}`);

    // Limpar SVG
    svg.innerHTML = '';
    let yPositions = [];

    // Remover tooltips existentes
    const existingTooltips = document.querySelectorAll('[data-tooltip="caminhao"]');
    existingTooltips.forEach(tooltip => tooltip.remove());

    // Criar novo tooltip
    const tooltip = createTooltip();

    // ✅ MAPEAMENTO DINÂMICO BASEADO NAS LOCALIZAÇÕES DO BANCO DE DADOS
    localizacaoParaPosicao = {}; // Inicializar global

    // Se há localizações dinâmicas, usar apenas elas
    if (formattedData.localizacoesDisponiveis && formattedData.localizacoesDisponiveis.length > 0) {
        console.debug('🎯 Construindo mapeamento DINÂMICO das localizações');

        formattedData.localizacoesDisponiveis.forEach((eixoLocalizacoes, eixoIndex) => {
            if (Array.isArray(eixoLocalizacoes)) {
                eixoLocalizacoes.forEach((locObj) => {
                    const loc = locObj.localizacao;

                    // Determinar posição X baseada na localização
                    let x, lado;

                    // ✅ PRIORIDADE 1: Verificar se é estepe ANTES de verificar D/E
                    if (loc.match(/^E\d+$/)) {
                        // ESTEPES (E1, E2, etc.) - usar configuração específica
                        const estepeConfig = layoutConfig.estepes[loc];
                        if (estepeConfig) {
                            x = estepeConfig.x;
                            lado = 'estepe';
                            console.debug(`🛞 MAPEAMENTO ESTEPE: ${loc} → x:${x} (configuração específica)`);
                        } else {
                            // Fallback para estepes não configurados
                            x = 480; // POSIÇÃO PADRONIZADA
                            lado = 'estepe';
                            console.warn(`⚠️ MAPEAMENTO ESTEPE: ${loc} → x:${x} (fallback padronizado)`);
                        }
                    } else if (loc.includes('DE')) {
                        // ✅ DIREITO EXTERNO (2DE, 3DE, etc.)
                        x = layoutConfig.quartroEixos.esquerdaExternaX;
                        lado = 'direito_externo';
                    } else if (loc.includes('DI')) {
                        // ✅ DIREITO INTERNO (2DI, 3DI, etc.)
                        x = layoutConfig.quartroEixos.esquerdaInternaX;
                        lado = 'direito_interno';
                    } else if (loc.includes('EE')) {
                        // ✅ ESQUERDO EXTERNO (2EE, 3EE, etc.)
                        x = layoutConfig.quartroEixos.direitaInternaX;
                        lado = 'esquerdo_externo';
                    } else if (loc.includes('EI')) {
                        // ✅ ESQUERDO INTERNO (2EI, 3EI, etc.)
                        x = layoutConfig.quartroEixos.direitaExternaX;
                        lado = 'esquerdo_interno';
                    } else if (loc.includes('D')) {
                        // ✅ DIREITA SIMPLES (1D, 2D, etc.) - apenas para eixos de 2 pneus
                        x = layoutConfig.primeiroEixo.direitaX;
                        lado = 'direito';
                    } else if (loc.includes('E')) {
                        // ✅ ESQUERDA SIMPLES (1E, 2E, etc.) - apenas para eixos de 2 pneus
                        x = layoutConfig.primeiroEixo.esquerdaX;
                        lado = 'esquerdo';
                    } else {
                        // Outras posições especiais
                        x = layoutConfig.centerX - (layoutConfig.pneuWidth / 2);
                        lado = 'centro';
                    }

                    localizacaoParaPosicao[loc] = {
                        eixo: loc.match(/^E\d+$/) ? -1 : eixoIndex, // ✅ ESTEPES usam eixo especial (-1)
                        x: x,
                        lado: lado
                    };

                    console.debug(`📍 Mapeamento dinâmico: ${loc} → eixo:${loc.match(/^E\d+$/) ? -1 : eixoIndex}, x:${x}, lado:${lado}`);
                });
            }
        });

        console.debug('✅ Mapeamento dinâmico criado:', Object.keys(localizacaoParaPosicao));
    } else {
        // FALLBACK: Se não há localizações dinâmicas, usar mapeamento hardcoded
        console.warn('⚠️ Usando mapeamento HARDCODED (fallback)');
        localizacaoParaPosicao = {
            // ✅ MOTOCICLETAS - 1 pneu por eixo (posição central)
            '1U': { eixo: 0, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },
            '2U': { eixo: 1, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },
            '3U': { eixo: 2, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },
            '4U': { eixo: 3, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },
            '5U': { eixo: 4, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },
            '6U': { eixo: 5, x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), lado: 'centro' },

            // Primeiro eixo - 2 pneus
            '1D': { eixo: 0, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '1E': { eixo: 0, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Primeiro eixo - 4 pneus (para casos onde o primeiro eixo tem 4 pneus)
            '1DE': { eixo: 0, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '1DI': { eixo: 0, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '1EE': { eixo: 0, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '1EI': { eixo: 0, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Segundo eixo - 4 pneus 
            '2DE': { eixo: 1, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '2DI': { eixo: 1, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '2EE': { eixo: 1, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '2EI': { eixo: 1, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Segundo eixo - 2 pneus (caso alternativo)
            '2D': { eixo: 1, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '2E': { eixo: 1, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Terceiro eixo - 4 pneus
            '3DE': { eixo: 2, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '3DI': { eixo: 2, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '3EE': { eixo: 2, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '3EI': { eixo: 2, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Terceiro eixo - 2 pneus (caso alternativo)
            '3D': { eixo: 2, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '3E': { eixo: 2, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Quarto eixo - 4 pneus
            '4DE': { eixo: 3, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '4DI': { eixo: 3, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '4EE': { eixo: 3, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '4EI': { eixo: 3, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Quarto eixo - 2 pneus (caso alternativo)
            '4D': { eixo: 3, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '4E': { eixo: 3, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Quinto eixo - 4 pneus (se necessário)
            '5DE': { eixo: 4, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '5DI': { eixo: 4, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '5EE': { eixo: 4, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '5EI': { eixo: 4, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Quinto eixo - 2 pneus (caso alternativo)
            '5D': { eixo: 4, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '5E': { eixo: 4, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Sexto eixo - 4 pneus (se necessário)
            '6DE': { eixo: 5, x: layoutConfig.quartroEixos.esquerdaExternaX, lado: 'esquerdo_externo' },
            '6DI': { eixo: 5, x: layoutConfig.quartroEixos.esquerdaInternaX, lado: 'esquerdo_interno' },
            '6EE': { eixo: 5, x: layoutConfig.quartroEixos.direitaInternaX, lado: 'direito_interno' },
            '6EI': { eixo: 5, x: layoutConfig.quartroEixos.direitaExternaX, lado: 'direito_externo' },

            // Sexto eixo - 2 pneus (caso alternativo)
            '6D': { eixo: 5, x: layoutConfig.primeiroEixo.esquerdaX, lado: 'direito' },
            '6E': { eixo: 5, x: layoutConfig.primeiroEixo.direitaX, lado: 'esquerdo' },

            // Estepes
            'E1': { estepe: true, x: layoutConfig.margemLateral, y: 30 },
            'E2': { estepe: true, x: layoutConfig.svgWidth - layoutConfig.margemLateral - layoutConfig.pneuWidth, y: 30 }
        };
    }

    // ✅ FUNÇÃO PARA OBTER CONFIGURAÇÃO DA LINHA DO EIXO DINAMICAMENTE
    function obterLinhaEixo(eixoIndex) {
        const numPneus = formattedData.pneus_por_eixo[eixoIndex] || 0;

        // Configuração dinâmica baseada no número de pneus do eixo específico
        if (numPneus === 1) {
            // Para motocicletas - linha mais curta e centrada
            return layoutConfig.linhasEixos.umPneu;
        } else if (numPneus === 2) {
            return layoutConfig.linhasEixos.doisPneus;
        } else if (numPneus === 4) {
            return layoutConfig.linhasEixos.quatroPneus;
        } else {
            // Fallback para casos especiais
            // Se o eixo não tem pneus definidos, inferir pelo padrão do tipo de veículo
            // Primeiro eixo geralmente tem 2 pneus, demais 4 pneus
            if (eixoIndex === 0) {
                return layoutConfig.linhasEixos.doisPneus;
            } else {
                return layoutConfig.linhasEixos.quatroPneus;
            }
        }
    }

    // ✅ RENDERIZAR ESTRUTURA DOS EIXOS COM LINHAS INDIVIDUAIS
    for (let i = 0; i < formattedData.eixos; i++) {
        const y = layoutConfig.startY + i * (layoutConfig.eixoHeight + layoutConfig.spacing);
        yPositions.push(y);

        const numPneus = formattedData.pneus_por_eixo[i] || 0;

        // ✅ LOG DE DEBUG PARA CADA EIXO
        console.debug(`🔧 Eixo ${i + 1}: configurado com ${numPneus} pneus`);

        // ✅ DEBUG: Verificar se as correções foram aplicadas corretamente
        if (i === 1) { // Segundo eixo
            console.debug(`🐛 DEBUG segundo eixo - Dados recebidos:`, {
                eixo: i + 1,
                numPneus: numPneus,
                pneus_por_eixo_completo: formattedData.pneus_por_eixo,
                categoria: formattedData.id_categoria || 'não informada'
            });
        }

        // SEMPRE renderizar os espaços conforme a quantidade definida para o tipo de veículo
        let posicoesEixo = [];

        // ✅ USAR LOCALIZAÇÕES DINÂMICAS DA TABELA EIXOS (SOLUÇÃO DEFINITIVA)
        if (formattedData.localizacoesDisponiveis && formattedData.localizacoesDisponiveis[i]) {
            // Usar localizações reais da base de dados
            const localizacoesEixo = formattedData.localizacoesDisponiveis[i];
            console.debug(`🎯 DINÂMICO: Eixo ${i + 1} usando localizações da base de dados:`, localizacoesEixo.map(l => l.localizacao));

            posicoesEixo = localizacoesEixo.map(loc => {
                // Mapear posições X baseadas na localização
                let x = 0;
                const localizacao = loc.localizacao;

                // Determinar posição X baseada no padrão da localização
                if (localizacao.includes('U')) {
                    // Único/Centro
                    x = layoutConfig.centerX - (layoutConfig.pneuWidth / 2);
                } else if (localizacao.includes('DE')) {
                    // Direito externo (2DE, 3DE, etc.)
                    x = layoutConfig.quartroEixos.esquerdaExternaX;
                } else if (localizacao.includes('DI')) {
                    // Direito interno (2DI, 3DI, etc.)
                    x = layoutConfig.quartroEixos.esquerdaInternaX;
                } else if (localizacao.includes('EE')) {
                    // Esquerdo externo (2EE, 3EE, etc.)
                    x = layoutConfig.quartroEixos.direitaInternaX;
                } else if (localizacao.includes('EI')) {
                    // Esquerdo interno (2EI, 3EI, etc.)
                    x = layoutConfig.quartroEixos.direitaExternaX;
                } else if (localizacao.endsWith('D')) {
                    // Direito simples (1D, 2D, etc.) - apenas para eixos de 2 pneus
                    x = layoutConfig.primeiroEixo.direitaX;
                } else if (localizacao.endsWith('E')) {
                    // Esquerdo simples (1E, 2E, etc.) - apenas para eixos de 2 pneus
                    x = layoutConfig.primeiroEixo.esquerdaX;
                } else if (localizacao.match(/^E\d+$/)) {
                    // ✅ ESTEPES (E1, E2, etc.) - posição específica fora do esqueleto
                    const estepeConfig = layoutConfig.estepes[localizacao];
                    if (estepeConfig) {
                        x = estepeConfig.x;
                        console.debug(`🛞 ESTEPE: ${localizacao} posicionado em X=${x} (fora do esqueleto)`);
                    } else {
                        // Fallback para estepes não configurados - usar posição padronizada
                        x = 480; // POSIÇÃO PADRONIZADA: mesma que E1 e E2
                        console.warn(`⚠️ ESTEPE: ${localizacao} usando posição fallback padronizada X=${x}`);
                    }
                } else {
                    // Fallback: usar posição X da base de dados se disponível
                    x = loc.x || layoutConfig.centerX;
                }

                return {
                    x: x,
                    localizacao: localizacao,
                    originalX: loc.x,
                    originalY: loc.y
                };
            });
        } else {
            // ✅ FALLBACK: Usar geração automática apenas se não houver dados dinâmicos
            console.warn(`⚠️ FALLBACK: Eixo ${i + 1} usando geração automática (${numPneus} pneus)`);

            if (numPneus === 1) {
                // Eixo com 1 pneu - específico para motocicletas
                posicoesEixo = [
                    { x: layoutConfig.centerX - (layoutConfig.pneuWidth / 2), localizacao: `${i + 1}U` }
                ];
            } else if (numPneus === 2) {
                // Eixo com 2 pneus - sempre usar configuração padrão (D e E)
                console.debug(`✅ FALLBACK: Criando posições para eixo ${i + 1} com 2 pneus: ${i + 1}D e ${i + 1}E`);
                posicoesEixo = [
                    { x: layoutConfig.primeiroEixo.esquerdaX, localizacao: `${i + 1}D` },
                    { x: layoutConfig.primeiroEixo.direitaX, localizacao: `${i + 1}E` }
                ];
            } else if (numPneus === 4) {
                // Eixo com 4 pneus - usar configuração completa
                console.warn(`⚠️ FALLBACK: Criando posições para eixo ${i + 1} com 4 pneus: ${i + 1}DE, ${i + 1}DI, ${i + 1}EE, ${i + 1}EI`);
                posicoesEixo = [
                    { x: layoutConfig.quartroEixos.esquerdaExternaX, localizacao: `${i + 1}DE` },
                    { x: layoutConfig.quartroEixos.esquerdaInternaX, localizacao: `${i + 1}DI` },
                    { x: layoutConfig.quartroEixos.direitaInternaX, localizacao: `${i + 1}EE` },
                    { x: layoutConfig.quartroEixos.direitaExternaX, localizacao: `${i + 1}EI` }
                ];
            } else {
                // Para casos sem configuração clara, usar 2 pneus como padrão seguro
                console.debug(`🔧 FALLBACK: Eixo ${i + 1} sem configuração clara - usando padrão de 2 pneus`);
                posicoesEixo = [
                    { x: layoutConfig.primeiroEixo.esquerdaX, localizacao: `${i + 1}D` },
                    { x: layoutConfig.primeiroEixo.direitaX, localizacao: `${i + 1}E` }
                ];
            }
        }

        // Para cada posição, desenhar pneu aplicado ou espaço vazio
        for (let j = 0; j < posicoesEixo.length; j++) {
            const pos = posicoesEixo[j];

            // ✅ TRATAMENTO ESPECIAL PARA ESTEPES
            if (pos.localizacao.match(/^E\d+$/)) {
                console.debug(`🛞 ESTEPE detectado: ${pos.localizacao} - será renderizado separadamente`);
                continue; // Pular estepes no loop normal - eles serão renderizados depois
            }

            const yRect = y - layoutConfig.pneuHeight / 2;
            let existePneuAplicado = false;
            let pneuAplicado = null;
            if (formattedData.pneusAplicadosFormatados && Array.isArray(formattedData.pneusAplicadosFormatados)) {
                pneuAplicado = formattedData.pneusAplicadosFormatados.find(p => p.localizacao === pos.localizacao);
                existePneuAplicado = !!pneuAplicado;
            }

            if (!existePneuAplicado) {
                let espaco = criarEspacoVazio(i, j, pos.x, yRect, null, 'aplicacao', pos.localizacao, null, null, null);
                if (espaco) {
                    svg.appendChild(espaco);
                } else {
                    console.error(`❌ Falha ao criar espaço vazio para ${pos.localizacao}`);
                }
            }
        }

        // ✅ CRIAR LINHA HORIZONTAL DO EIXO COM CONFIGURAÇÃO DINÂMICA
        const linhaConfig = obterLinhaEixo(i);

        // Log de debug para verificar configuração da linha
        console.debug(`📏 Eixo ${i + 1}: ${numPneus} pneus, linha de ${linhaConfig.inicio} até ${linhaConfig.fim}`);

        const eixoLinha = document.createElementNS('http://www.w3.org/2000/svg', 'line');

        eixoLinha.setAttribute('x1', linhaConfig.inicio);
        eixoLinha.setAttribute('y1', y);
        eixoLinha.setAttribute('x2', linhaConfig.fim);
        eixoLinha.setAttribute('y2', y);
        eixoLinha.setAttribute('stroke', '#333');
        eixoLinha.setAttribute('stroke-width', 3);
        svg.appendChild(eixoLinha);
    }

    // ✅ CRIAR LINHA VERTICAL CENTRAL
    if (yPositions.length > 0) {
        const eixoVertical = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        eixoVertical.setAttribute('x1', layoutConfig.centerX);
        eixoVertical.setAttribute('y1', yPositions[0]);
        eixoVertical.setAttribute('x2', layoutConfig.centerX);
        eixoVertical.setAttribute('y2', yPositions[yPositions.length - 1]);
        eixoVertical.setAttribute('stroke', '#333');
        eixoVertical.setAttribute('stroke-width', 3);
        svg.appendChild(eixoVertical);
    }

    // ✅ RENDERIZAR ESTEPES DINAMICAMENTE (E1, E2, etc.) FORA DO ESQUELETO
    const estepesEncontrados = new Set();

    if (formattedData.localizacoesDisponiveis) {
        // Procurar estepes em todos os eixos
        formattedData.localizacoesDisponiveis.forEach((eixoLocalizacoes, eixoIndex) => {
            if (Array.isArray(eixoLocalizacoes)) {
                eixoLocalizacoes.forEach((loc) => {
                    const localizacao = loc.localizacao;

                    // ✅ VERIFICAR SE É ESTEPE
                    if (localizacao.match(/^E\d+$/)) {
                        estepesEncontrados.add(localizacao);
                        console.debug(`🛞 RENDERIZANDO ESTEPE DINÂMICO: ${localizacao}`);

                        // Verificar se já existe pneu aplicado nesta posição
                        let existePneuAplicado = false;
                        let pneuAplicado = null;
                        if (formattedData.pneusAplicadosFormatados && Array.isArray(formattedData.pneusAplicadosFormatados)) {
                            pneuAplicado = formattedData.pneusAplicadosFormatados.find(p => p.localizacao === localizacao);
                            existePneuAplicado = !!pneuAplicado;
                        }

                        // Obter configuração do estepe
                        const estepeConfig = layoutConfig.estepes[localizacao];
                        if (!estepeConfig) {
                            console.warn(`⚠️ Configuração não encontrada para estepe: ${localizacao}`);
                            return;
                        }

                        const xEstepe = estepeConfig.x;
                        const yEstepe = estepeConfig.y;
                        const yRectEstepe = yEstepe - layoutConfig.pneuHeight / 2;

                        if (!existePneuAplicado) {
                            // Criar espaço vazio para estepe
                            const espacoVazioEstepe = criarEspacoVazio(
                                -1, // eixo especial para estepe
                                -1, // posição especial para estepe
                                xEstepe,
                                yRectEstepe,
                                null, // idPneuRemovido
                                'aplicacao', // tipo
                                localizacao, // localização
                                null, // kmRemovido
                                null, // sulcoRemovido
                                null  // destinacaoSolicitada
                            );
                            if (espacoVazioEstepe) {
                                svg.appendChild(espacoVazioEstepe);
                                console.debug(`✅ ESTEPE VAZIO criado: ${localizacao} em X=${xEstepe}, Y=${yEstepe} (yRect=${yRectEstepe})`);
                            }
                        } else {
                            console.debug(`🛞 ESTEPE JÁ APLICADO: ${localizacao} - pneu ${pneuAplicado.id_pneu}`);

                            // ✅ RENDERIZAR PNEU APLICADO NO ESTEPE NA POSIÇÃO CORRETA
                            const sulco = parseFloat(pneuAplicado.suco_pneu) || 0;
                            let corPneu = determinarCorPorSulco(sulco);

                            let pneuRect = criarPneu(xEstepe, yRectEstepe, corPneu, -1, -1, pneuAplicado.id_pneu);
                            pneuRect.setAttribute('data-localizacao', localizacao);

                            // ✅ CRIAR LEGENDA PARA O ESTEPE APLICADO
                            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');

                            // Posicionar texto abaixo do estepe
                            let textoX = xEstepe + layoutConfig.pneuWidth / 2;
                            let textoY = yRectEstepe + layoutConfig.pneuHeight + layoutConfig.textoOffsetY;

                            text.setAttribute('x', textoX);
                            text.setAttribute('y', textoY);
                            text.setAttribute('font-size', layoutConfig.textoFontSize);
                            text.setAttribute('text-anchor', 'middle');
                            text.setAttribute('fill', '#333');
                            text.textContent = `N: ${pneuAplicado.id_pneu} (${localizacao})`;

                            if (pneuRect) {
                                svg.appendChild(pneuRect);
                                svg.appendChild(text); // ✅ Adicionar a legenda também
                                console.debug(`✅ ESTEPE APLICADO renderizado: ${localizacao} - pneu ${pneuAplicado.id_pneu} em X=${xEstepe}, Y=${yEstepe}`);
                            }
                        }
                    }
                });
            }
        });
    }

    // ✅ GARANTIR RENDERIZAÇÃO DE TODOS OS ESTEPES PADRÃO (E1, E2)
    const estepesObrigatorios = ['E1', 'E2'];
    estepesObrigatorios.forEach(localizacaoEstepe => {
        // Se o estepe não foi encontrado na estrutura dinâmica, renderizar como fallback
        if (!estepesEncontrados.has(localizacaoEstepe)) {
            console.debug(`🛞 RENDERIZANDO ESTEPE OBRIGATÓRIO: ${localizacaoEstepe} (não encontrado na estrutura dinâmica)`);

            // Verificar se já existe pneu aplicado nesta posição
            let existePneuAplicado = false;
            let pneuAplicado = null;
            if (formattedData.pneusAplicadosFormatados && Array.isArray(formattedData.pneusAplicadosFormatados)) {
                pneuAplicado = formattedData.pneusAplicadosFormatados.find(p => p.localizacao === localizacaoEstepe);
                existePneuAplicado = !!pneuAplicado;
            }

            // Obter configuração do estepe
            const estepeConfig = layoutConfig.estepes[localizacaoEstepe];
            if (estepeConfig) {
                const xEstepe = estepeConfig.x;
                const yEstepe = estepeConfig.y;
                const yRectEstepe = yEstepe - layoutConfig.pneuHeight / 2;

                if (!existePneuAplicado) {
                    // Criar espaço vazio para estepe obrigatório
                    const espacoVazioEstepe = criarEspacoVazio(
                        -1, // eixo especial para estepe
                        -1, // posição especial para estepe
                        xEstepe,
                        yRectEstepe,
                        null, // idPneuRemovido
                        'aplicacao', // tipo
                        localizacaoEstepe, // localização
                        null, // kmRemovido
                        null, // sulcoRemovido
                        null  // destinacaoSolicitada
                    );
                    if (espacoVazioEstepe) {
                        svg.appendChild(espacoVazioEstepe);
                        console.debug(`✅ ESTEPE OBRIGATÓRIO VAZIO criado: ${localizacaoEstepe} em X=${xEstepe}, Y=${yEstepe}`);
                    }
                } else {
                    console.debug(`🛞 ESTEPE OBRIGATÓRIO JÁ APLICADO: ${localizacaoEstepe} - pneu ${pneuAplicado.id_pneu}`);
                }
            } else {
                console.error(`❌ Configuração não encontrada para estepe obrigatório: ${localizacaoEstepe}`);
            }
        }
    });

    // ✅ FALLBACK: CRIAR ESPAÇOS VAZIOS PARA ESTEPES HARDCODED (E1 e E2) - APENAS SE NÃO FORAM RENDERIZADOS DINAMICAMENTE
    // REMOVIDO: Esta seção estava causando duplicação de espaços vazios para E1 e E2
    // A seção anterior "GARANTIR RENDERIZAÇÃO DE TODOS OS ESTEPES PADRÃO" já cobre este caso

    // ✅ RENDERIZAR PNEUS APLICADOS COM POSIÇÕES E TEXTOS CORRETOS
    if (formattedData.pneusAplicadosFormatados && Array.isArray(formattedData.pneusAplicadosFormatados)) {

        console.debug('🎯 Renderizando pneus aplicados:', {
            quantidade: formattedData.pneusAplicadosFormatados.length,
            localizacoes: formattedData.pneusAplicadosFormatados.map(p => p.localizacao),
            mapeamento_disponivel: Object.keys(localizacaoParaPosicao)
        });

        formattedData.pneusAplicadosFormatados.forEach((pneu, index) => {
            let localizacao = pneu.localizacao;

            // ✅ PULAR ESTEPES - eles são renderizados separadamente na seção específica
            if (localizacao.match(/^E\d+$/)) {
                console.debug(`🛞 PULANDO estepe ${localizacao} - já renderizado na seção específica`);
                return; // Pular estepes
            }

            // ✅ CORREÇÃO CRÍTICA: Corrigir localizações incorretas para veículos utilitários
            const categoriaUpper = (formattedData.id_categoria || '').toUpperCase();
            if (categoriaUpper.includes('STRADA') || categoriaUpper.includes('UTILITARIO') || categoriaUpper.includes('FIAT')) {
                // Para utilitários, converter DE->D, DI->D, EE->E, EI->E
                if (localizacao.includes('DE')) {
                    const novaLocalizacao = localizacao.replace('DE', 'D');
                    console.debug(`🔧 CORREÇÃO: ${localizacao} → ${novaLocalizacao} (utilitário)`);
                    localizacao = novaLocalizacao;
                } else if (localizacao.includes('DI')) {
                    const novaLocalizacao = localizacao.replace('DI', 'D');
                    console.debug(`🔧 CORREÇÃO: ${localizacao} → ${novaLocalizacao} (utilitário)`);
                    localizacao = novaLocalizacao;
                } else if (localizacao.includes('EE')) {
                    const novaLocalizacao = localizacao.replace('EE', 'E');
                    console.debug(`🔧 CORREÇÃO: ${localizacao} → ${novaLocalizacao} (utilitário)`);
                    localizacao = novaLocalizacao;
                } else if (localizacao.includes('EI')) {
                    const novaLocalizacao = localizacao.replace('EI', 'E');
                    console.debug(`🔧 CORREÇÃO: ${localizacao} → ${novaLocalizacao} (utilitário)`);
                    localizacao = novaLocalizacao;
                }
            }

            const posicaoInfo = localizacaoParaPosicao[localizacao];

            if (!posicaoInfo) {
                console.warn(`⚠️ Localização "${localizacao}" não mapeada para pneu ${pneu.id_pneu}`);
                console.warn('💡 Mapeamentos disponíveis:', Object.keys(localizacaoParaPosicao));
                return;
            }

            let x, y;

            if (posicaoInfo.estepe) {
                // Posições dos estepes
                x = posicaoInfo.x;
                y = posicaoInfo.y;
                console.debug(`🛞 ESTEPE ${localizacao}: x=${x}, y=${y}`);
            } else {
                // Posições dos pneus nos eixos
                x = posicaoInfo.x;
                y = yPositions[posicaoInfo.eixo] - layoutConfig.pneuHeight / 2;

                // ✅ DEBUG DETALHADO PARA COORDENADAS Y
                console.debug(`🔍 DEBUG Y para ${localizacao}:`, {
                    eixo: posicaoInfo.eixo,
                    yPositions_array: yPositions,
                    yPosition_eixo: yPositions[posicaoInfo.eixo],
                    pneuHeight: layoutConfig.pneuHeight,
                    y_final: y
                });

                if (isNaN(y)) {
                    console.error(`❌ NaN detectado para pneu ${pneu.id_pneu} localização ${localizacao}!`);
                    return; // Pular este pneu se Y for NaN
                }
            }

            // Determinar cor baseada no sulco
            const sulco = parseFloat(pneu.suco_pneu) || 0;
            let corPneu = determinarCorPorSulco(sulco);            // ✅ BUSCAR E SUBSTITUIR O PLACEHOLDER EXATO
            const placeholderSelector = `.pneu[data-localizacao="${localizacao}"]`;
            let elementoExistente = svg.querySelector(placeholderSelector);

            // Criar novo pneu
            let pneuRect = criarPneu(x, y, corPneu, posicaoInfo.eixo || 0, 0, pneu.id_pneu);
            pneuRect.setAttribute('data-localizacao', localizacao);

            // ✅ POSICIONAR TEXTO DE FORMA INTELIGENTE PARA EVITAR SOBREPOSIÇÃO
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');

            // Calcular posição do texto baseada na localização
            let textoX, textoY;

            textoX = x + layoutConfig.pneuWidth / 2;
            textoY = y + layoutConfig.pneuHeight + layoutConfig.textoOffsetY;

            text.setAttribute('x', textoX);
            text.setAttribute('y', textoY);
            text.setAttribute('font-size', layoutConfig.textoFontSize);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('fill', '#333');
            text.textContent = `N: ${pneu.id_pneu} (${localizacao})`;

            // Adicionar eventos de tooltip
            pneuRect.addEventListener('mouseover', (event) => {
                if (!trocaEmAndamento) {
                    // Garantir que existe um tooltip
                    let tooltipElement = document.querySelector('[data-tooltip="caminhao"]');
                    if (!tooltipElement) {
                        tooltipElement = createTooltip();
                    }

                    // Tentar diferentes propriedades para o sulco
                    const sulcoValue = pneu.suco_pneu || pneu.sulco_pneu || pneu.sulco || pneu.sulco_pneu_adicionado || 'N/A';

                    // Posicionar e mostrar tooltip
                    tooltipElement.style.display = 'block';
                    tooltipElement.style.left = `${event.clientX + 10}px`;
                    tooltipElement.style.top = `${event.clientY - 30}px`;
                    tooltipElement.textContent = `Sulco do pneu: ${sulcoValue}mm`;
                }
            });

            pneuRect.addEventListener('mouseout', () => {
                const tooltipElement = document.querySelector('[data-tooltip="caminhao"]');
                if (tooltipElement) {
                    tooltipElement.style.display = 'none';
                }
            });            // Substituir placeholder ou adicionar novo
            if (elementoExistente) {
                elementoExistente.replaceWith(pneuRect);
            } else {
                svg.appendChild(pneuRect);
            }

            svg.appendChild(text);
        });
    }

    // Configurar eventos
    setTimeout(() => {
        configurarEventosPneus();
        configurarEventosDropzones();
    }, 100);

    dadosArray = coletarDadosParaEnvio();

    // Mostrar interface
    const mostarDiv = document.getElementById('mostarDiv');
    if (mostarDiv) {
        mostarDiv.style.display = 'flex';
    }
}

function getLocalizacaoParaEixoPosicao(eixo, lado, posicaoLocal) {
    const eixoNum = eixo + 1;

    if (eixoNum === 1) {
        return lado === 'esquerdo' ? '1D' : '1E';
    } else {
        const mapeamento = {
            'esquerdo_externo': `${eixoNum}DE`,
            'esquerdo_interno': `${eixoNum}DI`,
            'direito_interno': `${eixoNum}EE`,
            'direito_externo': `${eixoNum}EI`
        };
        return mapeamento[lado] || `${eixoNum}DE`;
    }
}

function configurarEventosPneus() {
    // ❌ REMOVIDO: Não clonar elementos para preservar eventos de tooltip
    // document.querySelectorAll('.pneu').forEach(pneu => {
    //     const novoPneu = pneu.cloneNode(true);
    //     pneu.parentNode.replaceChild(novoPneu, pneu);
    // });

    // Limpar apenas os event listeners de click antigos
    document.querySelectorAll('.pneu').forEach(pneu => {
        // Verificar se já tem event listener de click
        if (!pneu.hasAttribute('data-click-configured')) {
            pneu.setAttribute('data-click-configured', 'true');

            // Configurar clique nos pneus do caminhão
            pneu.addEventListener('click', function (event) {
                event.stopPropagation();

                const pneuId = this.getAttribute('data-id');

                // Se há um pneu avulso selecionado, tentar fazer a troca
                if (pneuSelecionadoParaTroca && pneuSelecionadoParaTroca.classList.contains(
                    'pronto-aplicacao')) {

                    // 🚨 NOVA VALIDAÇÃO: Verificar se é tentativa de substituição direta
                    const validacaoOcupacao = validarPosicaoOcupada(this);

                    if (validacaoOcupacao.ocupada) {
                        alert(validacaoOcupacao.mensagem);
                        console.warn('🚨 SUBSTITUIÇÃO DIRETA BLOQUEADA: Posição ocupada');

                        // Feedback visual: piscar o pneu em vermelho para indicar erro
                        const originalHref = this.getAttribute('href');
                        this.setAttribute('href', getSVGPath('red'));
                        setTimeout(() => {
                            this.setAttribute('href', originalHref);
                        }, 300);

                        deselecionarPneuAvulso();
                        return;
                    }

                    // Se chegou aqui, é um espaço vazio válido
                    trocarPneuAvulsoComAplicado(pneuSelecionadoParaTroca, this);
                    return;
                }

                // Lógica para troca entre pneus do caminhão
                if (!selectedPneu1) {
                    selectedPneu1 = this;
                    this.setAttribute('href', getSVGPath('orange'));
                    trocaEmAndamento = true;

                } else if (!selectedPneu2 && this !== selectedPneu1) {
                    selectedPneu2 = this;
                    this.setAttribute('href', getSVGPath('orange'));

                    const pneu2Id = this.getAttribute('data-id');

                    trocarPneus(selectedPneu1, selectedPneu2);

                    selectedPneu1 = null;
                    selectedPneu2 = null;
                    trocaEmAndamento = false;

                } else if (this === selectedPneu1) {
                    this.setAttribute('href', this.getAttribute('data-original-svg'));
                    selectedPneu1 = null;
                    trocaEmAndamento = false;
                }
            });
        }
    });
}

function configurarEventosDropzones() {
    document.querySelectorAll('.dropzone').forEach(zone => {
        zone.addEventListener('click', function () {

            if (selectedPneu1 && !selectedPneu2) {
                currentDropZone = this;
                abrirModal();
            } else {
                console.warn('⚠️ Nenhum pneu selecionado para remoção');
            }
        });
    });
}

function criarPneuAvulso(id, sulco, tipoPneu = null) {

    const areaPneusAvulsos = document.getElementById('areaPneusAvulsos');
    if (!areaPneusAvulsos) {
        console.error('❌ Área de pneus avulsos não encontrada');
        return;
    }

    // ✅ USAR A FUNÇÃO CENTRALIZADA DE DETERMINAÇÃO DE COR
    const sulcoNum = parseFloat(sulco);
    const corPneu = determinarCorPorSulco(sulcoNum);

    // Container do pneu
    const pneuContainer = document.createElement('div');
    pneuContainer.className = 'pneu-avulso-container';
    pneuContainer.style.cssText = `
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 10px;
        padding: 8px;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    `;

    // Imagem do pneu
    const pneuImg = document.createElement('img');
    pneuImg.src = getSVGPath(corPneu);
    pneuImg.alt = `Pneu ${id}`;
    pneuImg.style.cssText = `
        width: 40px;
        height: 80px;
        pointer-events: none;
    `;
    pneuImg.dataset.id = id;
    pneuImg.dataset.sulco = sulco;
    // ✅ CORREÇÃO: Armazenar o tipo do pneu no dataset
    if (tipoPneu) {
        pneuImg.dataset.tipo_pneu = tipoPneu;
    }
    pneuImg.classList.add('pneu-avulso');

    // Texto do ID
    const pneuIdText = document.createElement('span');
    pneuIdText.textContent = `N: ${id}`;
    pneuIdText.style.cssText = `
        margin-top: 5px;
        font-size: 12px;
        color: #374151;
        font-weight: 500;
        pointer-events: none;
    `;

    // Status do pneu
    const statusText = document.createElement('span');
    statusText.textContent = 'Clique para ativar';
    statusText.className = 'status-text';
    statusText.style.cssText = `
        margin-top: 2px;
        font-size: 10px;
        color: #6B7280;
        font-style: italic;
        pointer-events: none;
    `;

    // ✅ NOVO: Botão de cancelar
    const botaoCancelar = document.createElement('button');
    botaoCancelar.textContent = '✕ Cancelar';
    botaoCancelar.className = 'botao-cancelar-pneu';
    botaoCancelar.style.cssText = `
        margin-top: 5px;
        padding: 2px 6px;
        background-color: #EF4444;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 9px;
        cursor: pointer;
        transition: background-color 0.2s;
    `;

    botaoCancelar.addEventListener('click', function (event) {
        event.stopPropagation(); // Evitar que o clique no botão ative o pneu
        cancelarPneuAvulso(pneuImg);
    });

    botaoCancelar.addEventListener('mouseover', function () {
        this.style.backgroundColor = '#DC2626';
    });

    botaoCancelar.addEventListener('mouseout', function () {
        this.style.backgroundColor = '#EF4444';
    });

    // ✅ ADICIONAR: Indicador visual para pneus recapados/vulcanizados
    if (tipoPneu && (tipoPneu.toLowerCase().includes('vulcanizado') ||
        tipoPneu.toLowerCase().includes('recapado') ||
        tipoPneu.toLowerCase().includes('recapagem'))) {
        const avisoText = document.createElement('span');
        avisoText.textContent = '⚠️ Não pode ir no 1º eixo';
        avisoText.style.cssText = `
            margin-top: 2px;
            font-size: 9px;
            color: #DC2626;
            font-weight: bold;
            text-align: center;
            pointer-events: none;
        `;
        pneuContainer.appendChild(avisoText);

        // Adicionar borda vermelha para destacar
        pneuContainer.style.border = '2px solid #FCA5A5';
        pneuContainer.style.backgroundColor = '#FEF2F2';
    }

    // Montar container
    pneuContainer.appendChild(pneuImg);
    pneuContainer.appendChild(pneuIdText);
    pneuContainer.appendChild(statusText);
    pneuContainer.appendChild(botaoCancelar);

    // Tooltip
    const tooltip = document.createElement('div');
    tooltip.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid black;
        padding: 8px;
        display: none;
        pointer-events: none;
        z-index: 9999;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 12px;
    `;
    // ✅ MELHORAR: Tooltip com informações do tipo
    let tooltipText = `Sulco do pneu: ${sulco}mm`;
    if (tipoPneu) {
        tooltipText += `\nTipo: ${tipoPneu}`;
    }
    tooltip.textContent = tooltipText;
    document.body.appendChild(tooltip);

    // Events do tooltip
    pneuContainer.addEventListener('mouseover', (event) => {
        tooltip.style.display = 'block';
        tooltip.style.left = `${event.clientX + 10}px`;
        tooltip.style.top = `${event.clientY + 10}px`;
    });

    pneuContainer.addEventListener('mouseout', () => {
        tooltip.style.display = 'none';
    });

    // Event de clique - CORRIGIDO
    pneuContainer.addEventListener('click', function () {

        // Se já estava selecionado, desselecionar
        if (pneuSelecionadoParaTroca === pneuImg) {
            deselecionarPneuAvulso();
            return;
        }

        // Desselecionar qualquer pneu anteriormente selecionado
        deselecionarPneuAvulso();

        // Se o pneu não tem dados de aplicação, abrir modal
        if (!pneuImg.dataset.kmAdicionado || !pneuImg.dataset.sulcoAdicionado) {
            selectedPneu = pneuImg;
            abrirModalAdicionar();
        } else {
            // Pneu já tem dados, selecionar para troca
            selecionarPneuAvulso(pneuImg, pneuContainer, statusText);
        }
    });

    // Adicionar à área
    areaPneusAvulsos.appendChild(pneuContainer);

    // Atualizar dados
    dadosArray = coletarDadosParaEnvio();
}

function selecionarPneuAvulso(pneuImg, container, statusText) {
    pneuSelecionadoParaTroca = pneuImg;
    container.style.border = '3px solid #F59E0B';
    container.style.backgroundColor = '#FEF3C7';
    statusText.textContent = 'Clique em um ESPAÇO VAZIO do caminhão';
    statusText.style.color = '#D97706';
    statusText.style.fontWeight = 'bold';

    // Destacar espaços vazios disponíveis
    document.querySelectorAll('.espaco-vazio').forEach(espaco => {
        espaco.classList.add('espaco-vazio-disponivel');
    });

    // Adicionar feedback para pneus ocupados
    document.querySelectorAll('.pneu[data-id]:not([data-id="null"])').forEach(pneu => {
        pneu.style.cursor = 'not-allowed';
        pneu.title = 'Posição ocupada - remova o pneu atual primeiro';
    });
}

function deselecionarPneuAvulso() {
    if (pneuSelecionadoParaTroca) {
        const container = pneuSelecionadoParaTroca.closest('.pneu-avulso-container');
        const statusText = container.querySelector('.status-text');

        if (pneuSelecionadoParaTroca.classList.contains('pronto-aplicacao')) {
            container.style.border = '3px solid #10B981';
            container.style.backgroundColor = '#ECFDF5';
            statusText.textContent = 'Pronto para aplicação';
            statusText.style.color = '#059669';
        } else {
            container.style.border = '2px solid transparent';
            container.style.backgroundColor = 'transparent';
            statusText.textContent = 'Clique para ativar';
            statusText.style.color = '#6B7280';
            statusText.style.fontWeight = 'normal';
        }

        // Remover destaques visuais
        document.querySelectorAll('.espaco-vazio-disponivel').forEach(espaco => {
            espaco.classList.remove('espaco-vazio-disponivel');
        });

        document.querySelectorAll('.pneu').forEach(pneu => {
            pneu.style.cursor = 'pointer';
            pneu.title = '';
        });

        pneuSelecionadoParaTroca = null;
    }
}

marcarEspacosVaziosDisponiveis();

function trocarPneuAvulsoComAplicado(pneuAvulso, pneuAplicado) {

    // ✅ VERIFICAR SE É UM ESPAÇO VAZIO (posição definida)
    const isEspacoVazio = pneuAplicado.classList.contains('espaco-vazio');

    if (isEspacoVazio) {

        // Obter localização do espaço vazio de forma mais robusta
        let localizacao = pneuAplicado.getAttribute('data-localizacao');

        // ✅ VALIDAÇÃO: Se não tem localização no atributo, tentar calcular
        if (!localizacao || localizacao === 'null') {
            const eixo = pneuAplicado.getAttribute('data-eixo');
            const posicao = pneuAplicado.getAttribute('data-posicao');

            if (eixo !== null && posicao !== null) {
                localizacao = getLocalizacao(eixo, posicao);
            }
        }

        if (!localizacao || localizacao === 'null') {
            console.error('❌ Espaço vazio sem localização definida');
            alert('Erro: Posição não possui localização definida');
            deselecionarPneuAvulso();
            return;
        }

        // ✅ APLICAR PNEU NA LOCALIZAÇÃO ESPECÍFICA
        aplicarPneuAvulsoEmPosicao(pneuAvulso, pneuAplicado, localizacao);
        return;
    }

    // ✅ VALIDAÇÃO DE OCUPAÇÃO (código existente)
    const validacaoOcupacao = validarPosicaoOcupada(pneuAplicado);

    if (validacaoOcupacao.ocupada) {
        alert(validacaoOcupacao.mensagem);
        console.warn('🚨 SUBSTITUIÇÃO DIRETA BLOQUEADA: Posição ocupada, remoção obrigatória');
        deselecionarPneuAvulso();
        return;
    }

    // ✅ MELHORAR EXTRAÇÃO DE DADOS - MÚLTIPLOS MÉTODOS SEQUENCIAIS
    let localizacao = null;
    let idPneuAplicado = null;

    // ✅ DEPURAR ELEMENTO CLICADO
    depurarLocalizacao(pneuAplicado, 'ELEMENTO_CLICADO');

    // Método 1: Atributo direto (mais confiável)
    localizacao = pneuAplicado.getAttribute('data-localizacao');
    if (localizacao && localizacao !== 'null') {
        console.debug('✅ Método 1 - Localização do atributo:', localizacao);

        // ✅ CORREÇÃO CRÍTICA IMEDIATA: Corrigir localização incorreta para utilitários
        const categoriaAtual = formattedData?.id_categoria || 'desconhecida';
        const categoriaUpper = categoriaAtual.toUpperCase();

        if (categoriaUpper.includes('STRADA') || categoriaUpper.includes('UTILITARIO') || categoriaUpper.includes('FIAT')) {
            let localizacaoCorrigida = localizacao;

            // Aplicar correções para utilitários
            if (localizacao.includes('DE')) {
                localizacaoCorrigida = localizacao.replace('DE', 'D');
            } else if (localizacao.includes('DI')) {
                localizacaoCorrigida = localizacao.replace('DI', 'D');
            } else if (localizacao.includes('EE')) {
                localizacaoCorrigida = localizacao.replace('EE', 'E');
            } else if (localizacao.includes('EI')) {
                localizacaoCorrigida = localizacao.replace('EI', 'E');
            }

            if (localizacaoCorrigida !== localizacao) {
                console.debug(`🔧 CORREÇÃO APLICADA: ${localizacao} → ${localizacaoCorrigida} (categoria: ${categoriaAtual})`);
                localizacao = localizacaoCorrigida;

                // ✅ ATUALIZAR O ATRIBUTO DO ELEMENTO TAMBÉM
                pneuAplicado.setAttribute('data-localizacao', localizacao);
            }
        }
    }

    // Método 2: Calcular baseado em eixo/posição
    if (!localizacao || localizacao === 'null') {
        const eixo = pneuAplicado.getAttribute('data-eixo');
        const posicao = pneuAplicado.getAttribute('data-posicao');

        if (eixo !== null && posicao !== null) {
            localizacao = getLocalizacao(eixo, posicao);
        }
    }

    // Método 3: Extrair do texto adjacente
    if (!localizacao || localizacao === 'null') {
        const textElement = pneuAplicado.nextElementSibling;
        if (textElement && textElement.tagName === 'text') {
            const textContent = textElement.textContent;

            const regex = /N:\s*(\d+)\s*\((.*?)\)/;
            const match = textContent.match(regex);

            if (match) {
                idPneuAplicado = match[1];
                localizacao = match[2];
            }
        }
    }

    // Método 4: Procurar entre elementos adjacentes (mais amplo)
    if (!localizacao || localizacao === 'null') {

        const parent = pneuAplicado.parentElement;
        if (parent) {
            const siblings = Array.from(parent.children);
            const elementIndex = siblings.indexOf(pneuAplicado);

            // Verificar elementos adjacentes (antes e depois)
            for (let i = Math.max(0, elementIndex - 3); i <= Math.min(siblings.length - 1, elementIndex + 3); i++) {
                const sibling = siblings[i];
                if (sibling.tagName === 'text' && sibling.textContent) {
                    const regex = /\((.*?)\)/;
                    const match = sibling.textContent.match(regex);
                    if (match && match[1].length <= 3) { // Localizações são curtas como 1D, 1E, 2DE, etc.
                        localizacao = match[1];
                        break;
                    }
                }
            }
        }
    }

    // Método 5: Buscar por coordenadas próximas (último recurso)
    if (!localizacao || localizacao === 'null') {

        const rect = pneuAplicado.getBoundingClientRect();
        const espacosVazios = document.querySelectorAll('.espaco-vazio[data-localizacao]');
        const pneusAplicados = document.querySelectorAll('.pneu[data-localizacao]');

        const todosElementos = [...espacosVazios, ...pneusAplicados];

        let menorDistancia = Infinity;
        let localizacaoMaisProxima = null;

        todosElementos.forEach(elemento => {
            if (elemento === pneuAplicado) return; // Pular o próprio elemento

            const elementoRect = elemento.getBoundingClientRect();
            const distancia = Math.sqrt(
                Math.pow(rect.x - elementoRect.x, 2) +
                Math.pow(rect.y - elementoRect.y, 2)
            );

            if (distancia < menorDistancia && distancia < 100) { // Máximo 100px de distância
                const localizacaoElemento = elemento.getAttribute('data-localizacao');
                if (localizacaoElemento && localizacaoElemento !== 'null') {
                    menorDistancia = distancia;
                    localizacaoMaisProxima = localizacaoElemento;
                }
            }
        });

        if (localizacaoMaisProxima) {
            localizacao = localizacaoMaisProxima;
        }
    }

    // ✅ VALIDAÇÃO FINAL
    if (!localizacao || localizacao === 'null') {
        console.error('❌ FALHA TOTAL: Não foi possível determinar a localização');

        alert('Erro crítico: Não foi possível identificar a posição. Clique em um espaço vazio claramente marcado ou recarregue a página.');
        deselecionarPneuAvulso();
        return;
    }

    // ✅ APLICAR PNEU COM LOCALIZAÇÃO DEFINIDA
    aplicarPneuAvulsoEmPosicao(pneuAvulso, pneuAplicado, localizacao);

    // ✅ DISPARAR AUTO-SAVE APÓS APLICAÇÃO BEM-SUCEDIDA
    setTimeout(() => {

        if (typeof triggerAutoSave === 'function') {
            triggerAutoSave('aplicacao_pneu_avulso', {
                pneu_avulso_id: pneuAvulso.dataset.id,
                localizacao: localizacao, // ✅ Localização correta garantida
                km_aplicado: pneuAvulso.dataset.kmAdicionado,
                sulco_aplicado: pneuAvulso.dataset.sulcoAdicionado
            });
        } else {
            console.error('❌ triggerAutoSave não disponível');
        }
    }, 500); // Aguardar a aplicação ser concluída
}

// ✅ FUNÇÃO ADICIONAL: Validar consistência das localizações no sistema
function validarConsistenciaLocalizacoes() {

    const problemas = [];

    // Verificar espaços vazios
    const espacosVazios = document.querySelectorAll('.espaco-vazio');
    espacosVazios.forEach((espaco, index) => {
        const localizacao = espaco.getAttribute('data-localizacao');
        const eixo = espaco.getAttribute('data-eixo');
        const posicao = espaco.getAttribute('data-posicao');

        if (!localizacao || localizacao === 'null') {
            problemas.push(`Espaço vazio ${index + 1}: sem localização (eixo: ${eixo}, posição: ${posicao})`);
        } else {
            const localizacaoCalculada = getLocalizacao(eixo, posicao);
            if (localizacaoCalculada !== localizacao) {
                problemas.push(`Espaço vazio ${index + 1}: divergência (atual: ${localizacao}, esperado: ${localizacaoCalculada})`);
            }
        }
    });

    // Verificar pneus aplicados
    const pneusAplicados = document.querySelectorAll('.pneu[data-id]:not([data-id="null"])');
    pneusAplicados.forEach((pneu, index) => {
        const localizacao = pneu.getAttribute('data-localizacao');
        const eixo = pneu.getAttribute('data-eixo');
        const posicao = pneu.getAttribute('data-posicao');
        const idPneu = pneu.getAttribute('data-id');

        if (!localizacao || localizacao === 'null') {
            // Tentar extrair do texto
            const textElement = pneu.nextElementSibling;
            let localizacaoTexto = null;
            if (textElement && textElement.tagName === 'text') {
                const match = textElement.textContent.match(/\((.*?)\)/);
                if (match) localizacaoTexto = match[1];
            }

            problemas.push(`Pneu ${idPneu}: sem localização no atributo (eixo: ${eixo}, posição: ${posicao}, texto: ${localizacaoTexto})`);
        }
    });

    if (problemas.length > 0) {
        console.warn('⚠️ PROBLEMAS DE LOCALIZAÇÃO DETECTADOS:', problemas);
        return { valido: false, problemas };
    } else {
        return { valido: true, problemas: [] };
    }
}

// ✅ FUNÇÃO DE CORREÇÃO AUTOMÁTICA
function corrigirLocalizacoesInconsistentes() {

    let correcoes = 0;

    // Corrigir espaços vazios
    const espacosVazios = document.querySelectorAll('.espaco-vazio');
    espacosVazios.forEach(espaco => {
        const localizacao = espaco.getAttribute('data-localizacao');
        const eixo = espaco.getAttribute('data-eixo');
        const posicao = espaco.getAttribute('data-posicao');

        if ((!localizacao || localizacao === 'null') && eixo !== null && posicao !== null) {
            const localizacaoCorreta = getLocalizacao(eixo, posicao);
            if (localizacaoCorreta) {
                espaco.setAttribute('data-localizacao', localizacaoCorreta);

                // Atualizar texto também
                const textoEspaco = espaco.parentElement.querySelector('.texto-espaco-vazio');
                if (textoEspaco) {
                    textoEspaco.textContent = `VAZIO (${localizacaoCorreta})`;
                }
                correcoes++;
            }
        }
    });

    // Corrigir pneus aplicados
    const pneusAplicados = document.querySelectorAll('.pneu[data-id]:not([data-id="null"])');
    pneusAplicados.forEach(pneu => {
        const localizacao = pneu.getAttribute('data-localizacao');
        const eixo = pneu.getAttribute('data-eixo');
        const posicao = pneu.getAttribute('data-posicao');
        const idPneu = pneu.getAttribute('data-id');

        if ((!localizacao || localizacao === 'null') && eixo !== null && posicao !== null) {
            const localizacaoCorreta = getLocalizacao(eixo, posicao);
            if (localizacaoCorreta) {
                pneu.setAttribute('data-localizacao', localizacaoCorreta);

                // Atualizar texto também
                const textElement = pneu.nextElementSibling;
                if (textElement && textElement.tagName === 'text') {
                    textElement.textContent = `N: ${idPneu} (${localizacaoCorreta})`;
                }
                correcoes++;
            }
        }
    });

    return correcoes;
}

function aplicarPneuAvulsoEmPosicao(pneuAvulso, elementoPosicao, localizacao) {

    // 🛡️ VERIFICAR SE PNEU PODE SER PROCESSADO
    const pneuId = pneuAvulso.dataset.id;
    if (!pneuPodeSerProcessado(pneuId)) {
        console.warn(`🚫 Operação bloqueada: Pneu ${pneuId} não pode ser processado no momento`);
        deselecionarPneuAvulso();
        return;
    }

    // 🔒 MARCAR PNEU COMO PROCESSANDO
    marcarPneuComoProcessando(pneuId);

    try {
        // ✅ CORREÇÃO: NÃO aplicar conversões - manter localizações originais corretas
        // A lógica de mapeamento foi corrigida na renderização, não precisamos converter aqui

        // ✅ CRUCIAL: Verificar se o pneu avulso tem dados de aplicação válidos
        if (!pneuAvulso.dataset.kmAdicionado || !pneuAvulso.dataset.sulcoAdicionado) {
            // ✅ FALLBACK: Tentar obter km atual do veículo como último recurso
            const kmAtualInput = document.querySelector('[name="km_atual"]');
            const sulcoOriginal = pneuAvulso.dataset.sulco;

            if (kmAtualInput && kmAtualInput.value && sulcoOriginal) {
                pneuAvulso.dataset.kmAdicionado = kmAtualInput.value;
                pneuAvulso.dataset.sulcoAdicionado = sulcoOriginal;
            } else {
                alert('Erro: Pneu sem dados de aplicação válidos. Preencha o KM e sulco antes de aplicar.');
                deselecionarPneuAvulso();
                return;
            }
        }

        // ✅ VALIDAÇÃO CRÍTICA: Verificar se localização foi fornecida
        if (!localizacao || localizacao === 'null' || localizacao === 'undefined') {
            // Tentar extrair do elemento
            localizacao = extrairLocalizacaoDoElemento(elementoPosicao);

            if (!localizacao) {
                alert('Erro crítico: Não foi possível determinar a localização da posição. Contate o suporte.');
                deselecionarPneuAvulso();
                return;
            }
        }
        const validacao = validarPneuPrimeiroEixo(pneuAvulso, localizacao);

        if (!validacao.valido) {
            alert(validacao.mensagem);
            console.warn(`🚨 APLICAÇÃO BLOQUEADA: ${validacao.mensagem}`);
            deselecionarPneuAvulso();
            return;
        }

        // Obter dados do pneu avulso
        const sulcoAvulso = parseFloat(pneuAvulso.dataset.sulcoAdicionado);
        const corNova = determinarCorPorSulco(sulcoAvulso);

        // Posição do elemento no SVG
        const x = elementoPosicao.getAttribute('x');
        const y = elementoPosicao.getAttribute('y');
        const eixo = elementoPosicao.getAttribute('data-eixo') || 0;
        const posicao = elementoPosicao.getAttribute('data-posicao') || 0;

        // ✅ VALIDAÇÃO FINAL: Confirmar localização com base no eixo/posição (apenas para posições de eixo, não estepes)
        const eixoNum = parseInt(eixo);
        const posicaoNum = parseInt(posicao);

        // ✅ IDENTIFICAR SE É ESTEPE PELA LOCALIZAÇÃO DO SVG
        const ehEstepe = localizacao && (localizacao.startsWith('E') || localizacao === 'E1' || localizacao === 'E2');

        // Se é um estepe, NÃO recalcular localização com base em eixo/posição
        if (ehEstepe) {
            // Manter localização original para estepes - eles têm localização própria
        } else if (eixoNum < 0 || posicaoNum < 0) {
            // Manter localização original para estepes
        } else {
            // Para posições de eixo normais, validar localização
            const localizacaoCalculada = getLocalizacao(eixo, posicao);

            if (localizacaoCalculada && localizacaoCalculada !== localizacao) {
                // ✅ CORREÇÃO CRÍTICA: Usar localizações dinâmicas ao invés de lista hardcoded
                const localizacoesValidas = Object.keys(localizacaoParaPosicao);

                if (localizacoesValidas.includes(localizacao)) {
                    // Manter a localização do SVG que é mais confiável
                } else {
                    // Só usar a calculada se a do SVG não for válida
                    localizacao = localizacaoCalculada;
                }
            }
        }

        // Criar novo pneu visual
        let novoPneu = criarPneu(x, y, corNova, eixo, posicao, pneuAvulso.dataset.id);

        // ✅ CRUCIAL: Armazenar TODOS os dados necessários no elemento visual
        novoPneu.setAttribute('data-id-pneu', pneuAvulso.dataset.id);
        novoPneu.setAttribute('data-localizacao', localizacao);
        novoPneu.setAttribute('data-km-adicionado', pneuAvulso.dataset.kmAdicionado || '0');
        novoPneu.setAttribute('data-sulco-adicionado', pneuAvulso.dataset.sulcoAdicionado || '0');
        novoPneu.setAttribute('data-status', 'APLICADO');

        // ✅ IMPORTANTE: Armazenar a localização no pneu avulso para referência futura
        pneuAvulso.dataset.localizacao = localizacao;
        if (pneuAvulso.dataset.tipo_pneu) {
            novoPneu.setAttribute('data-tipo-pneu', pneuAvulso.dataset.tipo_pneu);
        }

        // Atualizar atributos visuais
        novoPneu.setAttribute('href', getSVGPath(corNova));
        novoPneu.setAttribute('data-original-svg', getSVGPath(corNova));

        // ✅ CRUCIAL: Definir localização no elemento do pneu
        novoPneu.setAttribute('data-localizacao', localizacao);

        // Criar texto para o novo pneu COM LOCALIZAÇÃO CORRETA
        const svg = document.getElementById('caminhao');
        const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        text.setAttribute('x', parseFloat(x) + config.pneuWidth / 2);
        text.setAttribute('y', parseFloat(y) + config.pneuHeight + 15);
        text.setAttribute('font-size', '10');
        text.setAttribute('text-anchor', 'middle');
        text.textContent = `N: ${pneuAvulso.dataset.id} (${localizacao})`; // ✅ Usar localização correta

        // Adicionar tooltip
        const tooltip = document.querySelector('[data-tooltip="caminhao"]') || createTooltip();
        novoPneu.addEventListener('mouseover', (event) => {
            if (!trocaEmAndamento) {
                tooltip.style.display = 'block';
                tooltip.style.left = `${event.clientX + 10}px`;
                tooltip.style.top = `${event.clientY - 30}px`;
                tooltip.textContent = `Sulco do pneu: ${pneuAvulso.dataset.sulcoAdicionado}mm`;
            }
        });

        novoPneu.addEventListener('mouseout', () => {
            const tooltip = document.querySelector('[data-tooltip="caminhao"]');
            if (tooltip) tooltip.style.display = 'none';
        });

        // Substituir elemento visual
        elementoPosicao.replaceWith(novoPneu);
        svg.appendChild(text);

        // ✅ ATUALIZAR DADOS GLOBAIS COM LOCALIZAÇÃO CORRETA E DADOS COMPLETOS
        if (formattedData?.pneusAplicadosFormatados) {
            // ✅ PRIMEIRO: Remover qualquer pneu que já esteja na mesma localização
            const pneusAntigos = formattedData.pneusAplicadosFormatados.filter(p => p.localizacao === localizacao);

            formattedData.pneusAplicadosFormatados = formattedData.pneusAplicadosFormatados.filter(p =>
                p.localizacao !== localizacao
            );

            // Verificar se pneu já existe no array (por ID)
            const pneuExistente = formattedData.pneusAplicadosFormatados.find(p =>
                String(p.id_pneu) === String(pneuAvulso.dataset.id)
            );

            if (pneuExistente) {
                // Atualizar localização do pneu existente
                pneuExistente.localizacao = localizacao;
                pneuExistente.suco_pneu = parseFloat(pneuAvulso.dataset.sulcoAdicionado);
                pneuExistente.sulco_pneu_adicionado = parseFloat(pneuAvulso.dataset.sulcoAdicionado);
                pneuExistente.km_adicionado = parseFloat(pneuAvulso.dataset.kmAdicionado);
            } else {
                // Adicionar novo pneu aos dados
                const novoPneuData = {
                    id_pneu: parseInt(pneuAvulso.dataset.id),
                    localizacao: localizacao, // ✅ Localização já corrigida
                    suco_pneu: parseFloat(pneuAvulso.dataset.sulcoAdicionado),
                    sulco_pneu_adicionado: parseFloat(pneuAvulso.dataset.sulcoAdicionado),
                    km_adicionado: parseFloat(pneuAvulso.dataset.kmAdicionado)
                };

                formattedData.pneusAplicadosFormatados.push(novoPneuData);
            }

            console.debug(`✅ FormattedData atualizado: pneu ${pneuAvulso.dataset.id} na localização ${localizacao}`);
        }

        // Reconfigurar eventos para o novo pneu
        configurarEventosPneus();

        // Remover o pneu avulso da interface
        const containerAvulso = pneuAvulso.closest('.pneu-avulso-container');
        if (containerAvulso) {
            containerAvulso.remove();
        }

        // ✅ PROTEÇÃO: Remover o pneu aplicado das opções do select apenas se não foi removido antes
        const pneuId = pneuAvulso.dataset.id;
        if (!pneusRemovidosDasOpcoes.has(String(pneuId))) {
            console.debug(`🔄 Removendo pneu ${pneuId} das opções após aplicação`);
            removerPneuDasOpcoes(pneuId);
        } else {
            console.debug(`🔄 Pneu ${pneuId} já foi removido das opções anteriormente`);
        }

        // Limpar seleção
        pneuSelecionadoParaTroca = null;

        // Atualizar dados para envio
        dadosArray = coletarDadosParaEnvio();

        // ✅ DEBUG: Verificar se a localização está correta nos dados coletados
        if (dadosArray?.pneusAplicados) {
            const pneuAplicado = dadosArray.pneusAplicados.find(p => String(p.id_pneu) === String(pneuAvulso.dataset.id));
            if (pneuAplicado) {
                console.debug(`📋 Dados coletados para pneu ${pneuAvulso.dataset.id}:`, pneuAplicado);

                // ✅ CORREÇÃO: Se a localização está incorreta ou muito genérica, corrigir
                if (!pneuAplicado.localizacao ||
                    pneuAplicado.localizacao === 'UNK' ||
                    pneuAplicado.localizacao === 'DESCONHEC' ||
                    pneuAplicado.localizacao !== localizacao) {

                    console.warn(`🔧 Corrigindo localização do pneu ${pneuAvulso.dataset.id}: "${pneuAplicado.localizacao}" → "${localizacao}"`);
                    pneuAplicado.localizacao = localizacao.length > 10 ? localizacao.substring(0, 10) : localizacao;

                    // ✅ ATUALIZAR TAMBÉM NO DADOSARRAY GLOBAL
                    window.dadosArray = dadosArray;
                }
            } else {
                console.warn(`⚠️ Pneu ${pneuAvulso.dataset.id} não encontrado nos dados coletados!`);
            }
        }

        // Mostrar notificação de sucesso
        if (typeof showNotification === 'function') {
            showNotification(
                `Pneu ${pneuAvulso.dataset.id} aplicado na posição ${localizacao}`,
                'success'
            );
        }

        // Verificar se o triggerAutoSave existe
        if (typeof triggerAutoSave === 'function') {
            triggerAutoSave('aplicacao_pneu_avulso', {
                pneu_avulso_id: pneuAvulso.dataset.id,
                localizacao: localizacao, // ✅ Localização correta garantida (ex: "E2")
                km_aplicado: pneuAvulso.dataset.kmAdicionado,
                sulco_aplicado: pneuAvulso.dataset.sulcoAdicionado
            });
        } else {
            console.error('❌ triggerAutoSave não está disponível');
        }

    } catch (error) {
        console.error(`❌ Erro ao aplicar pneu ${pneuId}:`, error);
        alert(`Erro ao aplicar pneu: ${error.message}`);
        deselecionarPneuAvulso();
    } finally {
        // 🔓 SEMPRE LIBERAR O PNEU DO PROCESSAMENTO
        liberarPneuProcessamento(pneuId);
    }
}


function determinarCorPorSulco(sulco) {
    let cor;
    if (sulco > 24) cor = 'black';  // preto para maior que 24
    else if (sulco >= 21) cor = 'green';  // verde entre 21 e 24 (inclusive)
    else if (sulco >= 15) cor = 'blue';   // azul entre 15 e 20 (inclusive)
    else if (sulco >= 11) cor = 'yellow'; // amarelo entre 11 e 14 (inclusive)
    else cor = 'red';    // vermelho menor que 11

    return cor;
}

function getSVGPath(color) {
    const svgPaths = {
        'black': '/vendor/bladewind/images/pneu_preto.svg',
        'green': '/vendor/bladewind/images/pneu_verde.svg',
        'blue': '/vendor/bladewind/images/pneu_azul.svg',
        'yellow': '/vendor/bladewind/images/pneu_amarelo.svg',
        'red': '/vendor/bladewind/images/pneu_vermelho.svg',
        'orange': '/vendor/bladewind/images/pneu_laranja.svg',
        '#ccc': '/vendor/bladewind/images/pneu_cinza.svg'
    };

    const path = svgPaths[color] || svgPaths['#ccc'];
    return path;
}

function abrirModal() {
    const modal = document.getElementById('modal');
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.style.display = 'block';
        overlay.style.display = 'block';
    }
}

function fecharModal() {
    const modal = document.getElementById('modal');
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.style.display = 'none';
        overlay.style.display = 'none';
    }

    // Limpar campos
    const kmRemovido = document.getElementById('kmRemovido');
    const sulcoRemovido = document.getElementById('sulcoRemovido');
    const destinacaoSolicitada = document.getElementById('destinacaoSolicitada');
    if (kmRemovido) kmRemovido.value = '';
    if (sulcoRemovido) sulcoRemovido.value = '';
    if (destinacaoSolicitada) destinacaoSolicitada.value = '';

    // Limpar seleção se necessário
    if (selectedPneu1 && !selectedPneu2) {
        selectedPneu1.setAttribute('href', selectedPneu1.getAttribute('data-original-svg'));
        selectedPneu1 = null;
        trocaEmAndamento = false;
    }
}

function abrirModalAdicionar() {
    const modal = document.getElementById('modal-adicionar');
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.style.display = 'block';
        overlay.style.display = 'block';

        // ✅ PRÉ-PREENCHER O CAMPO SULCO COM O VALOR DO PNEU SELECIONADO
        const sulcoAdicionadoInput = document.getElementById('sulcoAdicionado');
        if (sulcoAdicionadoInput && selectedPneu) {
            // Obter o sulco do pneu (pode ser do dataset.sulco ou dataset.sulco_original)
            const sulcoPneu = selectedPneu.dataset.sulco || selectedPneu.dataset.sulco_original || '';

            if (sulcoPneu && sulcoPneu !== 'null' && sulcoPneu !== '0') {
                sulcoAdicionadoInput.value = sulcoPneu;
                console.debug(`📏 Campo sulco pré-preenchido com: ${sulcoPneu}mm para pneu ${selectedPneu.dataset.id}`);

                // Focar no campo para que o usuário saiba que pode editar
                setTimeout(() => {
                    sulcoAdicionadoInput.focus();
                    sulcoAdicionadoInput.select();
                }, 100);
            } else {
                // Se não há valor de sulco, focar no campo para digitação
                setTimeout(() => {
                    sulcoAdicionadoInput.focus();
                }, 100);
            }

            // Garantir que o campo está sempre habilitado para edição
            sulcoAdicionadoInput.disabled = false;
            sulcoAdicionadoInput.readOnly = false;
        }
    }
}

function fecharModalAdicionar() {
    const modal = document.getElementById('modal-adicionar');
    const overlay = document.getElementById('modal-overlay');
    if (modal && overlay) {
        modal.style.display = 'none';
        overlay.style.display = 'none';
    }

    // Limpar campos
    const kmAdicionado = document.getElementById('kmAdicionado');
    const sulcoAdicionado = document.getElementById('sulcoAdicionado');
    if (kmAdicionado) kmAdicionado.value = '';
    if (sulcoAdicionado) sulcoAdicionado.value = '';
}

function moverPneuParaDrop(zone, kmRemovido, sulcoRemovido, destinacaoSolicitada) {
    console.log('🔧 PARÂMETROS moverPneuParaDrop:', { kmRemovido, sulcoRemovido, destinacaoSolicitada });

    if (!selectedPneu1 || !zone) {
        console.error('❌ selectedPneu1 ou zone não definidos');
        return;
    }

    const tipo = zone.dataset.tipo;
    const eixo = selectedPneu1.getAttribute('data-eixo');
    const posicao = selectedPneu1.getAttribute('data-posicao');
    const x = selectedPneu1.getAttribute('x');
    const y = selectedPneu1.getAttribute('y');

    // ✅ MELHORAR EXTRAÇÃO DE DADOS DO PNEU COM VALIDAÇÃO
    let idPneuRemovido = null;
    let localizacao = null;

    // Método 1: Extrair do texto adjacente
    const textElement = selectedPneu1.nextElementSibling;
    if (textElement && textElement.textContent) {
        const regex = /N:\s*(\d+)\s*\((.*?)\)/;
        const match = textElement.textContent.match(regex);

        if (match) {
            idPneuRemovido = match[1];
            localizacao = match[2];
        }
    }

    // Método 2: Fallback para atributos do elemento
    if (!idPneuRemovido || !localizacao) {

        // ID do pneu
        if (!idPneuRemovido) {
            idPneuRemovido = selectedPneu1.getAttribute('data-id');
        }

        // Localização
        if (!localizacao) {
            // Tentar atributo direto primeiro
            localizacao = selectedPneu1.getAttribute('data-localizacao');

            // ✅ CORREÇÃO: NÃO converter localizações - manter as corretas (2DI, 2DE)
            // A lógica de mapeamento foi corrigida na renderização, não precisamos converter aqui

            // Se não tem, calcular pela posição
            if (!localizacao || localizacao === 'null') {
                if (eixo !== null && posicao !== null) {
                    localizacao = getLocalizacao(eixo, posicao);
                }
            }
        }
    }

    // ✅ VALIDAÇÃO FINAL DOS DADOS EXTRAÍDOS
    if (!idPneuRemovido || !localizacao || localizacao === 'null') {
        console.error('❌ Falha na extração de dados do pneu', {
            idPneuRemovido,
            localizacao,
            eixo,
            posicao,
            elemento_classes: Array.from(selectedPneu1.classList),
            elemento_atributos: Array.from(selectedPneu1.attributes).map(attr => `${attr.name}=${attr.value}`)
        });

        alert('Erro: Não foi possível identificar os dados do pneu selecionado. Tente novamente.');
        return;
    }

    // Remover texto associado
    if (textElement && textElement.tagName === 'text') {
        textElement.remove();
    }

    // ✅ CRIAR ESPAÇO VAZIO COM LOCALIZAÇÃO CORRETA E VALIDADA
    let espacoVazio = criarEspacoVazio(eixo, posicao, x, y, idPneuRemovido, tipo, localizacao, kmRemovido, sulcoRemovido, destinacaoSolicitada);

    // ✅ VALIDAÇÃO CRÍTICA: Verificar se o espaço vazio foi criado corretamente
    if (!espacoVazio) {
        console.error('❌ ERRO CRÍTICO: Falha ao criar espaço vazio');
        alert('Erro crítico ao criar espaço vazio. Recarregue a página.');
        return;
    }

    const localizacaoEspaco = espacoVazio.getAttribute('data-localizacao');

    if (!localizacaoEspaco || localizacaoEspaco === 'null') {
        console.error('❌ ERRO CRÍTICO: Espaço vazio criado sem localização!');
        alert('Erro crítico ao criar espaço vazio. Recarregue a página.');
        return;
    }

    // ✅ VALIDAÇÃO ADICIONAL: Verificar se localização do espaço vazio coincide com a do pneu removido
    if (localizacaoEspaco !== localizacao) {
        console.warn('⚠️ DIVERGÊNCIA DE LOCALIZAÇÃO DETECTADA:', {
            localizacao_pneu_removido: localizacao,
            localizacao_espaco_vazio: localizacaoEspaco
        });

        // Corrigir a localização do espaço vazio
        espacoVazio.setAttribute('data-localizacao', localizacao);

        // Atualizar texto do espaço vazio também
        const textoEspaco = document.querySelector('.texto-espaco-vazio:last-child');
        if (textoEspaco) {
            textoEspaco.textContent = `VAZIO (${localizacao})`;
        }

    }

    // Substituir o pneu pelo espaço vazio
    selectedPneu1.replaceWith(espacoVazio);

    // ✅ ATUALIZAR DADOS GLOBAIS - REMOVER PNEU DA LISTA
    if (formattedData?.pneusAplicadosFormatados) {
        const index = formattedData.pneusAplicadosFormatados.findIndex(p =>
            String(p.id_pneu) === String(idPneuRemovido) && p.localizacao === localizacao
        );

        if (index !== -1) {
            const pneuRemovido = formattedData.pneusAplicadosFormatados.splice(index, 1)[0];
            console.warn('📤 Pneu removido dos dados globais:', pneuRemovido);
        }
    }

    // Limpar seleção
    selectedPneu1 = null;
    currentDropZone = null;
    trocaEmAndamento = false;

    // Atualizar dados para envio
    dadosArray = coletarDadosParaEnvio();

    // ✅ DISPARAR AUTO-SAVE PARA REMOÇÃO
    if (typeof triggerAutoSave === 'function') {
        const dadosAutoSave = {
            pneu_removido_id: idPneuRemovido,
            localizacao: localizacao, // ✅ Localização correta garantida
            destino: tipo,
            km_removido: kmRemovido,
            sulco_removido: sulcoRemovido,
            destinacao_solicitada: destinacaoSolicitada
        };

        console.log('🚀 Enviando dados para auto-save:', dadosAutoSave);
        triggerAutoSave('remocao_pneu', dadosAutoSave);
    }
}

// ✅ FUNÇÃO PARA VERIFICAR INTEGRIDADE APÓS OPERAÇÕES
function verificarIntegridadeAposOperacao() {

    const problemas = [];

    // Verificar se há elementos sem localização
    const elementosSemLocalizacao = document.querySelectorAll('[data-eixo][data-posicao]:not([data-localizacao]), [data-localizacao="null"]');

    if (elementosSemLocalizacao.length > 0) {
        problemas.push(`${elementosSemLocalizacao.length} elementos sem localização definida`);

        elementosSemLocalizacao.forEach((elemento, index) => {
            const eixo = elemento.getAttribute('data-eixo');
            const posicao = elemento.getAttribute('data-posicao');
            const id = elemento.getAttribute('data-id') || elemento.className;

            console.warn(`⚠️ Elemento ${index + 1} sem localização:`, {
                id,
                eixo,
                posicao,
                classes: Array.from(elemento.classList)
            });
        });
    }

    // Verificar duplicatas de localização
    const elementos = document.querySelectorAll('[data-localizacao]:not([data-localizacao="null"])');
    const localizacoes = {};

    elementos.forEach(elemento => {
        const loc = elemento.getAttribute('data-localizacao');
        if (!localizacoes[loc]) {
            localizacoes[loc] = [];
        }
        localizacoes[loc].push(elemento);
    });

    Object.entries(localizacoes).forEach(([loc, elementos]) => {
        if (elementos.length > 1) {
            problemas.push(`Localização ${loc} duplicada em ${elementos.length} elementos`);
            console.warn(`⚠️ Localização duplicada ${loc}:`, elementos);
        }
    });

    if (problemas.length === 0) {
        console.warn('✅ Integridade verificada - nenhum problema encontrado');
    } else {
        console.warn('⚠️ Problemas de integridade detectados:', problemas);
    }

    return { problemas, elementosSemLocalizacao, localizacoesDuplicadas: localizacoes };
}


function criarPneu(x, y, color, eixo, posicao, id = null) {
    const svgPath = getSVGPath(color);

    let pneu = document.createElementNS('http://www.w3.org/2000/svg', 'image');
    pneu.setAttribute('x', x);
    pneu.setAttribute('y', y);
    pneu.setAttribute('width', 40);
    pneu.setAttribute('height', 80);
    pneu.setAttribute('href', svgPath);
    pneu.setAttribute('data-original-svg', svgPath);
    pneu.setAttribute('class', 'pneu');
    pneu.setAttribute('data-eixo', eixo);
    pneu.setAttribute('data-posicao', posicao);
    if (id && id !== 'null') {
        pneu.setAttribute('data-id', id);
    }

    return pneu;
}

function criarEspacoVazio(eixo, posicao, x, y, idPneuRemovido, tipo, localizacao, kmRemovido, sulcoRemovido, destinacaoSolicitada) {

    // ✅ VALIDAÇÃO: Se localização não foi fornecida, calcular (apenas para posições de eixo)
    if (!localizacao || localizacao === 'null') {
        // Para estepes com valores especiais
        if (eixo === -1 && posicao === -1) {
            localizacao = 'E1'; // Estepe esquerdo
        } else if (eixo === -2 && posicao === -2) {
            localizacao = 'E2'; // Estepe direito
        } else if (eixo >= 0 && posicao >= 0) {
            // Para posições de eixo normais
            localizacao = getLocalizacao(eixo, posicao);
        }
    }

    // ✅ VALIDAÇÃO FINAL: Verificar se localização é válida
    if (!localizacao || localizacao === 'null') {
        console.error('❌ ERRO CRÍTICO: Localização inválida para espaço vazio', {
            eixo: eixo,
            posicao: posicao,
            x: x,
            y: y,
            localizacao: localizacao
        });
        return null;
    }

    let espacoVazio = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    espacoVazio.setAttribute('x', x);
    espacoVazio.setAttribute('y', y);
    espacoVazio.setAttribute('width', config.pneuWidth);
    espacoVazio.setAttribute('height', config.pneuHeight);
    espacoVazio.setAttribute('fill', 'transparent');
    espacoVazio.setAttribute('stroke', 'gray');
    espacoVazio.setAttribute('stroke-dasharray', '5,5');
    espacoVazio.setAttribute('stroke-width', '2');
    espacoVazio.setAttribute('class', 'espaco-vazio');
    espacoVazio.setAttribute('data-eixo', eixo);
    espacoVazio.setAttribute('data-posicao', posicao);
    espacoVazio.setAttribute('data-id', idPneuRemovido);
    espacoVazio.setAttribute('data-destino', tipo);

    // ✅ CRUCIAL: Definir localização correta
    espacoVazio.setAttribute('data-localizacao', localizacao);

    espacoVazio.setAttribute('data-kmRemovido', kmRemovido);
    espacoVazio.setAttribute('data-sulcoRemovido', sulcoRemovido);
    espacoVazio.setAttribute('data-destinacao-solicitada', destinacaoSolicitada || '');

    // Adicionar estilo visual melhor
    espacoVazio.style.cursor = 'pointer';
    espacoVazio.style.opacity = '0.7';

    // Criar texto informativo para o espaço vazio
    const svg = document.getElementById('caminhao');
    const textoEspacoVazio = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    textoEspacoVazio.setAttribute('x', parseFloat(x) + config.pneuWidth / 2);
    textoEspacoVazio.setAttribute('y', parseFloat(y) + config.pneuHeight + 15);
    textoEspacoVazio.setAttribute('font-size', '10');
    textoEspacoVazio.setAttribute('text-anchor', 'middle');
    textoEspacoVazio.setAttribute('fill', 'gray');
    textoEspacoVazio.setAttribute('class', 'texto-espaco-vazio');
    textoEspacoVazio.textContent = `VAZIO (${localizacao})`;

    // ✅ EVENTO MELHORADO com validação de localização
    espacoVazio.addEventListener('click', function (event) {
        event.stopPropagation();

        let localizacaoEspaco = this.getAttribute('data-localizacao');
        const eixoEspaco = this.getAttribute('data-eixo');
        const posicaoEspaco = this.getAttribute('data-posicao');

        if (pneuSelecionadoParaTroca && pneuSelecionadoParaTroca.classList.contains('pronto-aplicacao')) {

            // ✅ VERIFICAR SE TEM LOCALIZAÇÃO VÁLIDA
            if (!localizacaoEspaco || localizacaoEspaco === 'null') {
                alert('Erro: Esta posição não possui localização definida. Contate o suporte.');
                console.error('❌ Espaço vazio sem localização:', {
                    eixo: eixoEspaco,
                    posicao: posicaoEspaco,
                    localizacao: localizacaoEspaco
                });
                return;
            }

            // ✅ CORREÇÃO: NÃO converter localizações - manter as corretas (2DI, 2DE)
            // A lógica de mapeamento foi corrigida na renderização, não precisamos converter aqui

            // Usar a função centralizada
            aplicarPneuAvulsoEmPosicao(pneuSelecionadoParaTroca, this, localizacaoEspaco);
        }
    });

    // Efeito visual ao passar o mouse
    espacoVazio.addEventListener('mouseenter', function () {
        if (pneuSelecionadoParaTroca && pneuSelecionadoParaTroca.classList.contains('pronto-aplicacao')) {
            this.setAttribute('fill', 'rgba(16, 185, 129, 0.3)'); // Verde claro
            this.setAttribute('stroke', '#10B981');
            this.setAttribute('stroke-width', '3');
        }
    });

    espacoVazio.addEventListener('mouseleave', function () {
        this.setAttribute('fill', 'transparent');
        this.setAttribute('stroke', 'gray');
        this.setAttribute('stroke-width', '2');
    });

    // Adicionar o texto ao SVG também
    svg.appendChild(textoEspacoVazio);

    return espacoVazio;
}

function createTooltip() {
    // Verificar se já existe um tooltip
    let existingTooltip = document.querySelector('[data-tooltip="caminhao"]');
    if (existingTooltip) {
        return existingTooltip;
    }

    const tooltip = document.createElement('div');
    tooltip.setAttribute('data-tooltip', 'caminhao');
    tooltip.style.cssText = `
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border: 1px solid #333;
            padding: 8px 12px;
            display: none;
            pointer-events: none;
            z-index: 9999;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            font-size: 12px;
            font-family: system-ui, -apple-system, sans-serif;
            white-space: nowrap;
            max-width: 300px;
        `;
    document.body.appendChild(tooltip);
    return tooltip;
}

function trocarPneus(pneu1, pneu2) {

    // Obter IDs dos pneus dos elementos DOM
    const id1 = pneu1.getAttribute('data-id');
    const id2 = pneu2.getAttribute('data-id');

    if (!id1 || !id2) {
        console.error('❌ IDs dos pneus não encontrados');
        return;
    }

    // Encontrar os dados dos pneus no array
    const pneu1Data = formattedData.pneusAplicadosFormatados.find(p =>
        String(p.id_pneu) === String(id1));
    const pneu2Data = formattedData.pneusAplicadosFormatados.find(p =>
        String(p.id_pneu) === String(id2));

    if (!pneu1Data || !pneu2Data) {
        console.error('❌ Dados dos pneus não encontrados no array');
        return;
    }

    // 🚨 VALIDAÇÃO DE SEGURANÇA: Verificar se algum pneu vai para o primeiro eixo
    const localizacaoDestino1 = pneu2Data.localizacao; // Para onde o pneu1 vai
    const localizacaoDestino2 = pneu1Data.localizacao; // Para onde o pneu2 vai
    const localizacaoOrigem1 = pneu1Data.localizacao; // De onde o pneu1 vem
    const localizacaoOrigem2 = pneu2Data.localizacao; // De onde o pneu2 vem

    // Verificar se algum pneu que NÃO estava no primeiro eixo vai para o primeiro eixo
    let pneuProblematico = null;
    let localizacaoProblematica = null;

    if (!localizacaoOrigem1.startsWith('1') && localizacaoDestino1.startsWith('1')) {
        pneuProblematico = id1;
        localizacaoProblematica = localizacaoDestino1;
    } else if (!localizacaoOrigem2.startsWith('1') && localizacaoDestino2.startsWith('1')) {
        pneuProblematico = id2;
        localizacaoProblematica = localizacaoDestino2;
    }

    if (pneuProblematico) {
        // 🚫 BLOQUEAR COMPLETAMENTE A OPERAÇÃO
        alert(
            `🚫 OPERAÇÃO BLOQUEADA POR SEGURANÇA!\n\n` +
            `O pneu ${pneuProblematico} seria movido para o primeiro eixo (posição ${localizacaoProblematica}).\n\n` +
            `Por questões de segurança viária, esta operação é PROIBIDA, pois:\n` +
            `• Pneus vulcanizados/recapados não podem ir no primeiro eixo\n` +
            `• O primeiro eixo é responsável pela direção do veículo\n` +
            `• Esta é uma norma de segurança obrigatória\n\n` +
            `Se você precisa fazer esta troca, primeiro verifique:\n` +
            `1. Se o pneu é realmente novo (não vulcanizado/recapado)\n` +
            `2. Remova o pneu atual e adicione o novo através do sistema de adição de pneus avulsos`
        );

        console.warn(
            `🚨 OPERAÇÃO BLOQUEADA: Tentativa de mover pneu ${pneuProblematico} para primeiro eixo (${localizacaoProblematica})`
        );

        // Restaurar cores originais dos pneus selecionados
        selectedPneu1.setAttribute('href', selectedPneu1.getAttribute('data-original-svg'));
        selectedPneu2.setAttribute('href', selectedPneu2.getAttribute('data-original-svg'));

        // Limpar seleções
        selectedPneu1 = null;
        selectedPneu2 = null;
        trocaEmAndamento = false;

        return; // PARAR COMPLETAMENTE A EXECUÇÃO
    }


    // Trocar as localizações
    const tempLocalizacao = pneu1Data.localizacao;
    pneu1Data.localizacao = pneu2Data.localizacao;
    pneu2Data.localizacao = tempLocalizacao;

    // ✅ VERIFICAÇÃO DE INTEGRIDADE: Confirmar que ambos os objetos foram atualizados
    console.debug('✅ Troca manual realizada:', {
        pneu1: { id: id1, novaLocalizacao: pneu1Data.localizacao },
        pneu2: { id: id2, novaLocalizacao: pneu2Data.localizacao }
    });

    // Re-renderizar o caminhão com correções aplicadas
    const dadosCorrigidos = aplicarCorrecoesCategoria(formattedData, formattedData.id_categoria || '', 'troca_pneus');
    renderizarCaminhao(dadosCorrigidos);
}

function getLocalizacao(eixo, posicao) {
    // ✅ PRIORIDADE: Se há localizações dinâmicas carregadas, usar apenas elas
    if (formattedData && formattedData.localizacoesDisponiveis && formattedData.localizacoesDisponiveis.length > 0) {
        const eixoIndex = parseInt(eixo);
        const posicaoIndex = parseInt(posicao);

        // Buscar nas localizações dinâmicas
        if (formattedData.localizacoesDisponiveis[eixoIndex] &&
            formattedData.localizacoesDisponiveis[eixoIndex][posicaoIndex]) {
            const localizacaoObj = formattedData.localizacoesDisponiveis[eixoIndex][posicaoIndex];
            console.debug(`🎯 DINÂMICO getLocalizacao: eixo ${eixo}, posição ${posicao} → ${localizacaoObj.localizacao}`);
            return localizacaoObj.localizacao;
        }

        console.warn(`⚠️ DINÂMICO getLocalizacao: não encontrou eixo ${eixo}, posição ${posicao} nas localizações dinâmicas`);
        return null; // Não permitir fallback para hardcoded quando há dados dinâmicos
    }

    // FALLBACK: Só usar lógica hardcoded se NÃO há localizações dinâmicas
    console.warn(`⚠️ FALLBACK getLocalizacao: usando lógica hardcoded para eixo ${eixo}, posição ${posicao}`);

    const eixoNum = parseInt(eixo);
    const posicaoNum = parseInt(posicao);

    // ✅ CORREÇÃO: Verificar se é veículo utilitário para usar mapeamento correto
    const categoriaVeiculo = formattedData?.id_categoria || '';
    const ehUtilitario = categoriaVeiculo.toLowerCase().includes('strada') ||
        categoriaVeiculo.toLowerCase().includes('utilitario') ||
        categoriaVeiculo.toLowerCase().includes('fiat') ||
        categoriaVeiculo.toLowerCase().includes('renault') ||
        categoriaVeiculo.toLowerCase().includes('kangoo') ||
        categoriaVeiculo.toLowerCase().includes('express');

    // ✅ MAPEAMENTO ESPECÍFICO PARA UTILITÁRIOS - todos os eixos com 2 pneus
    if (ehUtilitario && eixoNum === 1) { // Segundo eixo de utilitários
        const localizacaoUtilitario = {
            '1-0': '2D', // Direita  
            '1-1': '2E'  // Esquerda
        };

        const chave = `${eixoNum}-${posicaoNum}`;
        return localizacaoUtilitario[chave] || null;
    }

    // ✅ MAPEAMENTO PADRÃO PARA OUTROS VEÍCULOS
    const localizacaoMap = {
        // Primeiro eixo (eixo 0) - 2 pneus
        '0-0': '1D', // Segunda posição = Direita
        '0-1': '1E', // Primeira posição = Esquerda ✅ CORRIGIDO

        // Segundo eixo (eixo 1) - 4 pneus
        '1-0': '2DE', // Direita Externa
        '1-1': '2DI', // Direita Interna  
        '1-2': '2EE', // Esquerda Externa
        '1-3': '2EI', // Esquerda Interna

        // Terceiro eixo (eixo 2) - 4 pneus
        '2-0': '3DE',
        '2-1': '3DI',
        '2-2': '3EE',
        '2-3': '3EI',

        // Quarto eixo (eixo 3) - 4 pneus
        '3-0': '4DE',
        '3-1': '4DI',
        '3-2': '4EE',
        '3-3': '4EI',

        // Estepes
        'E1': 'E1',
        'E2': 'E2'
    };

    const chave = `${eixoNum}-${posicaoNum}`;
    return localizacaoMap[chave] || null;
}

// ✅ NOVA FUNÇÃO: Validar posição baseada no layout do SVG
function extrairLocalizacaoDoElemento(elemento) {

    // Método 1: Verificar atributo data-localizacao
    let localizacao = elemento.getAttribute('data-localizacao');
    if (localizacao && localizacao !== 'null') {
        return localizacao;
    }

    // Método 2: Extrair do texto adjacente
    const textElement = elemento.nextElementSibling;
    if (textElement && textElement.tagName === 'text') {
        const match = textElement.textContent.match(/\((.*?)\)/);
        if (match) {
            return match[1];
        }
    }

    // Método 3: Calcular baseado na posição no SVG
    const eixo = elemento.getAttribute('data-eixo');
    const posicao = elemento.getAttribute('data-posicao');

    if (eixo !== null && posicao !== null) {
        // ✅ VERIFICAÇÃO ESPECIAL PARA ESTEPES
        const eixoNum = parseInt(eixo);
        const posicaoNum = parseInt(posicao);

        if (eixoNum === -1 && posicaoNum === -1) {
            return 'E1';
        } else if (eixoNum === -2 && posicaoNum === -2) {
            return 'E2';
        } else if (eixoNum >= 0 && posicaoNum >= 0) {
            // Para posições de eixo normais
            const localizacaoCalculada = getLocalizacao(eixo, posicao);
            return localizacaoCalculada;
        }
    }

    console.error('❌ Não foi possível extrair localização do elemento');
    return null;
}

// ✅ FUNÇÃO PARA VALIDAR E LIMPAR CONFLITOS NOS DADOS ANTES DO ENVIO
function validarELimparConflitosLocalizacao() {
    // Limpar conflitos em formattedData
    if (formattedData?.pneusAplicadosFormatados) {
        const pneusPorLocalizacao = new Map();
        const pneusLimpos = [];

        formattedData.pneusAplicadosFormatados.forEach(pneu => {
            const localizacao = pneu.localizacao;

            if (pneusPorLocalizacao.has(localizacao)) {
                console.warn(`⚠️ Conflito removido em formattedData: pneu ${pneu.id_pneu} duplicado na localização ${localizacao}`);
                // Manter apenas o último (mais recente)
                const pneuAnterior = pneusPorLocalizacao.get(localizacao);
                const indexAnterior = pneusLimpos.findIndex(p => p === pneuAnterior);
                if (indexAnterior !== -1) {
                    pneusLimpos.splice(indexAnterior, 1);
                }
            }

            pneusPorLocalizacao.set(localizacao, pneu);
            pneusLimpos.push(pneu);
        });

        formattedData.pneusAplicadosFormatados = pneusLimpos;
    }
}

function coletarDadosParaEnvio() {

    // ✅ VALIDAR E LIMPAR CONFLITOS ANTES DE COLETAR
    validarELimparConflitosLocalizacao();

    const idOrdemServico = document.querySelector('[name="id_ordem_servico"]')?.value;
    const idVeiculo = document.querySelector('[name="select_id"]')?.value;

    if (!idOrdemServico) {
        console.warn('⚠️ Nenhuma ordem de serviço selecionada');
        return null;
    }

    if (!idVeiculo) {
        console.warn('⚠️ Nenhum veículo vinculado à ordem de serviço');
        return null;
    }

    const dadosVeiculo = {
        id_ordem_servico: idOrdemServico,
        id_veiculo: idVeiculo
    };

    // ✅ CORREÇÃO: Coletar pneus removidos primeiro
    const pneusRemovidos = [];
    const idsRemovidos = new Set(); // Para filtrar da lista de aplicados

    document.querySelectorAll('.espaco-vazio').forEach(espaco => {
        const idPneu = espaco.getAttribute('data-id');
        const localizacao = espaco.getAttribute('data-localizacao');
        const destino = espaco.getAttribute('data-destino');
        const kmRemovido = espaco.getAttribute('data-kmRemovido');
        const sulcoRemovido = espaco.getAttribute('data-sulcoRemovido');

        if (idPneu && idPneu !== 'null' && localizacao && destino) {
            pneusRemovidos.push({
                id_pneu: idPneu,
                status: destino,
                localizacao: localizacao,
                km_removido: kmRemovido,
                sulco_removido: sulcoRemovido
            });

            // ✅ IMPORTANTE: Marcar como removido para filtrar depois
            idsRemovidos.add(idPneu + '_' + localizacao);
        }
    });

    // ✅ CORREÇÃO: Coletar apenas pneus realmente aplicados (não removidos)
    let pneusAplicados = [];
    let fonte = 'NENHUMA';

    // Tentar múltiplas fontes para dados aplicados
    if (window.formattedData?.pneusAplicadosFormatados) {

        window.formattedData.pneusAplicadosFormatados.forEach(pneu => {
            const chaveRemocao = pneu.id_pneu + '_' + pneu.localizacao;

            if (!idsRemovidos.has(chaveRemocao)) {
                pneusAplicados.push({
                    id_pneu: pneu.id_pneu,
                    localizacao: pneu.localizacao,
                    sulco_adicionado: pneu.suco_pneu || pneu.sulco_pneu_adicionado || null,
                    km_adicionado: pneu.km_adicionado || null // ✅ INCLUIR KM_ADICIONADO
                });
            }
        });
        fonte = 'formattedData';
    }
    else {
        const pneusDOM = document.querySelectorAll('.pneu[data-id]:not([data-id="null"])');

        pneusDOM.forEach(pneu => {
            const idPneu = pneu.getAttribute('data-id');
            const textElement = pneu.nextElementSibling;
            let localizacao = null;

            // ✅ MELHOR EXTRAÇÃO: Tentar múltiplas estratégias para obter a localização

            // 1. Verificar se o próprio elemento pneu tem a localização
            if (pneu.getAttribute('data-localizacao')) {
                localizacao = pneu.getAttribute('data-localizacao');
            }

            // 2. Tentar extrair do texto adjacente
            if (!localizacao && textElement && textElement.textContent) {
                const match = textElement.textContent.match(/\((.*?)\)/);
                if (match && match[1]) {
                    localizacao = match[1].trim();
                }
            }

            // 3. Verificar no elemento pai ou irmãos
            if (!localizacao) {
                const parent = pneu.closest('[data-localizacao]');
                if (parent) {
                    localizacao = parent.getAttribute('data-localizacao');
                }
            }

            // 4. Último recurso: tentar extrair de classes ou IDs
            if (!localizacao) {
                const classes = pneu.className.split(' ');
                for (let cls of classes) {
                    // Procurar por padrões como 'pos-E2', 'loc-1D', etc.
                    const match = cls.match(/(?:pos|loc|position)-(.+)/i);
                    if (match) {
                        localizacao = match[1];
                        break;
                    }
                }
            }

            // ✅ FALLBACK SEGURO: Se ainda não encontrou, usar posição baseada na estrutura
            if (!localizacao) {
                // Tentar encontrar baseado na posição do SVG
                const svgParent = pneu.closest('svg');
                if (svgParent) {
                    const posicao = svgParent.getAttribute('data-position') ||
                        svgParent.getAttribute('id') ||
                        'UNK'; // Unknown - apenas 3 chars
                    localizacao = posicao.length > 10 ? posicao.substring(0, 10) : posicao;
                } else {
                    localizacao = 'UNK'; // Unknown - seguro para o campo da DB
                }
            }

            // ✅ GARANTIR MÁXIMO DE 10 CARACTERES
            if (localizacao && localizacao.length > 10) {
                localizacao = localizacao.substring(0, 10);
            }

            const chaveRemocao = idPneu + '_' + localizacao;

            if (idPneu && idPneu !== 'null' && !idsRemovidos.has(chaveRemocao)) {
                // ✅ TENTAR OBTER KM_ADICIONADO E SULCO_ADICIONADO DO ELEMENTO
                let kmAdicionado = null;
                let sulcoAdicionado = null;

                // Primeiro, tentar pelo dataset padrão
                if (pneu.dataset && pneu.dataset.kmAdicionado) {
                    kmAdicionado = parseFloat(pneu.dataset.kmAdicionado);
                }

                if (pneu.dataset && pneu.dataset.sulcoAdicionado) {
                    sulcoAdicionado = parseFloat(pneu.dataset.sulcoAdicionado);
                }

                // Se não encontrou, tentar pelos atributos data-*
                if (!kmAdicionado && pneu.getAttribute('data-km-adicionado')) {
                    kmAdicionado = parseFloat(pneu.getAttribute('data-km-adicionado'));
                }

                if (!sulcoAdicionado && pneu.getAttribute('data-sulco-adicionado')) {
                    sulcoAdicionado = parseFloat(pneu.getAttribute('data-sulco-adicionado'));
                }

                pneusAplicados.push({
                    id_pneu: parseInt(idPneu),
                    localizacao: localizacao,
                    sulco_adicionado: sulcoAdicionado, // ✅ AGORA COLETA O SULCO CORRETAMENTE
                    km_adicionado: kmAdicionado // ✅ KM_ADICIONADO SE DISPONÍVEL
                });
            }
        });
        fonte = 'DOM';
    }

    // Coletar pneus avulsos
    const pneusAvulsos = [];
    document.querySelectorAll('.pneu-avulso').forEach(pneu => {
        // ✅ INCLUIR PNEUS AVULSOS COM DADOS DE APLICAÇÃO
        if (pneu.dataset.kmAdicionado && pneu.dataset.sulcoAdicionado) {
            pneusAvulsos.push({
                id_pneu: parseInt(pneu.dataset.id),
                status: 'APLICADO',
                km_adicionado: parseFloat(pneu.dataset.kmAdicionado) || null,
                sulco_adicionado: parseFloat(pneu.dataset.sulcoAdicionado) || null,
                // ✅ ADICIONAR LOCALIZAÇÃO SE DISPONÍVEL
                localizacao: pneu.dataset.localizacao || null
            });
        }
    });

    // ✅ VALIDAÇÃO FINAL: Remover duplicatas por localização nos pneusAplicados
    if (pneusAplicados.length > 0) {
        const pneusPorLocalizacao = new Map();
        const pneusAplicadosLimpos = [];

        pneusAplicados.forEach(pneu => {
            if (pneusPorLocalizacao.has(pneu.localizacao)) {
                console.warn(`⚠️ DUPLICATA REMOVIDA: pneu ${pneu.id_pneu} conflitante na localização ${pneu.localizacao}`);
                // Manter apenas o último (substituir)
                const indexAnterior = pneusAplicadosLimpos.findIndex(p => p.localizacao === pneu.localizacao);
                if (indexAnterior !== -1) {
                    pneusAplicadosLimpos[indexAnterior] = pneu;
                }
            } else {
                pneusPorLocalizacao.set(pneu.localizacao, pneu);
                pneusAplicadosLimpos.push(pneu);
            }
        });

        pneusAplicados = pneusAplicadosLimpos;

        if (pneusAplicadosLimpos.length !== pneusAplicados.length) {
            console.debug(`✅ Conflitos removidos: ${pneusAplicados.length - pneusAplicadosLimpos.length} duplicatas eliminadas`);
        }
    }

    return { dadosVeiculo, pneusAplicados, pneusRemovidos, pneusAvulsos };
}

function enviarDadosParaBackend() {
    if (!dadosArray) {
        console.error('❌ Nenhum dado para enviar');
        alert('Nenhum dado para enviar. Selecione um veículo primeiro.');
        return;
    }

    // ✅ DEBUG: Verificar estado dos pneus antes da validação (colapsado)
    console.groupCollapsed('🔍 DEBUG - Estado dos pneus antes da validação:');
    console.debug('📊 Pneus no smart-select:', document.querySelector('[name="id_pneu"]'));
    console.debug('🔧 Pneus avulsos pendentes:', document.querySelectorAll('.pneu-avulso:not(.aplicado)'));
    console.debug('⏳ Pneus em processamento:', Array.from(pneusEmProcessamento));
    console.debug('✅ Pneus aplicados:', document.querySelectorAll('.pneu[data-id]:not([data-id="null"]):not([data-id=""]):not(.espaco-vazio)'));

    const statusDebug = verificarPneusPendentes();
    console.debug('📋 Status retornado:', statusDebug);
    console.groupEnd();

    // ✅ NOVA VALIDAÇÃO: Verificar se todos os pneus da requisição estão aplicados
    const ordemServicoId = document.querySelector('[name="id_ordem_servico"]')?.value;
    if (ordemServicoId) {
        console.debug('🔍 Verificando requisição de pneus para OS:', ordemServicoId);

        // Mostrar loading enquanto valida
        const loadingMessage = 'Verificando requisição de pneus...';
        console.debug('⏳ ' + loadingMessage);

        // A validação será feita no backend, mas vamos informar ao usuário
        const confirmarValidacao = confirm(
            'ATENÇÃO - VALIDAÇÃO DE REQUISIÇÃO\n\n' +
            'O sistema irá verificar se todos os pneus da requisição desta ordem de serviço foram aplicados.\n\n' +
            'Se algum pneu da requisição ainda não estiver aplicado, a movimentação será bloqueada.\n\n' +
            'Deseja continuar com a validação?'
        );

        if (!confirmarValidacao) {
            console.debug('📄 Salvamento cancelado pelo usuário - validação de requisição');
            return;
        }
    }

    // ✅ VERIFICAR PNEUS PENDENTES ANTES DE ENVIAR
    const validacao = validarTodosPneusAplicados();

    console.debug('🎯 Resultado da validação:', validacao);

    if (!validacao.valido) {
        // Exibir mensagem de confirmação se houver pneus pendentes
        console.warn('⚠️ Exibindo confirmação de pneus pendentes para usuário');
        const confirmar = confirm(validacao.mensagem);
        if (!confirmar) {
            console.debug('📄 Salvamento cancelado pelo usuário - pneus pendentes');
            return;
        } else {
            console.warn('⚠️ Usuário confirmou salvamento mesmo com pneus pendentes');
        }
    } else {
        console.debug('✅ Validação de pneus pendentes: ' + validacao.mensagem);
    }

    // Atualizar dados antes de enviar
    dadosArray = coletarDadosParaEnvio();

    // ✅ ADICIONAR FLAG PARA SALVAMENTO MANUAL
    const dadosComFlag = {
        ...dadosArray,
        auto_save: false  // ✅ CRUCIAL: Marca como salvamento manual
    };

    console.debug('🚀 Enviando dados MANUAL para backend:', dadosComFlag);

    // ✅ OBTER TOKEN CSRF DINÂMICAMENTE
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('input[name="_token"]')?.value;

    if (!csrfToken) {
        console.error('❌ Token CSRF não encontrado');
        alert('Erro: Token de segurança não encontrado. Recarregue a página.');
        return;
    }

    console.debug(' Token CSRF:', csrfToken.substring(0, 10) + '...');

    fetch('/admin/movimentacaopneus/salvar-dados', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(dadosComFlag),  // ✅ Usar dadosComFlag
    })
        .then(async response => {
            console.debug('📡 Status da resposta:', response.status, response.statusText);

            // Parse do JSON primeiro
            const data = await response.json();

            if (!response.ok) {
                if (response.status === 419) {
                    throw new Error('Token de segurança expirado. Por favor, recarregue a página e tente novamente.');
                }
                console.debug('🔍 Dados de erro recebidos do backend:', data);
                const errorMessage = data.message || data.error || `Erro HTTP ${response.status}: ${response.statusText}`;
                throw new Error(errorMessage);
            }

            return data;
        })
        .then(data => {
            console.debug('📦 Resposta do servidor:', data);

            if (data.success) {
                alert('✅ MOVIMENTAÇÃO FINALIZADA COM SUCESSO!\n\nTodos os dados foram salvos corretamente no sistema.');
                // Limpar formulário se necessário
                // window.location.reload();
            } else {
                // ✅ Melhor formatação de erro para requisição de pneus
                const errorMessage = data.message || data.error || 'Erro desconhecido';
                console.error('❌ Erro do servidor:', errorMessage);

                // Sempre mostrar a mensagem completa do servidor
                alert(errorMessage);
            }
        })
        .catch(error => {
            console.error('❌ Erro ao enviar dados:', error);

            const errorMessage = error.message;

            // ✅ Sempre mostrar a mensagem de erro completa
            alert(errorMessage);
        });
}

function validarPneuPrimeiroEixo(pneuElement, localizacaoDestino) {
    // Verificar se a localização é do primeiro eixo
    if (!localizacaoDestino.startsWith('1')) {
        return {
            valido: true
        }; // Não é primeiro eixo, pode aplicar
    }

    // Verificar se o pneu tem tipo definido
    const tipoPneu = pneuElement.dataset.tipo_pneu;
    if (!tipoPneu) {
        return {
            valido: true
        }; // Sem informação de tipo, permitir (assumir que é novo)
    }

    // Verificar se é recapado/vulcanizado
    const tipoLower = tipoPneu.toLowerCase();
    const isRecapado = tipoLower.includes('vulcanizado') ||
        tipoLower.includes('recapado') ||
        tipoLower.includes('recapagem');

    if (isRecapado) {
        // ✅ NOVA REGRA: Permitir pneus recapados/vulcanizados no primeiro eixo
        // se o veículo NÃO possui tração
        if (!veiculoPossuiTracao) {
            console.debug(`✅ APLICAÇÃO PERMITIDA: Pneu ${pneuElement.dataset.id} (${tipoPneu}) pode ser aplicado no primeiro eixo porque o veículo NÃO possui tração`);
            return {
                valido: true,
                mensagem: `✅ Aplicação permitida: veículo sem tração`
            };
        }

        // Bloquear apenas se o veículo possui tração
        return {
            valido: false,
            mensagem: `🚫 OPERAÇÃO BLOQUEADA POR SEGURANÇA!\n\n` +
                `O pneu ${pneuElement.dataset.id} é do tipo "${tipoPneu}" e não pode ser aplicado no primeiro eixo (posição ${localizacaoDestino}) de veículos com tração.\n\n` +
                `NORMAS DE SEGURANÇA:\n` +
                `• Pneus vulcanizados/recapados são PROIBIDOS no primeiro eixo de veículos tracionados\n` +
                `• O primeiro eixo é responsável pela direção do veículo\n` +
                `• Esta é uma norma de segurança viária obrigatória\n\n` +
                `INFORMAÇÃO DO VEÍCULO:\n` +
                `• Este veículo possui tração: ${veiculoPossuiTracao ? 'SIM' : 'NÃO'}\n\n` +
                `SOLUÇÕES:\n` +
                `• Use apenas pneus NOVOS no primeiro eixo de veículos tracionados\n` +
                `• Aplique este pneu em outros eixos (2º, 3º, etc.)`
        };
    }

    return {
        valido: true
    };
}

function validarPosicaoOcupada(pneuAplicado) {
    // Verificar se o elemento clicado é um pneu aplicado (não um espaço vazio)
    const isPneuAplicado = pneuAplicado.classList.contains('pneu') &&
        pneuAplicado.getAttribute('data-id') &&
        pneuAplicado.getAttribute('data-id') !== 'null';

    return {
        ocupada: isPneuAplicado,
        mensagem: isPneuAplicado ?
            `🚫 POSIÇÃO OCUPADA!\n\n` +
            `Não é possível substituir diretamente um pneu aplicado.\n\n` +
            `PROCEDIMENTO CORRETO:\n` +
            `1️⃣ Primeiro REMOVA o pneu atual:\n` +
            `   • Clique no pneu para selecioná-lo\n` +
            `   • Clique em uma das áreas (Borracharia)\n` +
            `   • Preencha os dados de remoção\n\n` +
            `2️⃣ Depois APLIQUE o novo pneu:\n` +
            `   • Selecione o pneu avulso\n` +
            `   • Clique no espaço vazio criado\n\n` +
            `⚠️ Esta é uma medida de segurança para garantir o controle adequado dos pneus!` : null
    };
}

function marcarEspacosVaziosDisponiveis() {
    // Adicionar classe CSS para destacar espaços vazios quando pneu avulso está selecionado
    const style = document.createElement('style');
    style.textContent = `
        .espaco-vazio-disponivel {
            stroke: #10B981 !important;
            stroke-width: 3 !important;
            stroke-dasharray: 10,5 !important;
            animation: pulse-border 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse-border {
            0%, 100% { stroke-opacity: 1; }
            50% { stroke-opacity: 0.4; }
        }
        
        .pneu-ocupado-feedback {
            filter: drop-shadow(0 0 5px red);
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-2px); }
            75% { transform: translateX(2px); }
        }
    `;
    document.head.appendChild(style);
}





async function corrigirPneuSemLocalizacao(pneuId, novaLocalizacao) {

    const dadosCorrecao = {
        dadosVeiculo: {
            id_ordem_servico: document.querySelector('[name="id_ordem_servico"]')?.value,
            id_veiculo: document.querySelector('[name="select_id"]')?.value,
            km_atual: document.querySelector('[name="km_atual"]')?.value || '0'
        },
        pneusAplicados: [
            {
                id_pneu: pneuId,
                localizacao: novaLocalizacao
            }
        ],
        pneusRemovidos: [],
        pneusAvulsos: [],
        auto_save: false,
        correcao_localizacao: true
    };

    try {
        const response = await fetch('/admin/movimentacaopneus/salvar-dados', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(dadosCorrecao)
        });

        const result = await response.json();

        if (result.success) {

            if (typeof showNotification === 'function') {
                showNotification(`Localização do pneu ${pneuId} corrigida!`, 'success');
            }

            return true;
        } else {
            console.error('❌ Erro ao corrigir localização:', result.error);
            return false;
        }

    } catch (error) {
        console.error('❌ Erro na correção:', error);
        return false;
    }
}

// ✅ VARIÁVEL GLOBAL PARA RASTREAR PNEUS REMOVIDOS
let pneusRemovidosDasOpcoes = new Set();

// ✅ FUNÇÃO PARA LIMPAR CACHE DE PNEUS REMOVIDOS
function limparCachePneusRemovidos() {
    console.debug(`🧹 Limpando cache de pneus removidos (${pneusRemovidosDasOpcoes.size} pneus)`);
    pneusRemovidosDasOpcoes.clear();
}

// ✅ FUNÇÃO PARA RESETAR O ESTADO DE PNEUS QUANDO UMA NOVA ORDEM DE SERVIÇO É SELECIONADA
function resetEstadoPneusParaNovaOS() {
    console.debug('🔁 Resetando estado de pneus para nova Ordem de Serviço');

    // Limpar variáveis locais de seleção
    selectedPneu = null;
    selectedPneu1 = null;
    selectedPneu2 = null;
    pneuSelecionadoParaTroca = null;

    // Limpar área de pneus avulsos (não manter pneus de OS anterior)
    const areaPneusAvulsos = document.getElementById('areaPneusAvulsos');
    if (areaPneusAvulsos) {
        areaPneusAvulsos.innerHTML = '';
    }

    // Limpar cache de pneus removidos para que opções sejam recalculadas
    if (typeof limparCachePneusRemovidos === 'function') {
        limparCachePneusRemovidos();
    } else if (typeof pneusRemovidosDasOpcoes !== 'undefined' && pneusRemovidosDasOpcoes instanceof Set) {
        pneusRemovidosDasOpcoes.clear();
    }

    // Limpar flags visuais
    document.querySelectorAll('.pneu').forEach(p => {
        p.classList.remove('pronto-aplicacao');
        p.classList.remove('pneu-aplicado');
        if (p.tagName.toLowerCase() === 'img') {
            // Restaurar atributo data-original-svg se existir
            const original = p.getAttribute('data-original-svg');
            if (original) p.src = original;
        } else {
            const original = p.getAttribute('data-original-svg');
            if (original) p.setAttribute('href', original);
        }
    });

    // Atualizar estado global de dados
    dadosArray = null;
    formattedData = null;

    // Disparar evento customizado para integração com outros scripts
    try {
        const evento = new CustomEvent('movimentacao:osChanged', { detail: { timestamp: Date.now() } });
        window.dispatchEvent(evento);
        console.debug('📣 Evento disparado: movimentacao:osChanged');
    } catch (e) {
        console.warn('⚠️ Não foi possível disparar evento movimentacao:osChanged:', e);
    }
}

// ✅ FUNÇÃO PARA RESTAURAR PNEU NO CACHE SE NECESSÁRIO
function permitirPneuNasOpcoes(pneuId) {
    console.debug(`🔄 Permitindo pneu ${pneuId} nas opções novamente`);
    pneusRemovidosDasOpcoes.delete(String(pneuId));
}

// ✅ FUNÇÃO PARA REMOVER PNEU DAS OPÇÕES APÓS APLICAÇÃO
function removerPneuDasOpcoes(pneuId) {
    console.debug(`🗑️ Removendo pneu ${pneuId} das opções do select`);

    try {
        // ✅ PROTEÇÃO: Evitar dupla remoção
        if (pneusRemovidosDasOpcoes.has(String(pneuId))) {
            console.debug(`🔄 Pneu ${pneuId} já foi removido anteriormente, ignorando...`);
            return true;
        }

        let removidoComSucesso = false;

        // Remover usando a função do Smart-Select se disponível
        if (typeof window.removeSmartSelectOption === 'function') {
            const success = window.removeSmartSelectOption('id_pneu', pneuId);
            if (success) {
                console.debug(`✅ Pneu ${pneuId} removido das opções via Smart-Select`);
                removidoComSucesso = true;
            }
        }

        // ✅ BUSCA MAIS ABRANGENTE: Verificar várias estruturas possíveis
        const selectPneu = document.querySelector('[name="id_pneu"]');
        if (selectPneu && !removidoComSucesso) {
            // Para select tradicional
            const optionToRemove = selectPneu.querySelector(`option[value="${pneuId}"]`);
            if (optionToRemove) {
                optionToRemove.remove();
                console.debug(`✅ Pneu ${pneuId} removido das opções (select tradicional)`);
                removidoComSucesso = true;
            }

            // Para Smart-Select via Alpine.js
            if (selectPneu._x_dataStack && selectPneu._x_dataStack[0] && !removidoComSucesso) {
                const alpineData = selectPneu._x_dataStack[0];
                console.debug(`🔍 Dados do Alpine encontrados:`, {
                    hasOptions: !!(alpineData.options),
                    hasItems: !!(alpineData.items),
                    hasData: !!(alpineData.data),
                    optionsLength: alpineData.options?.length,
                    itemsLength: alpineData.items?.length
                });

                // Tentar remover de diferentes estruturas possíveis do Alpine
                const estruturas = ['options', 'items', 'data', 'selectedOptions', 'availableOptions'];

                for (let estrutura of estruturas) {
                    if (alpineData[estrutura] && Array.isArray(alpineData[estrutura])) {
                        const index = alpineData[estrutura].findIndex(item => {
                            const itemValue = item.value || item.id || item;
                            return String(itemValue) === String(pneuId);
                        });

                        if (index !== -1) {
                            alpineData[estrutura].splice(index, 1);
                            console.debug(`✅ Pneu ${pneuId} removido das opções (Alpine ${estrutura})`);
                            removidoComSucesso = true;
                            break;
                        }
                    }
                }
            }
        }

        // ✅ MÉTODO MAIS ABRANGENTE: buscar em todas as estruturas globais
        if (!removidoComSucesso) {
            const estruturasGlobais = [
                window.smartSelectData?.['id_pneu'],
                window.pneusDisponiveis,
                window.optionsPneus,
                window.selectData?.id_pneu
            ];

            estruturasGlobais.forEach((estrutura, indice) => {
                if (estrutura && Array.isArray(estrutura) && !removidoComSucesso) {
                    const index = estrutura.findIndex(item => {
                        const itemValue = item.value || item.id || item;
                        return String(itemValue) === String(pneuId);
                    });

                    if (index !== -1) {
                        estrutura.splice(index, 1);
                        console.debug(`✅ Pneu ${pneuId} removido da estrutura global ${indice}`);
                        removidoComSucesso = true;

                        // Atualizar o select se possível
                        if (typeof window.updateSmartSelectOptions === 'function') {
                            window.updateSmartSelectOptions('id_pneu', estrutura, false);
                        }
                    }
                }
            });
        }

        // ✅ REGISTRAR REMOÇÃO PARA EVITAR DUPLICATAS
        if (removidoComSucesso) {
            pneusRemovidosDasOpcoes.add(String(pneuId));
            console.debug(`📝 Pneu ${pneuId} marcado como removido das opções`);
            return true;
        } else {
            console.warn(`⚠️ Pneu ${pneuId} não encontrado em nenhuma estrutura de opções`);
            // ✅ Mesmo que não encontrado, marcar como "removido" para evitar tentativas repetidas
            pneusRemovidosDasOpcoes.add(String(pneuId));
            return false;
        }

    } catch (error) {
        console.error(`❌ Erro ao remover pneu ${pneuId} das opções:`, error);
        return false;
    }
}

// ✅ FUNÇÃO PARA RESTAURAR PNEU NAS OPÇÕES (caso cancelado)
function restaurarPneuNasOpcoes(pneuId, pneuLabel = null, pneuStatus = 'DEPOSITO', origem = 'deposito') {
    console.debug(`🔄 Restaurando pneu ${pneuId} nas opções do select`);

    try {
        // ✅ REMOVER DO CACHE DE PNEUS REMOVIDOS
        permitirPneuNasOpcoes(pneuId);

        // Se não tem label, criar um padrão
        if (!pneuLabel) {
            pneuLabel = `${pneuId} - DISPONIVEL EM DEPÓSITO`;
        }

        const novoPneu = {
            value: pneuId,
            label: pneuLabel,
            status: pneuStatus,
            origem: origem
        };

        // Restaurar usando a função do Smart-Select se disponível
        if (typeof window.addSmartSelectOption === 'function') {
            const success = window.addSmartSelectOption('id_pneu', novoPneu);
            if (success) {
                console.debug(`✅ Pneu ${pneuId} restaurado nas opções via Smart-Select`);
                return;
            }
        }

        // Método alternativo: adicionar diretamente ao select
        const selectPneu = document.querySelector('[name="id_pneu"]');
        if (selectPneu) {
            // Para select tradicional
            const newOption = document.createElement('option');
            newOption.value = pneuId;
            newOption.textContent = pneuLabel;
            selectPneu.appendChild(newOption);
            console.debug(`✅ Pneu ${pneuId} restaurado nas opções (select tradicional)`);
            return;
        }

        // Restaurar nos dados globais se disponível
        if (window.smartSelectData && window.smartSelectData['id_pneu']) {
            window.smartSelectData['id_pneu'].push(novoPneu);

            // Atualizar o select se possível
            if (typeof window.updateSmartSelectOptions === 'function') {
                window.updateSmartSelectOptions('id_pneu', window.smartSelectData['id_pneu'], false);
            }
            console.debug(`✅ Pneu ${pneuId} restaurado nos dados globais do Smart-Select`);
            return;
        }

        console.warn(`⚠️ Não foi possível restaurar o pneu ${pneuId} nas opções`);

    } catch (error) {
        console.error(`❌ Erro ao restaurar pneu ${pneuId} nas opções:`, error);
    }
}

// ✅ FUNÇÃO PARA CANCELAR PNEU AVULSO E RESTAURAR NAS OPÇÕES
function cancelarPneuAvulso(pneuAvulsoElement) {
    if (!pneuAvulsoElement) return;

    const pneuId = pneuAvulsoElement.dataset.id;
    const container = pneuAvulsoElement.closest('.pneu-avulso-container');

    if (container && pneuId) {
        // Restaurar pneu nas opções antes de remover
        restaurarPneuNasOpcoes(pneuId);

        // Remover container
        container.remove();

        // Limpar seleção se for o pneu selecionado
        if (pneuSelecionadoParaTroca === pneuAvulsoElement) {
            pneuSelecionadoParaTroca = null;
        }

        console.debug(`✅ Pneu avulso ${pneuId} cancelado e restaurado nas opções`);
    }
}

// ✅ FUNÇÃO PARA VALIDAR SE TODAS AS LOCALIZAÇÕES OBRIGATÓRIAS ESTÃO PREENCHIDAS
async function validarLocalizacoesObrigatorias(idVeiculo) {
    if (!idVeiculo) {
        console.error('❌ ID do veículo não fornecido para validação');
        return {
            valido: false,
            mensagem: 'ID do veículo não identificado'
        };
    }

    try {
        // Fazer requisição para obter as localizações obrigatórias do veículo
        const response = await fetch(`/admin/movimentacaopneus/localizacoes-obrigatorias/${idVeiculo}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success || !Array.isArray(data.localizacoes)) {
            throw new Error(data.message || 'Resposta inválida do servidor');
        }

        const localizacoesObrigatorias = data.localizacoes;
        console.debug(`📍 Localizações obrigatórias do veículo ${idVeiculo}:`, localizacoesObrigatorias);

        // ✅ CORREÇÃO: Obter apenas as posições que DEVERIAM ter pneus (não espaços vazios)
        const localizacoesPreenchidas = [];

        // Buscar pneus que estão realmente aplicados (não espaços vazios)
        const pneusAplicados = document.querySelectorAll('.pneu[data-id]:not([data-id="null"]):not([data-id=""]):not(.espaco-vazio)');

        pneusAplicados.forEach(pneu => {
            const localizacao = pneu.dataset.localizacao || pneu.getAttribute('data-localizacao');
            if (localizacao && localizacao.trim() !== '' && localizacao !== 'null') {
                localizacoesPreenchidas.push(localizacao.trim());
            }
        });

        // ✅ BUSCAR TAMBÉM EM ELEMENTOS COM CLASSE ESPECÍFICA DE PNEUS APLICADOS
        const pneusComClasse = document.querySelectorAll('.pneu-aplicado');
        pneusComClasse.forEach(pneu => {
            const localizacao = pneu.dataset.localizacao || pneu.getAttribute('data-localizacao');
            if (localizacao && localizacao.trim() !== '' && localizacao !== 'null') {
                localizacoesPreenchidas.push(localizacao.trim());
            }
        });

        console.debug(`🔍 Localizações atualmente preenchidas:`, localizacoesPreenchidas);

        // ✅ CORREÇÃO: Verificar quais localizações obrigatórias estão realmente vazias
        // Só considera vazio se a localização deveria ter pneu MAS está como espaço vazio
        const localizacoesVazias = [];

        localizacoesObrigatorias.forEach(loc => {
            const localizacao = loc.localizacao;

            // ✅ EXCLUIR ESTEPES (E2) DA VALIDAÇÃO - CLIENTE NÃO QUER VALIDAR
            if (localizacao === 'E2') {
                console.debug(`⏭️ Pulando validação do estepe: ${localizacao}`);
                return; // Pular validação dos estepes
            }

            // Verificar se esta localização está preenchida
            const estaPreenchida = localizacoesPreenchidas.includes(localizacao);

            if (!estaPreenchida) {
                // ✅ VERIFICAR SE EXISTE UM ESPAÇO VAZIO NESTA LOCALIZAÇÃO
                const espacoVazio = document.querySelector(`.espaco-vazio[data-localizacao="${localizacao}"]`);

                if (espacoVazio) {
                    // Se existe espaço vazio, significa que DEVERIA ter pneu mas está vazio
                    localizacoesVazias.push(loc);
                }
            }
        });

        if (localizacoesVazias.length > 0) {
            const listaVazias = localizacoesVazias.map(loc => loc.localizacao).join(', ');
            return {
                valido: false,
                mensagem: `As seguintes localizações obrigatórias estão vazias: ${listaVazias}`,
                localizacoesVazias: localizacoesVazias
            };
        }

        return {
            valido: true,
            mensagem: 'Todas as localizações obrigatórias estão preenchidas'
        };

    } catch (error) {
        console.error('❌ Erro ao validar localizações obrigatórias:', error);
        return {
            valido: false,
            mensagem: `Erro ao verificar localizações: ${error.message}`
        };
    }
}

window.corrigirPneuSemLocalizacao = corrigirPneuSemLocalizacao;
window.validarConsistenciaLocalizacoes = validarConsistenciaLocalizacoes;
window.corrigirLocalizacoesInconsistentes = corrigirLocalizacoesInconsistentes;
window.validarLocalizacoesObrigatorias = validarLocalizacoesObrigatorias;

/**
 * 🧪 FUNÇÃO DE TESTE - Simula pneus pendentes para testar a validação
 * Execute no console: testarValidacaoPneusPendentes()
 */
function testarValidacaoPneusPendentes() {
    console.groupCollapsed('🧪 TESTE MANUAL - Simulando pneus pendentes');

    // Simular pneu disponível no select
    const selectPneu = document.querySelector('[name="id_pneu"]');
    if (selectPneu && selectPneu._x_dataStack?.[0]) {
        // Adicionar uma opção fake para simular pneu disponível
        const originalOptions = selectPneu._x_dataStack[0].options || [];
        selectPneu._x_dataStack[0].options = [
            ...originalOptions,
            { value: '999999', text: '🧪 PNEU DE TESTE - PENDENTE' }
        ];
        console.debug('✅ Pneu fake adicionado ao select');
    }

    // Executar a validação
    console.debug('🔍 Executando validarTodosPneusAplicados()...');
    const validacao = validarTodosPneusAplicados();

    console.debug('📊 Resultado da validação:', validacao);

    if (!validacao.valido) {
        console.debug('⚠️ Mostrando mensagem de confirmação...');
        const confirmar = confirm(validacao.mensagem);
        console.debug('📝 Usuário confirmou:', confirmar);
    } else {
        console.debug('✅ Validação passou - todos os pneus aplicados');
    }

    // Limpar o teste
    if (selectPneu && selectPneu._x_dataStack?.[0]) {
        selectPneu._x_dataStack[0].options = selectPneu._x_dataStack[0].options.filter(opt => opt.value !== '999999');
        console.debug('🧹 Pneu fake removido do select');
    }

    console.groupEnd();
}

// Disponibilizar globalmente para teste
window.testarValidacaoPneusPendentes = testarValidacaoPneusPendentes;
