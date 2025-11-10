async function verificarSessaoExistente() {
    const idVeiculo = document.querySelector('[name="id_veiculo"]').value;

    if (!idVeiculo) return;

    try {
        const response = await fetch('movimentacaopneus/auto-save-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id_veiculo: idVeiculo })
        });

        const data = await response.json();

        if (data.success && data.has_session) {
            const shouldRestore = confirm(
                `Foi detectada uma sessão anterior de movimentação para este veículo.\n\n` +
                `Última atualização: ${new Date(data.last_update).toLocaleString()}\n` +
                `Operações realizadas: ${data.operacoes_count}\n\n` +
                `Deseja restaurar a sessão anterior?`
            );

            if (shouldRestore) {
                await restaurarSessao(idVeiculo);
            } else {
                // Limpar sessão se o usuário não quiser restaurar
                await limparSessao(data.session_key);
            }
        }

    } catch (error) {
        console.error('❌ Erro ao verificar sessão:', error);
    }
}

async function restaurarSessao(idVeiculo) {
    try {
        showNotification('Restaurando sessão anterior...', 'processing');

        const response = await fetch('movimentacaopneus/restore-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id_veiculo: idVeiculo })
        });

        const data = await response.json();

        if (data.success && data.has_session) {
            // Restaurar dados do veículo
            const dadosVeiculo = data.dados_veiculo;

            // Preencher campos do formulário
            const campos = {
                '[name="select_id"]': dadosVeiculo.id_veiculo,
                '[name="id_tipo_equipamento"]': dadosVeiculo.id_tipo_equipamento,
                '[name="id_categoria"]': dadosVeiculo.id_categoria,
                '[name="id_modelo_veiculo"]': dadosVeiculo.id_modelo_veiculo,
                '[name="chassi"]': dadosVeiculo.chassi,
                '[name="km_atual"]': dadosVeiculo.km_atual
            };

            Object.entries(campos).forEach(([selector, value]) => {
                const field = document.querySelector(selector);
                if (field) field.value = value;
            });

            // Restaurar dados formatados
            formattedData = {
                eixos: dadosVeiculo.eixos,
                pneus_por_eixo: dadosVeiculo.pneus_por_eixo,
                pneusAplicadosFormatados: dadosVeiculo.pneusAplicadosFormatados
            };

            // Re-renderizar caminhão
            renderizarCaminhao(formattedData);

            showNotification(
                `Sessão restaurada! ${data.session_data.operacoes_count || 0} operações recuperadas.`,
                'success'
            );

            // Mostrar histórico de operações
            mostrarHistoricoOperacoes(data.session_data.operacoes || []);

        } else {
            showNotification('Nenhuma sessão encontrada para restaurar', 'info');
        }

    } catch (error) {
        console.error('❌ Erro ao restaurar sessão:', error);
        showNotification('Erro ao restaurar sessão anterior', 'error');
    }
}

async function limparSessao(sessionKey) {
    try {
        // Implementar chamada para limpar sessão se necessário
        console.log('🧹 Sessão anterior descartada pelo usuário');
    } catch (error) {
        console.error('❌ Erro ao limpar sessão:', error);
    }
}

// ==========================================
// HISTÓRICO DE OPERAÇÕES
// ==========================================

function mostrarHistoricoOperacoes(operacoes) {
    if (!operacoes || operacoes.length === 0) return;

    // Criar modal de histórico
    const modal = document.createElement('div');
    modal.id = 'modal-historico';
    modal.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 10002;
        max-width: 600px;
        max-height: 70vh;
        overflow-y: auto;
        padding: 0;
    `;

    const header = document.createElement('div');
    header.style.cssText = `
        background: #F3F4F6;
        padding: 20px;
        border-bottom: 1px solid #E5E7EB;
        border-radius: 12px 12px 0 0;
    `;
    header.innerHTML = `
        <h3 style="margin: 0; color: #1F2937; font-size: 18px; font-weight: 600;">
            📋 Histórico de Operações Restauradas
        </h3>
        <p style="margin: 8px 0 0 0; color: #6B7280; font-size: 14px;">
            ${operacoes.length} operação(ões) foram restauradas da sessão anterior
        </p>
    `;

    const content = document.createElement('div');
    content.style.cssText = 'padding: 20px;';

    const lista = document.createElement('div');
    lista.style.cssText = 'display: flex; flex-direction: column; gap: 12px;';

    operacoes.forEach((op, index) => {
        const item = document.createElement('div');
        item.style.cssText = `
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 16px;
            position: relative;
        `;

        const timestamp = new Date(op.timestamp).toLocaleString();
        const tipoDisplay = getTipoOperacaoDisplay(op.tipo);

        item.innerHTML = `
            <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 8px;">
                <span style="background: #3B82F6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                    ${index + 1}
                </span>
                <span style="color: #6B7280; font-size: 12px; margin-left: auto;">
                    ${timestamp}
                </span>
            </div>
            <div style="font-weight: 600; color: #1F2937; margin-bottom: 4px;">
                ${tipoDisplay}
            </div>
            <div style="color: #6B7280; font-size: 14px;">
                ${getDetalhesOperacao(op)}
            </div>
        `;

        lista.appendChild(item);
    });

    content.appendChild(lista);

    const footer = document.createElement('div');
    footer.style.cssText = `
        background: #F3F4F6;
        padding: 20px;
        border-top: 1px solid #E5E7EB;
        border-radius: 0 0 12px 12px;
        text-align: right;
    `;

    const btnFechar = document.createElement('button');
    btnFechar.textContent = 'Entendi';
    btnFechar.style.cssText = `
        background: #3B82F6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
    `;

    btnFechar.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(overlay);
    };

    footer.appendChild(btnFechar);

    modal.appendChild(header);
    modal.appendChild(content);
    modal.appendChild(footer);

    // Overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 10001;
    `;

    overlay.onclick = () => {
        document.body.removeChild(modal);
        document.body.removeChild(overlay);
    };

    document.body.appendChild(overlay);
    document.body.appendChild(modal);
}

function getTipoOperacaoDisplay(tipo) {
    const tipos = {
        'troca_pneus': '🔄 Troca entre Pneus',
        'aplicacao_pneu_avulso': '🛞 Aplicação de Pneu Avulso',
        'remocao_pneu': '📤 Remoção de Pneu',
        'salvamento_manual': '💾 Salvamento Manual'
    };
    return tipos[tipo] || `📝 ${tipo}`;
}

function getDetalhesOperacao(operacao) {
    const dados = operacao.dados || {};

    switch (operacao.tipo) {
        case 'troca_pneus':
            return `Pneus trocados: ${dados.pneu1_id || 'N/A'} ↔ ${dados.pneu2_id || 'N/A'}`;
        case 'aplicacao_pneu_avulso':
            return `Pneu ${dados.pneu_avulso_id || 'N/A'} aplicado na posição ${dados.localizacao || 'N/A'}`;
        case 'remocao_pneu':
            return `Pneu removido para ${dados.destino || 'N/A'} - KM: ${dados.km_removido || 'N/A'}, Sulco: ${dados.sulco_removido || 'N/A'}mm`;
        default:
            return 'Operação realizada com sucesso';
    }
}

// ==========================================
// MELHORIAS NA INTERFACE
// ==========================================

function adicionarBotaoToggleAutoSave() {
    const botaoToggle = document.createElement('button');
    botaoToggle.type = 'button';
    botaoToggle.id = 'toggle-autosave';
    botaoToggle.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: #10B981;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9998;
        transition: all 0.3s ease;
    `;

    function updateToggleButton() {
        if (autoSaveConfig.enabled) {
            botaoToggle.textContent = '⚡ Auto-Save ON';
            botaoToggle.style.background = '#10B981';
        } else {
            botaoToggle.textContent = '⏸️ Auto-Save OFF';
            botaoToggle.style.background = '#EF4444';
        }
    }

    botaoToggle.onclick = () => {
        toggleAutoSave();
        updateToggleButton();
    };

    updateToggleButton();
    document.body.appendChild(botaoToggle);
}

// ==========================================
// BACKUP DE EMERGÊNCIA
// ==========================================

function criarBackupEmergencia() {
    if (!formattedData) return null;

    const backup = {
        timestamp: Date.now(),
        dados: JSON.stringify(coletarDadosParaEnvio()),
        veiculo_id: document.querySelector('[name="id_veiculo"]').value,
        user_agent: navigator.userAgent
    };

    // Salvar no localStorage como último recurso
    try {
        localStorage.setItem('movimentacao_pneus_backup', JSON.stringify(backup));
    } catch (error) {
        console.warn('⚠️ Não foi possível criar backup de emergência:', error);
    }

    return backup;
}

function verificarBackupEmergencia() {
    try {
        const backup = localStorage.getItem('movimentacao_pneus_backup');
        if (!backup) return;

        const dados = JSON.parse(backup);
        const idade = Date.now() - dados.timestamp;

        // Se o backup tem menos de 2 horas
        if (idade < 2 * 60 * 60 * 1000) {
            const shouldRestore = confirm(
                `Foi encontrado um backup de emergência local.\n\n` +
                `Criado em: ${new Date(dados.timestamp).toLocaleString()}\n` +
                `Idade: ${Math.round(idade / 60000)} minutos\n\n` +
                `Deseja restaurar este backup?`
            );

            if (shouldRestore) {
                // Implementar restauração do backup local
                showNotification('Backup de emergência restaurado', 'success');
            }
        }

        // Limpar backup antigo
        if (idade > 24 * 60 * 60 * 1000) { // Mais de 24 horas
            localStorage.removeItem('movimentacao_pneus_backup');
        }

    } catch (error) {
        console.error('❌ Erro ao verificar backup de emergência:', error);
    }
}

// Evitar múltiplas inicializações
if (!window.sessionRestInitialized) {
    window.sessionRestInitialized = true;

    document.addEventListener('DOMContentLoaded', () => {

        setTimeout(() => {
            if (!window.interceptacaoConfigurada) {
                configurarInterceptacaoVeiculoCorrigida();
                window.interceptacaoConfigurada = true;
            }
        }, 2500); // Aguardar mais tempo
    });
}

// Função corrigida para interceptação
function configurarInterceptacaoVeiculoCorrigida() {

    // NÃO fazer wrapper - apenas listener direto
    setTimeout(() => {
        const selectElement = document.querySelector('[name="id_veiculo"]');
        if (selectElement) {
            selectElement.addEventListener('change', function (event) {
                if (event.target.value) {
                    setTimeout(() => {
                        verificarSessaoExistente();
                    }, 3000);
                }
            });
        }
    }, 1000);
}

// ==========================================
// EVENTOS DE PÁGINA
// ==========================================

// Criar backup antes de sair da página
window.addEventListener('beforeunload', (event) => {
    if (autoSaveState.pendingChanges && autoSaveConfig.enabled) {
        criarBackupEmergencia();

        event.preventDefault();
        event.returnValue = 'Existem alterações não salvas. Tem certeza que deseja sair?';
        return event.returnValue;
    }
});

// Tentar salvar quando a página fica invisível (mobile/tab switching)
document.addEventListener('visibilitychange', () => {
    if (document.hidden && autoSaveState.pendingChanges && autoSaveConfig.enabled) {
        // Forçar salvamento imediato
        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
            performAutoSave();
        }
    }
});


// ==========================================
// CORREÇÃO CRÍTICA: session_rest.js
// ==========================================

// Modificar o DOMContentLoaded existente para corrigir conflitos
document.addEventListener('DOMContentLoaded', () => {

    // Aguardar inicialização do sistema principal
    setTimeout(() => {

        // Só inicializar se ainda não foi feito
        if (!window.autoSaveSystemReady) {

            // Verificar se as funções base existem antes de inicializar
            const funcionesBase = ['showNotification', 'removeNotification'];
            const funcionesFaltando = funcionesBase.filter(f => typeof window[f] !== 'function');

            if (funcionesFaltando.length > 0) {
                console.warn('⚠️ Funções base faltando:', funcionesFaltando);

                // Criar funções básicas se não existirem
                if (typeof window.showNotification !== 'function') {
                    window.showNotification = function (message, type = 'info', duration = 4000) {


                        // Criar notificação visual simples
                        const notification = document.createElement('div');
                        notification.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #3B82F6;
                            color: white;
                            padding: 12px 16px;
                            border-radius: 8px;
                            z-index: 10000;
                            font-size: 14px;
                        `;
                        notification.textContent = message;
                        document.body.appendChild(notification);

                        if (duration > 0) {
                            setTimeout(() => {
                                if (notification.parentNode) {
                                    notification.parentNode.removeChild(notification);
                                }
                            }, duration);
                        }

                        return notification;
                    };
                }

                if (typeof window.removeNotification !== 'function') {
                    window.removeNotification = function (notification) {
                        if (notification && notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    };
                }
            }

            // Inicializar sistema de auto-save
            try {
                if (typeof initAutoSaveSystem === 'function') {
                    initAutoSaveSystem();
                } else {
                    console.warn('⚠️ initAutoSaveSystem não disponível ainda');
                }

                if (typeof setupConfirmationSystem === 'function') {
                    setupConfirmationSystem();
                } else {
                    console.warn('⚠️ setupConfirmationSystem não disponível ainda');
                }

                // Marcar como inicializado
                window.autoSaveSystemReady = true;

            } catch (error) {
                console.error('❌ Erro ao inicializar auto-save:', error);
            }
        } else {
            console.log('ℹ️ Auto-save já foi inicializado anteriormente');
        }

        // Configurar interceptação de veículo
        configurarInterceptacaoVeiculo();

    }, 800); // Aguardar mais tempo para evitar conflitos
});

// ==========================================
// FUNÇÃO PARA INTERCEPTAR SELEÇÃO DE VEÍCULO
// ==========================================

function configurarInterceptacaoVeiculo() {
    // Tentar múltiplas abordagens para capturar a seleção

    // Abordagem 1: Interceptar onSmartSelectChange se existir
    if (typeof window.onSmartSelectChange === 'function') {

        const originalOnSmartSelectChange = window.onSmartSelectChange;

        window.onSmartSelectChange = function (name, callback) {

            if (name === 'id_veiculo') {
                const wrappedCallback = function (veiculo) {

                    // Executar callback original
                    const result = callback.call(this, veiculo);

                    // Verificar sessão após um delay
                    if (veiculo && veiculo.value) {
                        setTimeout(() => {
                            verificarSessaoExistente();
                        }, 2000);
                    }

                    return result;
                };

                return originalOnSmartSelectChange.call(this, name, wrappedCallback);
            } else {
                return originalOnSmartSelectChange.call(this, name, callback);
            }
        };

    } else {
        console.warn('⚠️ onSmartSelectChange não encontrado');
    }

    // Abordagem 2: Listener direto no elemento
    setTimeout(() => {
        const selectElement = document.querySelector('[name="id_ordem_servico"]');

        if (selectElement) {

            selectElement.addEventListener('change', function (event) {

                if (event.target.value) {
                    setTimeout(() => {
                        verificarSessaoExistente();
                    }, 3000);
                }
            });

        } else {
            console.warn('⚠️ Elemento select não encontrado');
        }
    }, 1000);

    // Abordagem 3: Observer para mudanças dinâmicas
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                // Verificar se novos elementos foram adicionados
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const selectVeiculo = node.querySelector ? node.querySelector('[name="id_veiculo"]') : null;
                        if (selectVeiculo) {
                            configurarListenerVeiculo(selectVeiculo);
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

function configurarListenerVeiculo(selectElement) {
    selectElement.addEventListener('change', function (event) {

        if (event.target.value) {
            setTimeout(() => {
                verificarSessaoExistente();
            }, 2000);
        }
    });
}

// ==========================================
// FUNÇÃO MELHORADA PARA VERIFICAR SESSÃO
// ==========================================

async function verificarSessaoExistente() {
    try {
        const idVeiculo = document.querySelector('[name="id_veiculo"]')?.value ||
            document.querySelector('[name="select_id"]')?.value;


        if (!idVeiculo) {
            return;
        }

        // Verificar se as funções necessárias existem
        if (typeof showNotification !== 'function') {
            console.warn('⚠️ showNotification não disponível, pulando verificação de sessão');
            return;
        }

        const response = await fetch('movimentacaopneus/auto-save-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ id_veiculo: idVeiculo })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.has_session) {
            const shouldRestore = confirm(
                `Foi detectada uma sessão anterior de movimentação para este veículo.\n\n` +
                `Última atualização: ${new Date(data.last_update).toLocaleString()}\n` +
                `Operações realizadas: ${data.operacoes_count}\n\n` +
                `Deseja restaurar a sessão anterior?`
            );

            if (shouldRestore) {
                await restaurarSessao(idVeiculo);
            } else {
                console.log('🗑️ Usuário optou por não restaurar a sessão');
            }
        } else {
            console.log('ℹ️ Nenhuma sessão anterior encontrada');
        }

    } catch (error) {
        console.error('❌ Erro ao verificar sessão:', error);

        // Só mostrar erro se for um problema real (não de rede temporário)
        if (error.message !== 'HTTP 500' && typeof showNotification === 'function') {
            showNotification('Erro ao verificar sessão anterior', 'warning', 3000);
        }
    }
}

// ==========================================
// FUNÇÃO MELHORADA PARA RESTAURAR SESSÃO
// ==========================================

async function restaurarSessao(idVeiculo) {
    try {

        if (typeof showNotification === 'function') {
            showNotification('Restaurando sessão anterior...', 'processing');
        }

        const response = await fetch('movimentacaopneus/restore-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ id_veiculo: idVeiculo })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();


        if (data.success && data.has_session) {
            // Restaurar dados do veículo
            const dadosVeiculo = data.dados_veiculo;

            // Preencher campos do formulário
            const campos = {
                '[name="select_id"]': dadosVeiculo.id_veiculo,
                '[name="id_tipo_equipamento"]': dadosVeiculo.id_tipo_equipamento,
                '[name="id_categoria"]': dadosVeiculo.id_categoria,
                '[name="id_modelo_veiculo"]': dadosVeiculo.id_modelo_veiculo,
                '[name="chassi"]': dadosVeiculo.chassi,
                '[name="km_atual"]': dadosVeiculo.km_atual
            };

            Object.entries(campos).forEach(([selector, value]) => {
                const field = document.querySelector(selector);
                if (field && value) {
                    field.value = value;

                }
            });

            // Restaurar dados formatados
            if (typeof window !== 'undefined') {
                window.formattedData = {
                    eixos: dadosVeiculo.eixos,
                    pneus_por_eixo: dadosVeiculo.pneus_por_eixo,
                    pneusAplicadosFormatados: dadosVeiculo.pneusAplicadosFormatados
                };
            }

            // Re-renderizar caminhão se a função existir
            if (typeof renderizarCaminhao === 'function' && window.formattedData) {
                renderizarCaminhao(window.formattedData);
            } else {
                console.warn('⚠️ renderizarCaminhao não disponível ou dados não carregados');
            }

            if (typeof showNotification === 'function') {
                showNotification(
                    `Sessão restaurada! ${data.session_data.operacoes_count || 0} operações recuperadas.`,
                    'success'
                );
            }

            // Mostrar histórico de operações se disponível
            if (typeof mostrarHistoricoOperacoes === 'function') {
                mostrarHistoricoOperacoes(data.session_data.operacoes || []);
            }

        } else {
            if (typeof showNotification === 'function') {
                showNotification('Nenhuma sessão encontrada para restaurar', 'info');
            }
        }

    } catch (error) {
        console.error('❌ Erro ao restaurar sessão:', error);

        if (typeof showNotification === 'function') {
            showNotification('Erro ao restaurar sessão anterior', 'error');
        }
    }
}

// ==========================================
// EXPOR FUNÇÕES GLOBALMENTE
// ==========================================

window.verificarSessaoExistente = verificarSessaoExistente;
window.restaurarSessao = restaurarSessao;
window.configurarInterceptacaoVeiculo = configurarInterceptacaoVeiculo;