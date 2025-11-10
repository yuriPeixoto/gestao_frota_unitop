// ==========================================
// SISTEMA DE AUTO-SAVE COM CONFIRMAÇÕES EM TEMPO REAL
// ==========================================

// Configurações do auto-save
const autoSaveConfig = {
    enabled: true,
    debounceTime: 1000, // 1 segundo de delay após última ação
    maxRetries: 3,
    retryDelay: 2000
};

// Estado do auto-save
let autoSaveState = {
    isProcessing: false,
    lastSaveTime: null,
    pendingChanges: false,
    currentOperation: null,
    retryCount: 0
};

// Debounce timer para evitar múltiplas chamadas
let autoSaveTimer = null;

// ==========================================
// SISTEMA DE NOTIFICAÇÕES
// ==========================================

function createNotificationSystem() {
    // Criar container de notificações se não existir
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 4000) {
    createNotificationSystem();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    // Estilos baseados no tipo
    const styles = {
        success: 'background: #10B981; color: white;',
        error: 'background: #EF4444; color: white;',
        warning: 'background: #F59E0B; color: white;',
        info: 'background: #3B82F6; color: white;',
        processing: 'background: #6B7280; color: white;'
    };

    notification.style.cssText = `
        ${styles[type]}
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-size: 14px;
        font-weight: 500;
        max-width: 300px;
        transform: translateX(100%);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    `;

    // Ícones para cada tipo
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️',
        processing: '⏳'
    };

    notification.innerHTML = `
        <span style="font-size: 16px;">${icons[type]}</span>
        <span>${message}</span>
    `;

    const container = document.getElementById('notification-container');
    container.appendChild(notification);

    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);

    // Auto-remover
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(notification);
        }, duration);
    }

    return notification;
}

function removeNotification(notification) {
    if (notification && notification.parentNode) {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

// ==========================================
// SISTEMA DE AUTO-SAVE
// ==========================================

function initAutoSaveSystem() {
    // Criar indicador visual de status
    createSaveStatusIndicator();

    // Configurar interceptadores para todas as operações
    setupAutoSaveInterceptors();
}

function createSaveStatusIndicator() {
    if (document.getElementById('save-status-indicator')) return;

    const indicator = document.createElement('div');
    indicator.id = 'save-status-indicator';
    indicator.style.cssText = `
        position: fixed;
        bottom: 275px;
        left: 279px;
        background: white;
        border: 2px solid #E5E7EB;
        border-radius: 50px;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        transition: all 0.3s ease;
    `;

    updateSaveStatus('saved');
    document.body.appendChild(indicator);
}

function updateSaveStatus(status, message = '') {
    const indicator = document.getElementById('save-status-indicator');
    if (!indicator) return;

    const statusConfig = {
        saved: {
            icon: '✅',
            text: 'Salvo no Banco',
            color: '#10B981',
            bg: '#ECFDF5'
        },
        saving: {
            icon: '💾',
            text: 'Salvando...',
            color: '#3B82F6',
            bg: '#EFF6FF'
        },
        error: {
            icon: '⚠️',
            text: 'Problema temporário',
            color: '#F59E0B',
            bg: '#FFFBEB'
        },
        pending: {
            icon: '⏳',
            text: 'Alterações pendentes',
            color: '#F59E0B',
            bg: '#FFFBEB'
        }
    };

    const config = statusConfig[status];
    if (!config) return;

    indicator.style.borderColor = config.color;
    indicator.style.backgroundColor = config.bg;
    indicator.innerHTML = `
        <span style="font-size: 14px;">${config.icon}</span>
        <span style="color: ${config.color};">${message || config.text}</span>
    `;

    // Adicionar animação para feedback visual
    indicator.style.transform = 'scale(1.1)';
    setTimeout(() => {
        indicator.style.transform = 'scale(1)';
    }, 200);
}

function setupAutoSaveInterceptors() {
    // Interceptar função de troca de pneus
    const originalTrocarPneus = window.trocarPneus;
    if (originalTrocarPneus) {
        window.trocarPneus = function (pneu1, pneu2) {

            const result = originalTrocarPneus.call(this, pneu1, pneu2);

            triggerAutoSave('troca_pneus', {
                pneu1_id: pneu1.getAttribute('data-id'),
                pneu2_id: pneu2.getAttribute('data-id'),
                localizacao_1: extrairLocalizacao(pneu1),
                localizacao_2: extrairLocalizacao(pneu2)
            });

            return result;
        };
    }

    // ✅ REMOÇÃO DO INTERCEPTADOR - O movimentacaopneu.js já chama triggerAutoSave corretamente
    // const originalMoverPneuParaDrop = window.moverPneuParaDrop;
    // if (originalMoverPneuParaDrop) {
    //     window.moverPneuParaDrop = function (zone, kmRemovido, sulcoRemovido) {
    //         // Interceptador removido - usando o triggerAutoSave do movimentacaopneu.js
    //         return originalMoverPneuParaDrop.call(this, zone, kmRemovido, sulcoRemovido);
    //     };
    // }
}

function extrairLocalizacao(elemento) {
    if (!elemento) return null;

    const textElement = elemento.nextElementSibling;
    if (textElement && textElement.textContent) {
        const match = textElement.textContent.match(/\((.*?)\)/);
        return match ? match[1] : null;
    }

    return null;
}

function triggerAutoSave(operationType, operationData = {}) {
    if (!autoSaveConfig.enabled) {
        return;
    }

    console.log('🎯 triggerAutoSave chamado:', {
        operationType: operationType,
        operationData: operationData
    });

    console.log('📋 Estado do autoSaveConfig:', autoSaveConfig);    // Limpar timer anterior
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }

    // Marcar como pendente
    autoSaveState.pendingChanges = true;
    autoSaveState.currentOperation = {
        type: operationType,
        data: operationData,
        timestamp: Date.now()
    };

    updateSaveStatus('pending');

    // ✅ IMPORTANTE: Coletar dados atualizados SEMPRE
    const dadosAtuais = coletarDadosParaEnvio();

    if (dadosAtuais) {
        // Adicionar dados da operação aos dados coletados
        autoSaveState.dadosCompletos = {
            ...dadosAtuais,
            operacao: autoSaveState.currentOperation
        };
    } else {
        console.warn('⚠️ Não foi possível coletar dados atuais');
    }

    // Debounce: esperar um tempo antes de salvar
    autoSaveTimer = setTimeout(() => {
        performAutoSave();
    }, autoSaveConfig.debounceTime);

}

async function performAutoSave() {
    console.log('🚀 performAutoSave INICIADO');

    if (autoSaveState.isProcessing) {
        console.log('⏸️ performAutoSave já está processando, saindo...');
        return;
    }

    console.log('💾 Configuração atual:', {
        enabled: autoSaveConfig.enabled,
        debounceTime: autoSaveConfig.debounceTime
    });

    autoSaveState.isProcessing = true;
    updateSaveStatus('saving');

    const processingNotification = showNotification(
        `Salvando ${getOperationDisplayName(autoSaveState.currentOperation?.type)}...`,
        'processing',
        0
    );

    try {

        // ✅ USAR DADOS PRÉ-COLETADOS OU COLETAR NOVAMENTE
        let dadosParaEnvio = autoSaveState.dadosCompletos || coletarDadosParaEnvio();

        if (!dadosParaEnvio) {
            throw new Error('Nenhum dado para salvar');
        }

        // ✅ VALIDAÇÃO ESPECÍFICA: Verificar localizações dos pneus aplicados
        if (dadosParaEnvio.pneusAplicados && Array.isArray(dadosParaEnvio.pneusAplicados)) {
            dadosParaEnvio.pneusAplicados.forEach((pneu, index) => {
                if (!pneu.localizacao || pneu.localizacao === 'UNK' || pneu.localizacao === 'DESCONHEC') {
                    console.warn(`⚠️ Pneu ${pneu.id_pneu} com localização inválida: "${pneu.localizacao}"`);

                    // ✅ TENTAR OBTER LOCALIZAÇÃO DO CONTEXTO DA OPERAÇÃO
                    if (autoSaveState.currentOperation?.metadata?.localizacao) {
                        pneu.localizacao = autoSaveState.currentOperation.metadata.localizacao;
                        console.log(`🔧 Localização corrigida para pneu ${pneu.id_pneu}: "${pneu.localizacao}"`);
                    }
                }

                // ✅ GARANTIR MÁXIMO 10 CARACTERES
                if (pneu.localizacao && pneu.localizacao.length > 10) {
                    pneu.localizacao = pneu.localizacao.substring(0, 10);
                }
            });
        }

        // Garantir que a operação esteja incluída
        const dadosComMetadata = {
            ...dadosParaEnvio,
            operacao: autoSaveState.currentOperation,
            timestamp: Date.now(),
            auto_save: true
        };

        console.log('📡 ENVIANDO REQUISIÇÃO para /admin/movimentacaopneus/salvar-dados');
        console.log('📋 Dados completos a enviar:', dadosComMetadata);
        console.log('🔍 ESPECÍFICO - Operação:', dadosComMetadata.operacao);
        console.log('🔎 ESPECÍFICO - Dados da Operação:', dadosComMetadata.operacao?.data);
        console.log('🚨 CAMPO destinacao_solicitada:', dadosComMetadata.operacao?.data?.destinacao_solicitada);

        // Enviar para o backend
        const response = await fetch('/admin/movimentacaopneus/salvar-dados', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(dadosComMetadata)
        });

        console.log('📡 RESPOSTA recebida:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok
        });

        let data;
        try {
            data = await response.json();
        } catch (parseError) {
            console.error('❌ Erro ao fazer parse da resposta JSON:', parseError);
            console.error('📝 Resposta do servidor (texto):', await response.text());
            throw new Error(`Erro no servidor: ${response.status} - Resposta inválida`);
        }

        if (!response.ok || !data.success) {
            // ✅ DETALHES MELHORADOS PARA ERROS
            const errorMessage = data.error || `Erro HTTP: ${response.status}`;
            const errorDetails = {
                status: response.status,
                statusText: response.statusText,
                error: data.error,
                detalhes: data.detalhes,
                operacao: autoSaveState.currentOperation?.type,
                dadosEnviados: Object.keys(dadosComMetadata)
            };

            console.error('❌ ERRO DETALHADO NO AUTO-SAVE:', errorDetails);

            // Erro específico para 500
            if (response.status === 500) {
                throw new Error(`Erro interno do servidor (500): ${errorMessage}. Verifique os logs do Laravel para mais detalhes.`);
            }

            throw new Error(errorMessage);
        }

        // Sucesso
        autoSaveState.lastSaveTime = Date.now();
        autoSaveState.pendingChanges = false;
        autoSaveState.retryCount = 0;
        autoSaveState.dadosCompletos = null; // Limpar dados usados

        updateSaveStatus('saved');
        removeNotification(processingNotification);
        removeButtonHighlight(); // ✅ Remover qualquer destaque do botão

        // ✅ MENSAGEM DE SUCESSO MAIS ESPECÍFICA E AMIGÁVEL
        const operationName = getOperationDisplayName(autoSaveState.currentOperation?.type);
        showNotification(
            `✅ ${operationName.charAt(0).toUpperCase() + operationName.slice(1)} realizada com sucesso! Dados salvos automaticamente.`,
            'success',
            3000 // Menos tempo para não incomodar o usuário
        );

    } catch (error) {
        console.error('❌ Erro no auto-save:', error);
        removeNotification(processingNotification);

        // Tentar novamente se não excedeu o limite
        if (autoSaveState.retryCount < autoSaveConfig.maxRetries) {
            autoSaveState.retryCount++;
            updateSaveStatus('error', `Tentativa ${autoSaveState.retryCount}/${autoSaveConfig.maxRetries}`);

            // ✅ MENSAGEM MAIS TRANQUILIZADORA DURANTE TENTATIVAS
            const operationName = getOperationDisplayName(autoSaveState.currentOperation?.type);
            showNotification(
                `Tentando salvar a ${operationName} novamente... (${autoSaveState.retryCount}/${autoSaveConfig.maxRetries})`,
                'info',
                3000
            );

            setTimeout(() => {
                autoSaveState.isProcessing = false;
                performAutoSave();
            }, autoSaveConfig.retryDelay);

            return;
        } else {
            updateSaveStatus('error', 'Erro temporário');

            // ✅ MENSAGEM MAIS AMIGÁVEL: Explicar o que aconteceu e o que fazer
            const operationName = getOperationDisplayName(autoSaveState.currentOperation?.type);
            showNotification(
                `Houve um problema ao salvar a ${operationName}. O sistema tentará salvar novamente em alguns segundos, mas suas alterações estão seguras na interface.`,
                'warning',
                8000
            );

            // ✅ TENTAR NOVAMENTE AUTOMATICAMENTE APÓS UM TEMPO
            console.log('🔄 Tentando salvar novamente em 5 segundos...');
            setTimeout(() => {
                if (autoSaveState.pendingChanges && !autoSaveState.isProcessing) {
                    console.log('🔄 Tentativa de salvamento automático após falha');
                    triggerAutoSave('retry_after_failure', autoSaveState.currentOperation?.metadata || {});
                }
            }, 5000);
        }
    } finally {
        if (autoSaveState.retryCount >= autoSaveConfig.maxRetries || autoSaveState.retryCount === 0) {
            autoSaveState.isProcessing = false;
        }
    }
}

function getOperationDisplayName(operationType) {
    const names = {
        'troca_pneus': 'troca de pneus',
        'aplicacao_pneu_avulso': 'aplicação de pneu',
        'remocao_pneu': 'remoção de pneu',
        'retry_after_failure': 'operação',
        'salvamento_manual': 'salvamento',
        'teste_manual_debug': 'teste'
    };
    return names[operationType] || 'operação';
}

function removeButtonHighlight() {
    const saveButton = document.getElementById('idDobotao');
    if (saveButton) {
        saveButton.style.animation = '';
        saveButton.style.boxShadow = '';
    }
}

// ✅ FUNÇÃO MELHORADA: Realça o botão apenas quando necessário
function highlightManualSaveButton() {
    const saveButton = document.getElementById('idDobotao');
    if (saveButton) {
        // ✅ EFEITO MAIS SUTIL: Apenas mudança de cor
        saveButton.style.backgroundColor = '#f59e0b';
        saveButton.style.borderColor = '#d97706';

        // ✅ REMOVER DESTAQUE APÓS UM TEMPO
        setTimeout(() => {
            saveButton.style.backgroundColor = '';
            saveButton.style.borderColor = '';
        }, 3000);
    }
}

// ==========================================
// SISTEMA DE CONFIRMAÇÕES
// ==========================================

function setupConfirmationSystem() {
    // Interceptar operações críticas que precisam de confirmação

    // Confirmação para remoção de múltiplos pneus
    const originalMoverPneuParaDrop = window.moverPneuParaDrop;
    window.moverPneuParaDrop = function (zone, kmRemovido, sulcoRemovido, destinacaoSolicitada) {
        const pneuId = selectedPneu1?.getAttribute('data-id');
        const destino = zone.dataset.tipo;

        if (!confirm(
            `Confirma a remoção do pneu ${pneuId} para ${destino}?\n` +
            `KM: ${kmRemovido}\n` +
            `Sulco: ${sulcoRemovido}mm\n` +
            `Destino: ${destinacaoSolicitada}\n\n` +
            `Esta operação será salva automaticamente.`
        )) {
            return;
        }

        return originalMoverPneuParaDrop.call(this, zone, kmRemovido, sulcoRemovido, destinacaoSolicitada);
    };
}

// ==========================================
// INICIALIZAÇÃO ÚNICA DO AUTO-SAVE
// ==========================================
if (!window.autoSaveGlobalInitialized) {
    window.autoSaveGlobalInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (!window.autoSaveSystemReady) {

                try {
                    initAutoSaveSystem();
                    setupConfirmationSystem();
                    window.autoSaveSystemReady = true;
                } catch (error) {
                    console.error('❌ Erro auto-save:', error);
                }
            }
        }, 1500);
    });
} else {
    console.log('⚠️ Auto-save: Evitando duplicação');
}

// ==========================================
// CONTROLES MANUAIS
// ==========================================

// Função para ativar/desativar auto-save
function toggleAutoSave() {
    autoSaveConfig.enabled = !autoSaveConfig.enabled;

    const status = autoSaveConfig.enabled ? 'ativado' : 'desativado';
    showNotification(`Auto-save ${status}`, 'info');

    if (!autoSaveConfig.enabled) {
        updateSaveStatus('pending', 'Auto-save desabilitado');
    } else {
        updateSaveStatus('saved');
    }
}

// Função para forçar salvamento manual
function forceSave() {
    if (autoSaveState.isProcessing) {
        showNotification('Aguarde o salvamento automático terminar...', 'warning');
        return;
    }

    autoSaveState.currentOperation = {
        type: 'salvamento_manual',
        data: {},
        timestamp: Date.now()
    };

    performAutoSave();
}

// Função de teste
window.testarVeiculo = function () {

    const select = document.querySelector('[name="id_veiculo"]');
    if (select && select.options.length > 1) {
        const valor = select.options[1].value;

        select.value = valor;
        select.dispatchEvent(new Event('change', { bubbles: true }));

    } else {
        console.error('❌ Select não encontrado ou sem opções');
    }
};

// 2. FUNÇÃO PARA VERIFICAR SE O BOTÃO EXISTE
function verificarBotaoAutoSave() {
    const botao = document.getElementById('toggle-autosave');
    const statusIndicator = document.getElementById('save-status-indicator');

    return { botao: !!botao, statusIndicator: !!statusIndicator };
}

// 3. FUNÇÃO CORRIGIDA PARA CRIAR O BOTÃO
function criarBotaoAutoSaveCorrigido() {

    // Remover botão existente se houver
    const botaoExistente = document.getElementById('toggle-autosave');
    if (botaoExistente) {
        botaoExistente.remove();
    }

    const botaoToggle = document.createElement('button');
    botaoToggle.type = 'button';
    botaoToggle.id = 'toggle-autosave';
    botaoToggle.innerHTML = '⚡ Auto-Save ON';

    botaoToggle.style.cssText = `
        position: fixed !important;
        bottom: 275px !important;
        left: 279px !important;
        background: #10B981 !important;
        color: white !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 12px 20px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        z-index: 99999 !important;
        transition: all 0.3s ease !important;
        min-width: 140px !important;
        text-align: center !important;
    `;

    botaoToggle.addEventListener('mouseenter', function () {
        this.style.transform = 'scale(1.05)';
        this.style.boxShadow = '0 6px 20px rgba(0,0,0,0.25)';
    });

    botaoToggle.addEventListener('mouseleave', function () {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    });

    botaoToggle.onclick = function () {

        if (typeof window.toggleAutoSave === 'function') {
            window.toggleAutoSave();
            atualizarTextoBotao();
        } else {
            console.error('❌ Função toggleAutoSave não encontrada');
            // Implementar toggle básico
            window.autoSaveConfig = window.autoSaveConfig || { enabled: true };
            window.autoSaveConfig.enabled = !window.autoSaveConfig.enabled;
            atualizarTextoBotao();
        }
    };

    function atualizarTextoBotao() {
        const enabled = window.autoSaveConfig?.enabled !== false;
        botaoToggle.innerHTML = enabled ? '⚡ Auto-Save ON' : '⏸️ Auto-Save OFF';
        botaoToggle.style.background = enabled ? '#10B981' : '#EF4444';
    }

    document.body.appendChild(botaoToggle);

    return botaoToggle;
}

// 4. FUNÇÃO PARA CRIAR INDICADOR DE STATUS
function criarIndicadorStatusCorrigido() {

    // Remover indicador existente se houver
    const indicadorExistente = document.getElementById('save-status-indicator');
    if (indicadorExistente) {
        indicadorExistente.remove();
    }

    const indicator = document.createElement('div');
    indicator.id = 'save-status-indicator';
    indicator.innerHTML = `
        <span style="font-size: 14px;">✅</span>
        <span style="color: #10B981;">Salvo</span>
    `;

    indicator.style.cssText = `
        position: fixed !important;
        bottom: 275px !important;
        left: 470px !important;
        background: white !important;
        border: 2px solid #10B981 !important;
        border-radius: 50px !important;
        padding: 8px 16px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        z-index: 99998 !important;
        transition: all 0.3s ease !important;
        min-width: 80px !important;
    `;

    document.body.appendChild(indicator);

    return indicator;
}

// 5. FUNÇÃO PRINCIPAL PARA RECRIAR ELEMENTOS AUTO-SAVE
function recriarElementosAutoSave() {

    try {
        // Criar indicador de status
        criarIndicadorStatusCorrigido();

        // Criar botão de toggle
        criarBotaoAutoSaveCorrigido();

        // Criar container de notificações se não existir
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 100000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }


        // Verificar resultado
        setTimeout(() => {
            verificarBotaoAutoSave();
        }, 100);

    } catch (error) {
        console.error('❌ Erro ao recriar elementos:', error);
    }
}

// 6. FUNÇÃO DE TESTE PARA NOTIFICAÇÕES
function testarNotificacaoAutoSave() {

    if (typeof window.showNotification === 'function') {
        window.showNotification('Teste do sistema de auto-save!', 'success', 3000);
    } else {
        console.error('❌ Função showNotification não encontrada');

        // Criar notificação básica
        const notif = document.createElement('div');
        notif.textContent = 'Teste do sistema de auto-save!';
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10B981;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            z-index: 100001;
            font-size: 14px;
        `;
        document.body.appendChild(notif);

        setTimeout(() => {
            if (notif.parentNode) {
                notif.parentNode.removeChild(notif);
            }
        }, 3000);
    }
}

// Verificar estado atual
const estado = verificarBotaoAutoSave();

if (!estado.botao || !estado.statusIndicator) {
    recriarElementosAutoSave();
} else {
    console.log('✅ Elementos auto-save já existem');
}




// Expor funções para teste manual
window.verificarBotaoAutoSave = verificarBotaoAutoSave;
window.recriarElementosAutoSave = recriarElementosAutoSave;
window.testarNotificacaoAutoSave = testarNotificacaoAutoSave;
// Expor funções globalmente para controle manual
window.toggleAutoSave = toggleAutoSave;
window.forceSave = forceSave;