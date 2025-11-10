    <script>
        function pedidoForm() {
            return {
                @if (isset($pedido))
                    itens: {!! json_encode(
                        $pedido->itens->map(function ($item) {
                            return [
                                'id' => $item->id_item_pedido,
                                'id_item_solicitacao' => $item->id_item_solicitacao,
                                'descricao' => $item->descricao,
                                'tipo' => $item->tipo,
                                'quantidade' => $item->quantidade,
                                'unidade_medida' => $item->unidade_medida,
                                'valor_unitario' => $item->valor_unitario,
                            ];
                        }),
                    ) !!},
                    valorTotal: {{ $pedido->valor_total }},
                @else
                    itens: [],
                    valorTotal: 0,
                @endif
                itensSolicitacao: [],
                modalAberto: false,
                itemAtual: {
                    @if (isset($pedido))
                        id: '',
                    @endif
                    id_item_solicitacao: '',
                    descricao: '',
                    tipo: 'produto',
                    quantidade: '',
                    quantidade_solicitada: '',
                    unidade_medida: '',
                    valor_unitario: '',
                    observacao_edicao: ''
                },
                itemEditIndex: null,
                attemptedSubmit: false,
                // Nova estrutura para seleção múltipla
                itensSelecionados: {},
                valorTotalModal: 0,

                @if (isset($pedido))
                    // Carregar itens da solicitação (para edição)
                    carregarItensSolicitacao() {
                            fetch(`/admin/compras/api/solicitacoes/single/{{ $pedido->id_solicitacoes_compras }}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.itensSolicitacao = data.itens || [];
                                    console.log('Itens da solicitação carregados:', this.itensSolicitacao);
                                })
                                .catch(error => console.error('Erro ao carregar itens da solicitação:', error));
                        },
                @endif

                // Carregar dados da solicitação selecionada
                carregarSolicitacao(idSolicitacao) {
                    if (!idSolicitacao) return;

                    fetch(`/admin/compras/api/solicitacoes/single/${idSolicitacao}`)
                        .then(response => response.json())
                        .then(data => {
                            this.itensSolicitacao = data.itens || [];
                            console.log('Itens da solicitação carregados:', this.itensSolicitacao);
                        })
                        .catch(error => console.error('Erro ao carregar solicitação:', error));
                },

                // Selecionar item da solicitação
                selecionarItemSolicitacao() {
                    const itemSolicitacaoId = this.itemAtual.id_item_solicitacao;
                    if (!itemSolicitacaoId) return;

                    const itemSolicitacao = this.itensSolicitacao.find(item => item.id_item_solicitacao ==
                        itemSolicitacaoId);
                    if (itemSolicitacao) {
                        this.itemAtual.descricao = itemSolicitacao.descricao;
                        this.itemAtual.tipo = itemSolicitacao.tipo;
                        this.itemAtual.unidade_medida = itemSolicitacao.unidade_medida;
                        this.itemAtual.quantidade_solicitada = itemSolicitacao.quantidade; // Armazenar quantidade original
                        // Não preenchemos quantidade e valor_unitario automaticamente
                    }
                },

                // Editar item existente
                editarItem(index) {
                    this.itemEditIndex = index;
                    const item = this.itens[index];
                    this.itemAtual = {
                        @if (isset($pedido))
                            id: item.id || '',
                        @endif
                        id_item_solicitacao: item.id_item_solicitacao,
                        descricao: item.descricao,
                        tipo: item.tipo,
                        quantidade: item.quantidade,
                        quantidade_solicitada: item.quantidade_solicitada || '',
                        unidade_medida: item.unidade_medida,
                        valor_unitario: this.formatarValorMonetario(item.valor_unitario *
                            100), // Multiplica por 100 para a formatação
                        observacao_edicao: item.observacao_edicao || ''
                    };
                    this.modalAberto = true;
                },

                // Remover item
                removerItem(index) {
                    if (confirm('Tem certeza que deseja remover este item?')) {
                        this.itens.splice(index, 1);
                        this.calcularValorTotal();
                    }
                },

                // Salvar item atual
                salvarItem() {
                    if (!this.validarItem()) return;

                    const itemParaSalvar = {
                        @if (isset($pedido))
                            id: this.itemAtual.id,
                        @endif
                        id_item_solicitacao: this.itemAtual.id_item_solicitacao,
                        descricao: this.itemAtual.descricao,
                        tipo: this.itemAtual.tipo,
                        quantidade: parseFloat(this.itemAtual.quantidade),
                        quantidade_solicitada: parseFloat(this.itemAtual.quantidade_solicitada) || 0,
                        unidade_medida: this.itemAtual.unidade_medida,
                        valor_unitario: this.converterValorParaNumero(this.itemAtual.valor_unitario),
                        observacao_edicao: this.itemAtual.observacao_edicao || ''
                    };

                    if (this.itemEditIndex !== null) {
                        // Atualizando item existente
                        this.itens[this.itemEditIndex] = itemParaSalvar;
                    } else {
                        // Adicionando novo item
                        this.itens.push(itemParaSalvar);
                    }

                    this.calcularValorTotal();
                    this.resetarItemAtual();
                    this.modalAberto = false;
                },

                // === NOVAS FUNÇÕES PARA SELEÇÃO MÚLTIPLA ===

                // Inicializar itens selecionados no modal
                inicializarModalSelecao() {
                    this.itensSelecionados = {};
                    this.valorTotalModal = 0;

                    // Para cada item disponível, inicializa com dados vazios
                    this.itensDisponiveis.forEach(item => {
                        this.itensSelecionados[item.id_item_solicitacao] = {
                            selecionado: false,
                            quantidade: item.quantidade || '', // Usar quantidade da solicitação como padrão
                            unidade_medida: item.unidade_medida || '',
                            valor_unitario: '0,00',
                            subtotal: 0
                        };
                    });
                },

                // Alternar seleção de um item
                toggleSelecaoItem(idItem) {
                    if (this.itensSelecionados[idItem]) {
                        this.itensSelecionados[idItem].selecionado = !this.itensSelecionados[idItem].selecionado;

                        // Se deselecionar, limpa os campos
                        if (!this.itensSelecionados[idItem].selecionado) {
                            this.itensSelecionados[idItem].quantidade = '';
                            this.itensSelecionados[idItem].valor_unitario = '0,00';
                            this.itensSelecionados[idItem].subtotal = 0;
                        }

                        this.calcularValorTotalModal();
                    }
                },

                // Atualizar campos de um item selecionado
                atualizarCampoItem(idItem, campo, valor) {
                    if (this.itensSelecionados[idItem] && this.itensSelecionados[idItem].selecionado) {
                        if (campo === 'valor_unitario') {
                            this.itensSelecionados[idItem][campo] = this.formatarValorMonetario(valor);
                        } else {
                            this.itensSelecionados[idItem][campo] = valor;
                        }

                        // Calcular subtotal
                        this.calcularSubtotalItem(idItem);
                        this.calcularValorTotalModal();
                    }
                },

                // Calcular subtotal de um item
                calcularSubtotalItem(idItem) {
                    const item = this.itensSelecionados[idItem];
                    if (item && item.selecionado) {
                        const quantidade = parseFloat(item.quantidade) || 0;
                        const valorUnitario = this.converterValorParaNumero(item.valor_unitario) || 0;
                        item.subtotal = quantidade * valorUnitario;
                    }
                },

                // Calcular valor total do modal
                calcularValorTotalModal() {
                    this.valorTotalModal = Object.values(this.itensSelecionados)
                        .filter(item => item.selecionado)
                        .reduce((total, item) => total + (item.subtotal || 0), 0);
                },

                // Validar itens selecionados
                validarItensSelecionados() {
                    const itensSelecionados = Object.entries(this.itensSelecionados)
                        .filter(([id, item]) => item.selecionado);

                    if (itensSelecionados.length === 0) {
                        alert('Por favor, selecione pelo menos um item.');
                        return false;
                    }

                    for (const [id, item] of itensSelecionados) {
                        if (!item.quantidade || parseFloat(item.quantidade) <= 0) {
                            alert('Por favor, informe a quantidade para todos os itens selecionados.');
                            return false;
                        }

                        if (!item.unidade_medida) {
                            alert('Por favor, informe a unidade de medida para todos os itens selecionados.');
                            return false;
                        }

                        const valor = this.converterValorParaNumero(item.valor_unitario);
                        if (!valor || valor <= 0) {
                            alert('Por favor, informe o valor unitário para todos os itens selecionados.');
                            return false;
                        }
                    }

                    return true;
                },


                // Abrir modal com inicialização
                abrirModalSelecao() {
                    this.modalAberto = true;
                    this.itemEditIndex = null;
                    this.$nextTick(() => {
                        this.inicializarModalSelecao();
                    });
                },

                // Selecionar/deselecionar todos os itens
                selecionarTodos(selecionado) {
                    Object.keys(this.itensSelecionados).forEach(idItem => {
                        if (this.itensSelecionados[idItem]) {
                            this.itensSelecionados[idItem].selecionado = selecionado;

                            if (!selecionado) {
                                this.itensSelecionados[idItem].quantidade = '';
                                this.itensSelecionados[idItem].valor_unitario = '0,00';
                                this.itensSelecionados[idItem].subtotal = 0;
                            }
                        }
                    });

                    this.calcularValorTotalModal();
                },

                // Validar o item atual
                validarItem() {
                    if (!this.itemAtual.id_item_solicitacao) {
                        alert('Por favor, selecione um item da solicitação.');
                        return false;
                    }

                    if (!this.itemAtual.descricao) {
                        alert('Por favor, forneça uma descrição para o item.');
                        return false;
                    }

                    if (!this.itemAtual.quantidade || parseFloat(this.itemAtual.quantidade) <= 0) {
                        alert('Por favor, forneça uma quantidade válida.');
                        return false;
                    }

                    if (!this.itemAtual.unidade_medida) {
                        alert('Por favor, forneça uma unidade de medida.');
                        return false;
                    }

                    const valorNumerico = this.converterValorParaNumero(this.itemAtual.valor_unitario);
                    if (!this.itemAtual.valor_unitario || valorNumerico <= 0) {
                        alert('Por favor, forneça um valor unitário válido.');
                        return false;
                    }

                    // Validar divergência de quantidade
                    if (this.itemAtual.quantidade_solicitada &&
                        parseFloat(this.itemAtual.quantidade) !== parseFloat(this.itemAtual.quantidade_solicitada)) {

                        if (!this.itemAtual.observacao_edicao || this.itemAtual.observacao_edicao.trim() === '') {
                            const justificativa = prompt(
                                `A quantidade digitada (${this.itemAtual.quantidade}) é diferente da quantidade solicitada (${this.itemAtual.quantidade_solicitada}).\n\n` +
                                'Por favor, informe a justificativa para esta alteração:'
                            );

                            if (!justificativa || justificativa.trim() === '') {
                                alert('É obrigatório informar uma justificativa quando a quantidade for alterada.');
                                return false;
                            }

                            this.itemAtual.observacao_edicao = justificativa.trim();
                        }
                    } else {
                        // Se as quantidades são iguais, limpar a observação
                        this.itemAtual.observacao_edicao = '';
                    }

                    return true;
                },

                // Resetar o item atual
                resetarItemAtual() {
                    this.itemAtual = {
                        @if (isset($pedido))
                            id: '',
                        @endif
                        id_item_solicitacao: '',
                        descricao: '',
                        tipo: 'produto',
                        quantidade: '',
                        quantidade_solicitada: '',
                        unidade_medida: '',
                        valor_unitario: '0,00',
                        observacao_edicao: ''
                    };
                    this.itemEditIndex = null;
                },

                // Calcular o valor total do pedido
                calcularValorTotal() {
                    this.valorTotal = this.itens.reduce((total, item) => {
                        return total + (item.quantidade * item.valor_unitario);
                    }, 0);
                },

                // Validar o formulário antes de enviar
                validarFormulario(e) {
                    if (this.itens.length === 0) {
                        e.preventDefault();
                        this.attemptedSubmit = true;
                        window.scrollTo(0, document.querySelector('.bg-yellow-100').offsetTop - 100);
                        return false;
                    }

                    return true;
                },

                // Limpar todo o formulário
                limparFormulario() {
                    if (confirm('Tem certeza que deseja limpar todos os dados do formulário?')) {
                        this.itens = [];
                        this.valorTotal = 0;
                        this.resetarItemAtual();
                        document.getElementById('pedidoForm').reset();
                    }
                },

                // Formatar um número para exibição
                formatarNumero(numero) {
                    return parseFloat(numero).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                // Formatar um valor monetário para exibição
                formatarMoeda(valor) {
                    return parseFloat(valor).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                },

                // Obter itens disponíveis para seleção (não adicionados ainda)
                get itensDisponiveis() {
                    if (this.itemEditIndex !== null) {
                        // Se está editando, permite selecionar o item atual novamente
                        const itemAtualId = this.itens[this.itemEditIndex].id_item_solicitacao;
                        return this.itensSolicitacao.filter(item =>
                            !this.itens.some(itemAdicionado => itemAdicionado.id_item_solicitacao == item
                                .id_item_solicitacao) ||
                            item.id_item_solicitacao == itemAtualId
                        );
                    } else {
                        // Se está adicionando novo, filtra apenas os não adicionados
                        return this.itensSolicitacao.filter(item =>
                            !this.itens.some(itemAdicionado => itemAdicionado.id_item_solicitacao == item
                                .id_item_solicitacao)
                        );
                    }
                },

                // Formatar valor monetário com máscara
                formatarValorMonetario(valor) {
                    if (!valor) return '0,00';

                    // Remove tudo que não é dígito
                    let numeroLimpo = valor.toString().replace(/\D/g, '');

                    // Se não tem nada, retorna 0,00
                    if (!numeroLimpo) return '0,00';

                    // Converte para número com 2 casas decimais
                    let numero = parseInt(numeroLimpo) / 100;

                    // Formata no padrão brasileiro
                    return numero.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                // Converter valor formatado para número
                converterValorParaNumero(valorFormatado) {
                    if (!valorFormatado) return 0;

                    return parseFloat(
                        valorFormatado.toString()
                        .replace(/\./g, '')
                        .replace(',', '.')
                    );
                },

                // Inicialização
                init() {
                    @if (isset($pedido))
                        // Se é edição, carrega os itens da solicitação
                        this.carregarItensSolicitacao();
                    @endif

                    // Se já tiver um id de solicitação, carrega seus itens
                    const solicitacaoSelect = document.querySelector('[name="id_solicitacoes_compras"]');
                    if (solicitacaoSelect && solicitacaoSelect.value) {
                        console.log('Carregando solicitação com ID:', solicitacaoSelect.value);
                        this.carregarSolicitacao(solicitacaoSelect.value);
                    }

                    console.log('Pedido Formulário inicializado');

                    // Ouvir eventos de seleção da solicitação
                    window.addEventListener('id_solicitacoes_compras:selected', (event) => {
                        console.log('Evento de seleção de solicitação recebido:', event);
                        const detail = event.detail;
                        if (detail && detail.value) {
                            this.carregarSolicitacao(detail.value);
                        }
                    });

                    // Carregar eventos dos elementos do formulário
                    window.carregarSolicitacao = this.carregarSolicitacao.bind(this);
                }
            };
        }
    </script>
