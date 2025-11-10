<!-- Script Alpine.js inline -->
<script>
    function modalComponent() {
        return {
            modalOpen: false,
            loading: false,
            dados: null,
            erro: null,
            bloqueiaFechamento: false, // Nova flag para bloquear fechamento

            async carregarDados(id) {
                this.loading = true;
                this.erro = null;
                this.modalOpen = true;
                this.bloqueiaFechamento = false; // Reset da flag

                try {
                    const response = await fetch(`/admin/requisicaopneusvendas/${id}/dados`);

                    if (!response.ok) {
                        throw new Error('Erro ao buscar dados');
                    }

                    const data = await response.json();
                    this.dados = data;

                } catch (error) {
                    console.error('Erro ao carregar dados:', error);
                    this.erro = 'Erro ao carregar dados da requisição';
                    alert('Erro ao carregar dados da requisição');
                    this.closeModal();
                } finally {
                    this.loading = false;
                }
            },

            closeModal() {
                // Se estiver bloqueado, não fechar
                if (this.bloqueiaFechamento) {
                    return;
                }

                this.modalOpen = false;
                // Limpar dados quando fechar o modal
                setTimeout(() => {
                    this.dados = null;
                    this.erro = null;
                }, 300); // Aguarda a animação de fechamento
            },

            // Método para lidar com atualizações de requisição
            handleRequisicaoAtualizada(detail) {
                if (detail.sucesso === false) {
                    // Só fecha o modal se houve erro
                    this.closeModal();
                } else if (detail.sucesso === true) {
                    this.dados = null;
                    this.erro = null;

                    // Opcional: Recarregar a página após um delay para mostrar as mudanças
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            },

            // Método para mostrar notificações
            showNotification(type, message) {
                // Se você estiver usando BladewindUI
                if (typeof showNotification === 'function') {
                    showNotification(message, type);
                } else {
                    // Fallback para alert
                    alert(message);
                }
            }
        }
    }
</script>

<script>
    function requisicaoComponent() {
        return {
            // ADICIONADO: Controle do modal principal (se necessário)
            modalPrincipalAberto: false,

            // ID da requisição (será passado pelo modal pai)
            requisicaoId: null,

            // Estado do modal de confirmação
            modalConfirmacao: {
                aberto: false,
                acao: null,
                justificativa: '',
                processando: false,
                erro: null,
                config: {
                    titulo: '',
                    mensagem: '',
                    icone: '',
                    corIcone: '',
                    corFundo: '',
                    corBotao: '',
                    textoBotao: '',
                    justificativaObrigatoria: false,
                    placeholderJustificativa: ''
                }
            },

            // NOVO: Estado do modal de valores
            modalValores: {
                aberto: false,
                carregando: false,
                erro: null,
                modoEdicao: false, // NOVO: controla se está em modo de edição
                salvando: false, // NOVO: controla estado de salvamento
                valoresEditados: {}, // NOVO: armazena valores editados {pneuId: valor}
                valoresOriginais: {}, // NOVO: backup dos valores originais
                pneus: [],
                resumo: {
                    totalPneus: 0,
                    valorTotal: 'R$ 0,00',
                    valorMedio: 'R$ 0,00'
                },
                // NOVO: Modal de aplicação em lote
                modalLote: {
                    aberto: false,
                    valor: ''
                }
            },

            // Feedback para usuário
            feedback: {
                mostrar: false,
                tipo: 'sucesso', // ou 'erro'
                mensagem: ''
            },

            // Configurações das ações
            configsAcoes: {
                aprovar: {
                    titulo: 'Aprovar Requisição',
                    mensagem: 'Tem certeza que deseja aprovar esta requisição de pneus para venda?',
                    icone: 'M5 13l4 4L19 7',
                    corIcone: 'text-green-600',
                    corFundo: 'bg-green-100',
                    corBotao: 'bg-green-600 hover:bg-green-700 disabled:bg-green-400',
                    textoBotao: 'Aprovar',
                    justificativaObrigatoria: false,
                    placeholderJustificativa: 'Digite uma justificativa para a aprovação (opcional)...'
                },
                reprovar: {
                    titulo: 'Reprovar Requisição',
                    mensagem: 'Tem certeza que deseja reprovar esta requisição de pneus para venda?',
                    icone: 'M6 18L18 6M6 6l12 12',
                    corIcone: 'text-red-600',
                    corFundo: 'bg-red-100',
                    corBotao: 'bg-red-600 hover:bg-red-700 disabled:bg-red-400',
                    textoBotao: 'Reprovar',
                    justificativaObrigatoria: true,
                    placeholderJustificativa: 'Digite o motivo da reprovação (obrigatório)...'
                }
            },

            // ADICIONADO: Método para fechar modal principal
            fecharModalPrincipal() {
                // Se este componente está dentro de um modal pai, você pode:
                // 1. Disparar um evento personalizado
                this.$dispatch('fechar-modal-principal');

                // 2. Ou se você tem acesso direto à variável do componente pai
                // this.$parent.modalOpen = false;

                // 3. Ou usar uma variável própria
                this.modalPrincipalAberto = false;
            },

            // Abrir modal de confirmação
            async abrirConfirmacao(acao) {

                // Aguardar dados se necessário
                const id = await this.obterRequisicaoIdComEspera();

                if (!id) {
                    alert(
                        'Erro: Não foi possível identificar a requisição atual. Os dados podem ainda estar carregando. Tente novamente em alguns segundos.'
                    );
                    return;
                }

                if (!this.configsAcoes[acao]) {
                    console.error(`Ação "${acao}" não configurada`);
                    return;
                }

                this.modalConfirmacao.acao = acao;
                this.modalConfirmacao.config = {
                    ...this.configsAcoes[acao]
                };
                this.modalConfirmacao.justificativa = '';
                this.modalConfirmacao.erro = null;
                this.modalConfirmacao.processando = false;
                this.modalConfirmacao.aberto = true;

                this.$nextTick(() => {
                    const textarea = this.$el.querySelector('textarea');
                    if (textarea) textarea.focus();
                });
            },

            // NOVO: Abrir modal de valores
            async abrirModalValores() {

                // Aguardar dados se necessário
                const id = await this.obterRequisicaoIdComEspera();

                if (!id) {
                    alert(
                        'Erro: Não foi possível identificar a requisição atual. Os dados podem ainda estar carregando. Tente novamente em alguns segundos.'
                    );
                    return;
                }

                this.modalValores.aberto = true;
                await this.carregarValoresPneus();
            },

            // NOVO: Carregar valores dos pneus
            async carregarValoresPneus() {
                this.modalValores.carregando = true;
                this.modalValores.erro = null;

                try {
                    const response = await fetch(
                        `/admin/requisicaopneusvendas/${this.requisicaoId}/valores-venda`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao carregar valores dos pneus');
                    }

                    // Verificar se a resposta tem sucesso
                    if (!data.sucesso) {
                        throw new Error('Erro na resposta da API');
                    }

                    // Mapear os dados dos pneus para o formato esperado pelo frontend
                    const pneusMapeados = (data.pneus || []).map(pneu => ({
                        id: pneu.id_pneu,
                        codigo: pneu.numero_fogo || pneu.id_pneu,
                        modelo: pneu.modelo || 'Não informado',
                        condicao: this.mapearCondicao(pneu.vida),
                        valorVenda: this.formatarMoeda(pneu.valor_venda),
                        status: pneu.status,
                        dataInclusao: pneu.data_inclusao_item
                    }));

                    // Calcular resumo
                    const totalPneus = pneusMapeados.length;
                    const valorTotalNumerico = (data.pneus || []).reduce((total, pneu) => {
                        // Usar valor original do backend para cálculo
                        const valor = parseFloat(pneu.valor_venda) || 0;
                        return total + valor;
                    }, 0);

                    const valorMedioNumerico = totalPneus > 0 ? valorTotalNumerico / totalPneus : 0;

                    // Atualizar dados no estado
                    this.modalValores.pneus = pneusMapeados;
                    this.modalValores.resumo = {
                        totalPneus: totalPneus,
                        valorTotal: this.formatarMoeda(valorTotalNumerico),
                        valorMedio: this.formatarMoeda(valorMedioNumerico)
                    };

                } catch (error) {
                    console.error('Erro ao carregar valores:', error);
                    this.modalValores.erro = error.message;

                    // Limpar dados em caso de erro
                    this.modalValores.pneus = [];
                    this.modalValores.resumo = {
                        totalPneus: 0,
                        valorTotal: 'R$ 0,00',
                        valorMedio: 'R$ 0,00'
                    };
                } finally {
                    this.modalValores.carregando = false;
                }
            },

            // NOVO: Mapear condição do pneu baseado na vida
            mapearCondicao(vida) {
                if (!vida) return 'Não informado';

                const vidaNum = parseInt(vida);
                if (vidaNum === 1) return '1';
                if (vidaNum === 2) return '2';
                if (vidaNum === 3) return '3';
                if (vidaNum >= 4) return '4';

                return vida;
            },

            // NOVO: Formatar valor em moeda brasileira
            formatarMoeda(valor) {
                if (!valor && valor !== 0) return 'R$ 0,00';

                const numero = typeof valor === 'string' ? parseFloat(valor) : valor;

                if (isNaN(numero)) return 'R$ 0,00';

                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(numero);
            },

            // NOVO: Fechar modal de valores
            fecharModalValores() {
                this.modalValores.aberto = false;
                this.modalValores.erro = null;
                this.modalValores.pneus = [];
                this.modalValores.resumo = {
                    totalPneus: 0,
                    valorTotal: 'R$ 0,00',
                    valorMedio: 'R$ 0,00'
                };
            },

            // NOVO: Exportar valores para Excel
            async exportarValores() {
                try {
                    this.mostrarFeedback('sucesso', 'Iniciando exportação...');

                    const response = await fetch(
                        `/admin/requisicaopneusvendas/${this.requisicaoId}/exportar-valores`, {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                    if (!response.ok) {
                        throw new Error('Erro ao exportar dados');
                    }

                    // Criar download do arquivo
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `valores_pneus_requisicao_${this.requisicaoId}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    this.mostrarFeedback('sucesso', 'Arquivo exportado com sucesso!');

                } catch (error) {
                    console.error('Erro ao exportar:', error);
                    this.mostrarFeedback('erro', 'Erro ao exportar arquivo: ' + error.message);
                }
            },

            // Método auxiliar para obter o ID da requisição
            obterRequisicaoId() {
                // Limpar o cache primeiro
                this.requisicaoId = null;

                // 1. PRIORIDADE: Input hidden dentro do modal atual
                const inputHidden = this.$el.querySelector('input[name="requisicao_id"]');
                if (inputHidden && inputHidden.value && inputHidden.value !== '') {
                    this.requisicaoId = inputHidden.value;
                    return inputHidden.value;
                }

                // 2. Data attribute no footer do modal
                const footer = this.$el.closest('[data-requisicao-id]');
                if (footer) {
                    const id = footer.getAttribute('data-requisicao-id');
                    if (id && id !== 'null' && id !== 'undefined' && id !== '') {
                        this.requisicaoId = id;
                        return id;
                    }
                }

                // 3. Buscar em elementos do modal pai (contexto dos dados)
                const modalElement = this.$el.closest('.fixed.inset-0');
                if (modalElement) {
                    // Buscar input hidden no modal pai
                    const inputModalPai = modalElement.querySelector('input[name="requisicao_id"]');
                    if (inputModalPai && inputModalPai.value) {
                        this.requisicaoId = inputModalPai.value;
                        return inputModalPai.value;
                    }

                    // Buscar data attribute no modal pai
                    const elementComData = modalElement.querySelector('[data-requisicao-id]');
                    if (elementComData) {
                        const id = elementComData.getAttribute('data-requisicao-id');
                        if (id && id !== 'null' && id !== 'undefined' && id !== '') {
                            this.requisicaoId = id;
                            return id;
                        }
                    }
                }

                // 4. Fallback: URL (caso o modal seja acessado diretamente)
                const urlMatch = window.location.pathname.match(/\/requisicaopneusvendas\/(\d+)/);
                if (urlMatch && urlMatch[1]) {
                    this.requisicaoId = urlMatch[1];
                    return urlMatch[1];
                }

                // 5. Último recurso: buscar no contexto Alpine.js pai
                try {
                    // Tentar acessar os dados do componente pai
                    let elementoPai = this.$el.parentElement;
                    while (elementoPai) {
                        if (elementoPai.__x && elementoPai.__x.$data && elementoPai.__x.$data.dados) {
                            const dadosPai = elementoPai.__x.$data.dados;
                            if (dadosPai.requisicao && dadosPai.requisicao.id_requisicao_pneu) {
                                this.requisicaoId = dadosPai.requisicao.id_requisicao_pneu;
                                return this.requisicaoId;
                            }
                        }
                        elementoPai = elementoPai.parentElement;
                    }
                } catch (e) {
                    console.error('Erro ao acessar dados do componente pai:', e);
                }

                console.error('❌ ID da requisição não encontrado em nenhum local!');

                return null;
            },

            // Fechar modal
            fecharConfirmacao() {
                this.modalConfirmacao.aberto = false;
                this.modalConfirmacao.acao = null;
                this.modalConfirmacao.justificativa = '';
                this.modalConfirmacao.erro = null;
                this.modalConfirmacao.processando = false;
            },

            // Confirmar ação
            async confirmarAcao() {
                const config = this.modalConfirmacao.config;

                // Validar justificativa obrigatória
                if (config.justificativaObrigatoria && !this.modalConfirmacao.justificativa.trim()) {
                    this.modalConfirmacao.erro = 'A justificativa é obrigatória';
                    return;
                }

                this.modalConfirmacao.erro = null;
                this.modalConfirmacao.processando = true;

                try {
                    // Chamada para API
                    // Salvar dados antes de qualquer operação
                    const acaoAtual = this.modalConfirmacao.acao;
                    const justificativaAtual = this.modalConfirmacao.justificativa;

                    const result = await this.executarAcao(acaoAtual, justificativaAtual);

                    // Mostrar feedback de sucesso ANTES de fechar
                    this.mostrarFeedback('sucesso',
                        `Venda ${acaoAtual === 'aprovar' ? 'aprovada' : 'reprovada'} com sucesso!`
                    );

                    // Fechar modal de confirmação após um pequeno delay
                    setTimeout(() => {
                        this.fecharConfirmacao();
                    }, 300);

                    // Disparar evento para fechar modal principal
                    setTimeout(() => {
                        this.$dispatch('requisicao-atualizada', {
                            id: this.requisicaoId,
                            acao: acaoAtual,
                            justificativa: justificativaAtual,
                            sucesso: true
                        });
                    }, 500);

                } catch (error) {
                    this.$dispatch('bloquear-fechamento-modal');

                    // Em caso de erro, NÃO fechar o modal - apenas mostrar o erro
                    this.modalConfirmacao.erro = error.message || 'Erro ao processar ação';

                    // Também mostrar feedback visual (opcional)
                    this.mostrarFeedback('erro', error.message || 'Erro ao processar ação');
                } finally {
                    // Sempre parar o loading
                    this.modalConfirmacao.processando = false;
                }
            },

            // Executar ação (aqui você faria a chamada real para o backend)
            async executarAcao(acao, justificativa) {
                // Simular delay da API
                await new Promise(resolve => setTimeout(resolve, 1500));
                // Aqui você faria algo como:

                const response = await fetch(`/admin/requisicaopneusvendas/${this.requisicaoId}/${acao}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        justificativa: justificativa
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message);
                }

                return await response.json();
            },

            obterValorEdicao(pneuId, valorOriginal) {
                if (this.modalValores.valoresEditados[pneuId] !== undefined) {
                    return this.modalValores.valoresEditados[pneuId];
                }

                // Converter valor original para formato de edição (apenas números)
                if (typeof valorOriginal === 'number') {
                    return valorOriginal.toFixed(2).replace('.', ',');
                }

                // Se for string, remover formatação
                const valorLimpo = (valorOriginal || '0').toString()
                    .replace(/[^\d,]/g, '')
                    .replace('.', ',');

                return valorLimpo || '0,00';
            },

            atualizarValorPneu(pneuId, valor) {
                // Formatar valor durante a digitação
                const valorFormatado = this.formatarValorEntrada(valor);

                // Armazenar valor editado
                this.modalValores.valoresEditados[pneuId] = valorFormatado;

                // Recalcular resumo
                this.recalcularResumo();
            },

            validarValorPneu(pneuId, valor) {
                const valorNumerico = this.converterParaNumero(valor);

                if (valorNumerico < 0) {
                    alert('O valor não pode ser negativo');
                    this.modalValores.valoresEditados[pneuId] = '0,00';
                } else if (valorNumerico > 99999.99) {
                    alert('O valor não pode ser maior que R$ 99.999,99');
                    this.modalValores.valoresEditados[pneuId] = '99999,99';
                }

                this.recalcularResumo();
            },

            formatarValorEntrada(valor) {
                // Remove tudo que não é dígito
                let apenasNumeros = valor.replace(/\D/g, '');

                // Se vazio, retorna 0,00
                if (!apenasNumeros) return '0,00';

                // Converte para número e divide por 100 para ter centavos
                let numero = parseInt(apenasNumeros) / 100;

                // Formata com 2 casas decimais
                return numero.toFixed(2).replace('.', ',');
            },

            converterParaNumero(valor) {
                if (typeof valor === 'number') return valor;

                const valorLimpo = (valor || '0').toString()
                    .replace(/\./g, '') // Remove pontos (milhares)
                    .replace(',', '.'); // Converte vírgula para ponto decimal

                return parseFloat(valorLimpo) || 0;
            },

            cancelarEdicaoValor(pneuId) {
                delete this.modalValores.valoresEditados[pneuId];
                this.recalcularResumo();
            },

            resetarValores() {
                if (Object.keys(this.modalValores.valoresEditados).length === 0) {
                    return;
                }

                if (confirm('Tem certeza que deseja descartar todas as alterações?')) {
                    this.modalValores.valoresEditados = {};
                    this.recalcularResumo();
                }
            },

            aplicarValorEmLote() {
                this.modalValores.modalLote.aberto = true;
                this.modalValores.modalLote.valor = '';
            },

            recalcularResumo() {
                const totalPneus = this.modalValores.pneus.length;
                let valorTotalNumerico = 0;

                this.modalValores.pneus.forEach(pneu => {
                    let valorPneu;

                    // Verificar se existe valor editado
                    if (this.modalValores.valoresEditados[pneu.id] !== undefined) {
                        valorPneu = this.converterParaNumero(this.modalValores.valoresEditados[pneu.id]);
                    } else {
                        valorPneu = pneu.valorVendaNumerico || 0;
                    }

                    valorTotalNumerico += valorPneu;
                });

                const valorMedioNumerico = totalPneus > 0 ? valorTotalNumerico / totalPneus : 0;

                this.modalValores.resumo = {
                    totalPneus: totalPneus,
                    valorTotal: this.formatarMoeda(valorTotalNumerico),
                    valorMedio: this.formatarMoeda(valorMedioNumerico)
                };
            },

            formatarValorLote(valor) {
                this.modalValores.modalLote.valor = this.formatarValorEntrada(valor);
            },

            confirmarAplicacaoLote() {
                const valor = this.modalValores.modalLote.valor;

                if (!valor || this.converterParaNumero(valor) <= 0) {
                    alert('Por favor, informe um valor válido');
                    return;
                }

                const totalPneus = this.modalValores.pneus.length;
                const valorFormatado = this.formatarMoeda(this.converterParaNumero(valor));

                if (confirm(`Aplicar o valor ${valorFormatado} em ${totalPneus} pneu(s)?`)) {
                    // Aplicar valor a todos os pneus
                    this.modalValores.pneus.forEach(pneu => {
                        this.modalValores.valoresEditados[pneu.id] = valor;
                    });

                    this.modalValores.modalLote.aberto = false;
                    this.modalValores.modalLote.valor = '';
                    this.recalcularResumo();

                    this.mostrarFeedback('sucesso', `Valor aplicado a ${totalPneus} pneu(s)`);
                }
            },

            async salvarValoresEditados() {
                if (Object.keys(this.modalValores.valoresEditados).length === 0) {
                    this.mostrarFeedback('erro', 'Nenhuma alteração para salvar');
                    return;
                }

                this.modalValores.salvando = true;

                try {
                    // Preparar dados para envio
                    const valoresParaSalvar = {};

                    Object.keys(this.modalValores.valoresEditados).forEach(pneuId => {
                        valoresParaSalvar[pneuId] = this.converterParaNumero(
                            this.modalValores.valoresEditados[pneuId]
                        );
                    });

                    const response = await fetch(
                        `/admin/requisicaopneusvendas/${this.requisicaoId}/atualizar-valores`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                valores: valoresParaSalvar
                            })
                        });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao salvar valores');
                    }

                    // Sucesso - atualizar dados locais
                    this.modalValores.pneus.forEach(pneu => {
                        if (this.modalValores.valoresEditados[pneu.id] !== undefined) {
                            const novoValor = this.converterParaNumero(this.modalValores.valoresEditados[
                                pneu.id]);
                            pneu.valorVendaNumerico = novoValor;
                            pneu.valorVenda = this.formatarMoeda(novoValor);
                        }
                    });

                    // Limpar valores editados
                    this.modalValores.valoresEditados = {};

                    // Sair do modo edição
                    this.modalValores.modoEdicao = false;

                    // Recalcular resumo
                    this.recalcularResumo();

                    this.mostrarFeedback('sucesso', 'Valores atualizados com sucesso!');

                } catch (error) {
                    console.error('Erro ao salvar valores:', error);
                    this.mostrarFeedback('erro', 'Erro ao salvar valores: ' + error.message);
                } finally {
                    this.modalValores.salvando = false;
                }
            },

            async aguardarDadosModal() {
                return new Promise((resolve) => {
                    const verificarDados = () => {
                        const inputHidden = this.$el.querySelector('input[name="requisicao_id"]');
                        if (inputHidden && inputHidden.value && inputHidden.value !== '') {
                            resolve(inputHidden.value);
                            return;
                        }

                        // Se não encontrou, tenta novamente em 100ms
                        setTimeout(verificarDados, 100);
                    };

                    verificarDados();

                    // Timeout após 5 segundos
                    setTimeout(() => {
                        resolve(null);
                    }, 5000);
                });
            },

            async obterRequisicaoIdComEspera() {
                // Primeira tentativa imediata
                let id = this.obterRequisicaoId();

                if (!id) {
                    // Aguardar dados serem carregados
                    id = await this.aguardarDadosModal();
                }

                if (!id) {
                    console.error('❌ Timeout: ID não foi carregado nos dados do modal');
                }

                return id;
            },

            init() {
                // Escutar evento customizado do modal
                this.$el.addEventListener('set-requisicao-id', (event) => {
                    if (event.detail && event.detail.id) {
                        this.requisicaoId = event.detail.id;
                    }
                });

                // Também escutar mudanças no input hidden
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                            const input = mutation.target;
                            if (input.name === 'requisicao_id' && input.value) {
                                this.requisicaoId = input.value;
                            }
                        }
                    });
                });

                // Observar mudanças no input hidden
                const inputHidden = this.$el.querySelector('input[name="requisicao_id"]');
                if (inputHidden) {
                    observer.observe(inputHidden, {
                        attributes: true,
                        attributeFilter: ['value']
                    });
                }
            },

            // Mostrar feedback
            mostrarFeedback(tipo, mensagem) {
                this.feedback.tipo = tipo;
                this.feedback.mensagem = mensagem;
                this.feedback.mostrar = true;

                // Esconder após 5 segundos
                setTimeout(() => {
                    this.feedback.mostrar = false;
                }, 5000);
            }
        }
    }
</script>
