<script>
    // Callback global para atualização de dados do veículo
    function atualizarDadosVeiculoCallback(idVeiculo, option) {
        if (window.ipvaFormComponent) {
            window.ipvaFormComponent.atualizarDadosVeiculo(idVeiculo);
        }
    }

    function ipvaForm() {
        const component = {
            renavam: '{{ isset($ipvaveiculos) && isset($ipvaveiculos->veiculo) ? $ipvaveiculos->veiculo->renavam : '' }}',
            valorPrevistoIPVA: '{{ old('valor_previsto_ipva', isset($ipvaveiculos) ? $ipvaveiculos->valor_previsto_ipva ?? '0,00' : '0,00') }}',
            valorPagoIPVA: '{{ old('valor_pago_ipva', isset($ipvaveiculos) ? $ipvaveiculos->valor_pago_ipva ?? '0,00' : '0,00') }}',
            items: {!! isset($parcelas) ? json_encode($parcelas) : '[]' !!},
            novoItem: {
                id_parcelas_ipva: null,
                numero_parcela: '',
                data_vencimento: '',
                valor_parcela: '',
                data_pagamento: '',
                valor_desconto: '',
                valor_juros: '',
                valor_pagamento: ''
            },
            csrfToken: null,

            totais: {
                valorParcela: 0,
                valorDesconto: 0,
                valorJuros: 0,
                valorPagamento: 0
            },

            async atualizarDadosVeiculo(idVeiculoParam) {
                const idVeiculo = idVeiculoParam ||
                    (document.querySelector('input[name="id_veiculo"]') ? document.querySelector(
                            'input[name="id_veiculo"]').value :
                        (document.querySelector('select[name="id_veiculo"]') ? document.querySelector(
                            'select[name="id_veiculo"]').value : null));
                const renavamInput = document.getElementById('renavam');

                if (!idVeiculo) {
                    if (renavamInput) renavamInput.value = '';
                    this.renavam = '';
                    this.id_filial_veiculo = '';
                    return;
                }
                if (!this.csrfToken) {
                    console.error('CSRF Token não disponível em atualizarDadosVeiculo.');
                    if (renavamInput) renavamInput.value = 'Erro de Config.';
                    this.renavam = 'Erro de Config.';
                    return;
                }

                try {
                    const response = await fetch(`/admin/api/veiculos/${idVeiculo}/dados-renavam`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Erro na resposta da API: ' + response.status);
                    }
                    const data = await response.json();

                    const renavamValue = data.renavam || 'N/A';
                    const idFilialVeiculoValue = data.filial_veiculo || 'N/A';
                    if (renavamInput) {
                        renavamInput.value = renavamValue;
                    }
                    if (idFilialVeiculo) {
                        idFilialVeiculo.value = idFilialVeiculoValue;
                    }
                    this.id_filial_veiculo = idFilialVeiculoValue;
                    this.renavam = renavamValue;

                } catch (error) {
                    console.error('Erro ao buscar dados do veículo:', error);
                    if (renavamInput) {
                        renavamInput.value = 'Erro ao buscar';
                    }
                    this.renavam = 'Erro ao buscar';
                }
            },

            // Nova função para calcular os totais da tabela
            calcularTotais() {
                // Reinicia os totais
                this.totais.valorParcela = 0;
                this.totais.valorDesconto = 0;
                this.totais.valorJuros = 0;
                this.totais.valorPagamento = 0;

                // Percorre todos os itens para calcular os totais
                this.items.forEach(item => {
                    this.totais.valorParcela += this.converterParaNumero(item.valor_parcela);
                    this.totais.valorDesconto += this.converterParaNumero(item.valor_desconto);
                    this.totais.valorJuros += this.converterParaNumero(item.valor_juros);
                    this.totais.valorPagamento += this.converterParaNumero(item.valor_pagamento);
                });

                // Atualiza a linha de totais no DOM, se existir
                this.atualizarLinhaTotais();

                return this.totais;
            },

            // Função para atualizar a linha de totais na tabela
            atualizarLinhaTotais() {
                // Seleciona a linha de totais existente
                let linhaTotais = document.querySelector('tr.bg-gray-100.border-b.font-medium');

                // Se não encontrar, não faz nada pois o HTML já está pronto na estrutura
                if (!linhaTotais) return;

                // Atualiza apenas os valores nas células correspondentes
                const celulas = linhaTotais.querySelectorAll('td');

                // Valor Desconto (quinta célula, índice 4)
                if (celulas.length > 4) {
                    celulas[4].textContent = this.formatarMoeda(this.totais.valorDesconto);
                }

                // Valor Juros (sexta célula, índice 5)
                if (celulas.length > 5) {
                    celulas[5].textContent = this.formatarMoeda(this.totais.valorJuros);
                }

            },

            // Função para formatar valores monetários enquanto o usuário digita
            // Converte automaticamente 10000 para 100,00
            formatarInputMonetario(event, campo) {
                let valor = event.target.value;

                // Remove todos os caracteres não numéricos
                valor = valor.replace(/\D/g, '');

                // Se não houver valor, define como vazio
                if (valor === '') {
                    this.novoItem[campo] = '';
                    return;
                }

                // Converte para número e divide por 100 para ter os centavos
                const numero = parseInt(valor, 10) / 100;

                // Formata para o padrão brasileiro: 1.234,56
                const formatado = numero.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Atualiza o valor no modelo
                this.novoItem[campo] = formatado;

                // Se estamos atualizando valor_parcela ou valor_pagamento e ambos têm valores,
                // recalculamos desconto/juros
                if ((campo === 'valor_parcela' || campo === 'valor_pagamento') &&
                    this.novoItem.valor_parcela && this.novoItem.valor_pagamento) {
                    this.calcularValorPagamento();
                }
            },

            // Também formatamos os inputs principais do formulário
            formatarInputMonetarioGlobal(event, campo) {
                let valor = event.target.value;

                // Remove todos os caracteres não numéricos
                valor = valor.replace(/\D/g, '');

                // Se não houver valor, define como vazio
                if (valor === '') {
                    if (campo === 'valor_previsto_ipva') {
                        this.valorPrevistoIPVA = '';
                    } else if (campo === 'valor_pago_ipva') {
                        this.valorPagoIPVA = '';
                    }
                    return;
                }

                // Converte para número e divide por 100 para ter os centavos
                const numero = parseInt(valor, 10) / 100;

                // Formata para o padrão brasileiro: 1.234,56
                const formatado = numero.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Atualiza o valor - REMOVIDO o cálculo automático
                if (campo === 'valor_previsto_ipva') {
                    this.valorPrevistoIPVA = formatado;
                } else if (campo === 'valor_pago_ipva') {
                    this.valorPagoIPVA = formatado;
                }
            },

            async atualizarDadosVeiculo(idVeiculoParam) {
                const idVeiculo = idVeiculoParam ||
                    (document.querySelector('input[name="id_veiculo"]') ? document.querySelector(
                            'input[name="id_veiculo"]').value :
                        (document.querySelector('select[name="id_veiculo"]') ? document.querySelector(
                            'select[name="id_veiculo"]').value : null));
                const renavamInput = document.getElementById('renavam');
                const idFilialVeiculo = document.querySelector('input[name="id_filial_veiculo"]');


                if (!idVeiculo) {
                    if (renavamInput) renavamInput.value = '';
                    this.renavam = '';
                    this.id_filial_veiculo = '';
                    return;
                }
                if (!this.csrfToken) {
                    console.error('CSRF Token não disponível em atualizarDadosVeiculo.');
                    if (renavamInput) renavamInput.value = 'Erro de Config.';
                    this.renavam = 'Erro de Config.';
                    return;
                }

                try {
                    const response = await fetch(`/admin/api/veiculos/${idVeiculo}/dados-renavam`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Erro na resposta da API: ' + response.status);
                    }
                    const data = await response.json();
                    const renavamValue = data.renavam || 'N/A';
                    const idFilialVeiculoValue = data.filial_veiculo || 'N/A';
                    if (renavamInput) {
                        renavamInput.value = renavamValue;
                    }
                    if (idFilialVeiculo) {
                        idFilialVeiculo.value = idFilialVeiculoValue;
                    }
                    this.id_filial_veiculo = idFilialVeiculoValue;
                    this.renavam = renavamValue;

                } catch (error) {
                    console.error('Erro ao buscar dados do veículo:', error);
                    if (renavamInput) {
                        renavamInput.value = 'Erro ao buscar';
                    }
                    this.renavam = 'Erro ao buscar';
                }
            },

            // Convertendo string formatada para número para cálculos
            converterParaNumero(valorString) {
                if (!valorString) return 0;

                // Se já for número, retorna o mesmo
                if (typeof valorString === 'number') return valorString;

                // Remove qualquer não-dígito exceto vírgula ou ponto
                valorString = valorString.replace(/[^\d,.]/g, '');

                // Substitui vírgula por ponto para cálculos
                valorString = valorString.replace(/\./g, '').replace(',', '.');

                return parseFloat(valorString) || 0;
            },

            calcularValorPagamento() {
                // Converte os valores formatados para números
                const valorParcela = this.converterParaNumero(this.novoItem.valor_parcela);
                const valorPagamento = this.converterParaNumero(this.novoItem.valor_pagamento);

                const diferenca = valorParcela - valorPagamento;

                if (diferenca > 0) {
                    // Desconto
                    this.novoItem.valor_juros = 'R$ 0,00';
                    this.novoItem.valor_desconto = this.formatarMoeda(diferenca);
                } else if (diferenca < 0) {
                    // Juros
                    this.novoItem.valor_desconto = 'R$ 0,00';
                    this.novoItem.valor_juros = this.formatarMoeda(Math.abs(diferenca));
                } else {
                    this.novoItem.valor_desconto = 'R$ 0,00';
                    this.novoItem.valor_juros = 'R$ 0,00';
                }
            },

            adicionarItem() {
                if (!this.validarItem()) return;
                this.items.push({
                    ...this.novoItem
                });
                this.limparNovoItem();
            },

            editarItem(index) {
                document.getElementById('adicionar-item').classList.add('hidden');
                const button = document.getElementById('atualizar-item');
                button.classList.remove('hidden');
                button.setAttribute("data-id", index);
                this.novoItem = {
                    ...this.items[index]
                };
            },

            atualizarItem() {
                document.getElementById('adicionar-item').classList.remove('hidden');
                const button = document.getElementById('atualizar-item');
                button.classList.add('hidden');
                const index = Number(button.getAttribute("data-id"));
                this.items[index] = {
                    ...this.novoItem
                };
                this.limparNovoItem();
            },

            removerItem(index) {
                if (!confirm('Deseja realmente remover esta parcela?')) return;
                this.items.splice(index, 1);
            },

            limparNovoItem() {
                this.novoItem = {
                    id_parcelas_ipva: null,
                    numero_parcela: '',
                    data_vencimento: '',
                    valor_parcela: '',
                    data_pagamento: '',
                    valor_desconto: '',
                    valor_juros: '',
                    valor_pagamento: ''
                };
            },

            limparFormulario() {
                if (!confirm('Deseja realmente limpar todos os dados do formulário?')) return;
                this.items = [];
                this.limparNovoItem();
                this.renavam = '';
                this.id_filial_veiculo = '';
                this.valorPrevistoIPVA = '';
                this.valorPagoIPVA = '';
                const renavamInput = document.getElementById('renavam');
                if (renavamInput) renavamInput.value = '';
                const valorPrevistoInput = document.querySelector('input[name="valor_previsto_ipva"]');
                if (valorPrevistoInput) valorPrevistoInput.value = '';
                const valorPagoInput = document.querySelector('input[name="valor_pago_ipva"]');
                if (valorPagoInput) valorPagoInput.value = '';
                const ipvaFormElement = document.getElementById('ipvaForm');
                if (ipvaFormElement) ipvaFormElement.reset();
            },

            validarItem() {
                const campos = ['numero_parcela', 'data_vencimento', 'valor_parcela'];
                for (const campo of campos) {
                    if (!this.novoItem[campo]) {
                        alert(`O campo ${campo.replace('_', ' ')} é obrigatório`);
                        return false;
                    }
                }
                const parcelaExistente = this.items.find(item =>
                    item.numero_parcela === this.novoItem.numero_parcela &&
                    item.id_parcelas_ipva !== this.novoItem.id_parcelas_ipva
                );
                if (parcelaExistente && !this.novoItem.id_parcelas_ipva) {
                    alert(`Já existe uma parcela com o número ${this.novoItem.numero_parcela}`);
                    return false;
                }
                return true;
            },

            formatarData(data) {
                if (!data) return '';
                if (/^\d{4}-\d{2}-\d{2}$/.test(data)) {
                    const [ano, mes, dia] = data.split('-');
                    return `${dia}/${mes}/${ano}`;
                }
                try {
                    const dataObj = new Date(data);
                    if (isNaN(dataObj.getTime())) return data;
                    return dataObj.toLocaleDateString('pt-BR', {
                        timeZone: 'UTC'
                    });
                } catch (e) {
                    return data;
                }
            },

            // Função para formatar valores na tabela
            formatarValorExibicao(valor) {
                if (!valor) return 'R$ 0,00';

                // Se já começa com R$, retorna como está
                if (typeof valor === 'string' && valor.trim().startsWith('R$')) {
                    return valor;
                }

                // Converte para número e formata
                const valorNumerico = this.converterParaNumero(valor);
                return this.formatarMoeda(valorNumerico);
            },

            formatarMoeda(valor) {
                const formatoMoeda = {
                    style: 'currency',
                    currency: 'BRL'
                };
                return valor.toLocaleString('pt-BR', formatoMoeda);
            },

        };

        // Salvamos a referência para uso global
        window.ipvaFormComponent = component;

        // Pegamos o token CSRF após o carregamento do DOM
        document.addEventListener('DOMContentLoaded', function() {
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                component.csrfToken = metaToken.getAttribute('content');
            }
        });

        return component;
    };
</script>

{{-- Botão de Gerar Parcelas - MODIFICADO para incluir divisão do valor --}}
<script>
    const gerarParcelasBtn = document.getElementById('gerar-parcelas');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (gerarParcelasBtn) {
        gerarParcelasBtn.addEventListener('click', async () => {
            const idIpvaVeiculoInput = document.getElementById('id_ipva_veiculo');
            const quantidadeParcelasInput = document.getElementById('quantidade_parcelas')
                .value;
            const intervaloParcelasInput = document.getElementById('intervalo_parcelas')
                .value;
            const dataBaseVencimento = document.getElementById('data_base_vencimento')
                .value;
            const dataPrimeiraParcela = document.getElementById('data_primeira_parcela')
                .value;
            const valorPagamento = document.getElementById('valor_previsto_ipva').value;

            const validations = [{
                    condition: !idIpvaVeiculoInput,
                    message: 'Campo id_ipva_veiculo não encontrado.'
                },
                {
                    condition: !idIpvaVeiculoInput.value,
                    message: 'É necessário salvar o IPVA antes de gerar parcelas.'
                },
                {
                    condition: !quantidadeParcelasInput,
                    message: 'Informe a quantidade de parcelas.'
                },
                {
                    condition: !intervaloParcelasInput,
                    message: 'Informe o intervalo de parcelas.'
                },
                {
                    condition: !dataBaseVencimento,
                    message: 'Informe a data base de vencimento.'
                },
                {
                    condition: !dataPrimeiraParcela,
                    message: 'Informe a data do primeiro vencimento.'
                },
                {
                    condition: dataPrimeiraParcela > dataBaseVencimento,
                    message: 'A data do primeiro vencimento deve ser menor ou igual a data base de vencimento.'
                },
                {
                    condition: !valorPagamento || valorPagamento == 0,
                    message: 'Informe o valor no campo Valor Previsto IPVA para gerar parcelas.'
                },
                {
                    condition: !csrfToken,
                    message: 'Erro de configuração: CSRF Token não encontrado para gerar parcelas.'
                }
            ];

            for (const validation of validations) {
                if (validation.condition) {
                    alert(validation.message);
                    return;
                }
            }

            const idIpvaVeiculo = idIpvaVeiculoInput.value;

            try {
                const response = await fetch('/admin/ipvaveiculos/gerar-parcelas-ipva', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        id_ipva_veiculo: idIpvaVeiculo
                    })
                });

                const data = await response.json();
                alert(data.message);

                if (data.type === 'success' || (data.notification && data.notification
                        .type === 'success') || data.title === 'Sucesso') {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Erro ao gerar parcelas:', error);
                alert('Erro ao gerar parcelas. Verifique o console para mais detalhes.');
            }
        });
    } else {
        console.warn('Botão gerar-parcelas (id=gerar-parcelas) não encontrado.');
    }
</script>

<script>
    // Variável global para armazenar o token CSRF para funções globais como excluirIpva
    let globalCsrfToken = null;

    function excluirIpva(id) {
        if (!confirm('Tem certeza que deseja desativar este registro de IPVA?')) {
            return;
        }

        const csrfTokenForExcluir = globalCsrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute(
            'content');
        if (!csrfTokenForExcluir) {
            console.error('CSRF Token não encontrado para desativar Ipva');
            alert('Erro de configuração: CSRF Token não encontrado.');
            return;
        }

        fetch(`/admin/ipvaveiculos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenForExcluir,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.notification && data.notification.type === 'success') {
                    alert(data.notification.message || 'IPVA desativado com sucesso');
                    window.location.reload();
                } else {
                    alert(data.notification?.message || 'Erro ao desativar IPVA');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao desativar IPVA');
            });
    }
</script>

{{-- Script final correto para calcular automaticamente a Data Base de Vencimento --}}
<script>
    function calcularDataBaseVencimento() {
        const quantidadeParcelas = document.getElementById('quantidade_parcelas').value;
        const intervaloParcelas = document.getElementById('intervalo_parcelas').value;
        const dataPrimeiraParcela = document.getElementById('data_primeira_parcela').value;
        const dataBaseVencimentoInput = document.getElementById('data_base_vencimento');

        // Verifica se todos os campos necessários estão preenchidos
        if (!quantidadeParcelas || !intervaloParcelas || !dataPrimeiraParcela) {
            dataBaseVencimentoInput.value = '';
            return;
        }

        // Validação básica
        if (parseInt(quantidadeParcelas) < 1) {
            dataBaseVencimentoInput.value = '';
            return;
        }

        try {
            // Converte a data da primeira parcela para objeto Date
            const [ano, mes, dia] = dataPrimeiraParcela.split('-');
            const dataInicial = new Date(parseInt(ano), parseInt(mes) - 1, parseInt(dia));

            // Verifica se a data é válida
            if (isNaN(dataInicial.getTime())) {
                dataBaseVencimentoInput.value = '';
                return;
            }

            const parcelasRestantes = parseInt(quantidadeParcelas) - 1;
            const intervalo = parseInt(intervaloParcelas);
            let dataUltimaParcela = new Date(dataInicial);
            let metodoUsado = '';

            // Escolhe o método de cálculo baseado no intervalo
            switch (intervalo) {
                case 7: // Semanal - usar dias
                    dataUltimaParcela.setTime(dataUltimaParcela.getTime() + (parcelasRestantes * 7 * 24 * 60 * 60 *
                        1000));
                    metodoUsado = 'Adição de dias (semanal)';
                    break;

                case 30: // Mensal - usar meses para maior precisão
                    dataUltimaParcela.setMonth(dataUltimaParcela.getMonth() + parcelasRestantes);
                    metodoUsado = 'Adição de meses (mensal)';
                    break;

                case 60: // Bimestral - usar meses (2 meses por parcela)
                    dataUltimaParcela.setMonth(dataUltimaParcela.getMonth() + (parcelasRestantes * 2));
                    metodoUsado = 'Adição de meses (bimestral)';
                    break;

                case 90: // Trimestral - usar meses (3 meses por parcela)
                    dataUltimaParcela.setMonth(dataUltimaParcela.getMonth() + (parcelasRestantes * 3));
                    metodoUsado = 'Adição de meses (trimestral)';
                    break;

                case 180: // Semestral - usar meses (6 meses por parcela)
                    dataUltimaParcela.setMonth(dataUltimaParcela.getMonth() + (parcelasRestantes * 6));
                    metodoUsado = 'Adição de meses (semestral)';
                    break;

                default: // Qualquer outro intervalo - usar dias
                    dataUltimaParcela.setTime(dataUltimaParcela.getTime() + (parcelasRestantes * intervalo * 24 * 60 *
                        60 * 1000));
                    metodoUsado = 'Adição de dias (personalizado)';
                    break;
            }

            // Formata a data para o formato ISO (YYYY-MM-DD) para o input date
            const anoFinal = dataUltimaParcela.getFullYear();
            const mesFinal = String(dataUltimaParcela.getMonth() + 1).padStart(2, '0');
            const diaFinal = String(dataUltimaParcela.getDate()).padStart(2, '0');

            const dataFormatada = `${anoFinal}-${mesFinal}-${diaFinal}`;

            // Define a data calculada no campo
            dataBaseVencimentoInput.value = dataFormatada;

            // Adiciona um feedback visual
            dataBaseVencimentoInput.style.backgroundColor = '#f0f9ff';
            setTimeout(() => {
                dataBaseVencimentoInput.style.backgroundColor = '';
            }, 1000);

        } catch (error) {
            console.error('Erro ao calcular data base de vencimento:', error);
            dataBaseVencimentoInput.value = '';
        }
    }

    // Integração com a inicialização do sistema
    function integrarCalculoDataBase() {
        const quantidadeParcelasInput = document.getElementById('quantidade_parcelas');
        const intervaloParcelasSelect = document.getElementById('intervalo_parcelas');
        const dataPrimeiraParcelaInput = document.getElementById('data_primeira_parcela');

        if (quantidadeParcelasInput) {
            quantidadeParcelasInput.addEventListener('input', calcularDataBaseVencimento);
            quantidadeParcelasInput.addEventListener('change', calcularDataBaseVencimento);
        }

        if (intervaloParcelasSelect) {
            intervaloParcelasSelect.addEventListener('change', calcularDataBaseVencimento);
        }

        if (dataPrimeiraParcelaInput) {
            dataPrimeiraParcelaInput.addEventListener('change', calcularDataBaseVencimento);
        }

        // Calcula na inicialização se os campos já estiverem preenchidos
        setTimeout(calcularDataBaseVencimento, 100);
    }

    // Adiciona os event listeners quando o DOM estiver carregado
    document.addEventListener('DOMContentLoaded', function() {
        integrarCalculoDataBase();
    });

    // Se o Alpine.js já estiver carregado, integra imediatamente
    if (window.Alpine) {
        integrarCalculoDataBase();
    }
</script>
