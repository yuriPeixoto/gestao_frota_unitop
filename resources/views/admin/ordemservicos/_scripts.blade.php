<script>
    // Formata o telefone por (00) 0 0000-0000
    document.getElementById("telefone_motorista").addEventListener("input", function(e) {
        let value = e.target.value.replace(/\D/g, ""); // Remove tudo que não for número
        if (value.length > 11) value = value.slice(0, 11); // Limita a 11 dígitos

        let formatted = value.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3"); // Formatação padrão
        e.target.value = formatted;
    });

    // Não permitir que a data de abertura seja menor que a data de inclusão
    document.addEventListener('DOMContentLoaded', function() {
        dataAbertura = document.getElementById('data_abertura');
        dataSaida = document.getElementById('data_previsao_saida');

        dataSaida.addEventListener('blur', function() {
            const data1 = new Date(dataAbertura.value);
            const data2 = new Date(dataSaida.value);

            if (data1 > data2) {
                alert('A data de previsão de saída deve ser maior que a data de abertura.');
                dataSaida.value = '';
            }
        });
    });


    // Validar os campos se está vazio ou não
    function validarCampos() {
        let camposVazios = [];

        // Verifica se há edição de serviço em andamento
        if (typeof verificarEdicaoEmAndamento === 'function' && !verificarEdicaoEmAndamento()) {
            return; // Bloqueia o salvamento se houver edição em andamento
        }

        const elemento = document.getElementById("Aba1");
        const placa = getSmartSelectValue('id_veiculo');

        if (elemento) { // Verifica se o elemento foi encontrado
            const camposVazios = [];

            // Seleciona todos os inputs dentro do elemento
            const campos = elemento.querySelectorAll("input, textarea, select");
            const botao = document.getElementById("btnSalvar");

            const mapeamentoNomes = {
                'data_abertura': 'Data Abertura',
                'data_previsao_saida': 'Data de Saída',
                'local_manutencao': 'Local Manutenção',
                'observacao': 'Observação',
            };

            campos.forEach(campo => {
                if (!campo.value.trim() && mapeamentoNomes[campo.name]) {
                    camposVazios.push('O campo "' + mapeamentoNomes[campo.name] +
                        '" é obrigatório.'); // Adiciona o nome do campo ao array de mensagens
                }
            });

            if (!placa.value) {
                camposVazios.push('O campo "Placa" é obrigatório.');
            }

            if (camposVazios.length > 0) {
                showModal('campos-obrigatorios-aba');

                domEl('.bw-campos-obrigatorios-aba .dados-aba').innerText = camposVazios.join(" \n");
                botao.type = "button";
            } else {
                botao.type = "submit";
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const buttons = document.querySelectorAll(".dropdown-button");

        buttons.forEach(button => {
            button.addEventListener("click", function(event) {
                event.stopPropagation();

                // Fecha todos os outros dropdowns
                document.querySelectorAll(".dropdown-menu").forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add("hidden");
                    }
                });

                // Alterna apenas o menu clicado
                this.nextElementSibling.classList.toggle("hidden");
            });
        });

        // Fecha o dropdown ao clicar fora
        document.addEventListener("click", function() {
            document.querySelectorAll(".dropdown-menu").forEach(menu => {
                menu.classList.add("hidden");
            });
        });
    });

    function openTab(evt, tabName, radio = null) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-blue-500", "text-white");
            link.classList.add("bg-gray-200", "text-gray-700");
        });

        // Mostra o conteúdo da aba atual e adiciona a classe "active" ao botão
        document.getElementById(tabName).classList.remove("hidden");
        if (radio == null) {
            evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
            evt.currentTarget.classList.add("bg-blue-500", "text-white");
        }

        // Salvar a aba ativa no localStorage
        localStorage.setItem('ordemservico_active_tab', tabName);
    }

    // Restaura a aba ativa ou mostra a primeira por padrão
    document.addEventListener("DOMContentLoaded", () => {
        const activeTab = localStorage.getItem('ordemservico_active_tab') || 'Aba1';

        // Encontrar o botão da aba ativa
        const activeButton = document.querySelector(`[onclick*="${activeTab}"]`);

        if (activeButton) {
            // Simular clique no botão da aba ativa
            activeButton.click();
        } else {
            // Fallback para primeira aba se a salva não existir
            document.querySelector(".tablink").click();
        }
    });
</script>

{{-- Ordem de Serviço Preventiva e Corretiva – Ao selecionar a opção Socorro, abrir a Aba de Socorro pra ser preenchida.
--}}
<script>
    // Variável global para armazenar o valor da operação selecionada
    var operacaoSelecionada = 1; // Valor padrão: Investimento

    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="situacao_tipo_os_corretiva"]');

        // Define a operação inicial baseada no radio button já marcado (se houver)
        const radioMarcado = document.querySelector('input[name="situacao_tipo_os_corretiva"]:checked');
        if (radioMarcado) {
            operacaoSelecionada = radioMarcado.value;
        }

        radios.forEach((radio, index) => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    // Armazena o valor na variável global
                    operacaoSelecionada = this.value;

                    processarSelecao(this.value, this.id);
                }
            });
        });
    });

    function processarSelecao(valor, id) {
        if (valor == '3') {
            openTab(event, 'Aba4', valor);
        }
    }
</script>

{{-- preencher o campo data_abertura --}}
<script>
    document.addEventListener("DOMContentLoaded", () => {
        let data_abertura = document.querySelector('input[name="data_abertura"]');
        let data_inclusao = document.querySelector('input[name="data_inclusao"]');

        // Cria um objeto Date com a data e hora atuais
        let dataAtual = new Date();

        // Formata a data no formato 'YYYY-MM-DD HH:MM:SS'
        let ano = dataAtual.getFullYear();
        let mes = String(dataAtual.getMonth() + 1).padStart(2, '0'); // Meses são de 0 a 11
        let dia = String(dataAtual.getDate()).padStart(2, '0');
        let horas = String(dataAtual.getHours()).padStart(2, '0');
        let minutos = String(dataAtual.getMinutes()).padStart(2, '0');
        let segundos = String(dataAtual.getSeconds()).padStart(2, '0');

        let dataFormatada = `${ano}-${mes}-${dia} ${horas}:${minutos}`;
        let dataFormatada2 = `${ano}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;

        // Define o valor do input com a data formatada
        if (!data_abertura.value.trim()) {
            data_abertura.value = dataFormatada;
        }

        if (!data_inclusao.value.trim()) {
            data_inclusao.value = dataFormatada2;
        }
    });
</script>

{{-- visualização de serviços --}}
<script>
    async function visualizarServicos(id) {
        showModal('vizualizar-servicos');

        // Mostra um loading na tabela enquanto aguarda os dados
        mostrarLoading();

        try {
            // Aguarda os dados serem carregados
            const servicosData = await aguardarDados();

            const servicoFiltrado = filtrarServicosPorId(servicosData, id);
            const visualizarServicosArray = formatarServicos(servicoFiltrado);

            preencherTabela(visualizarServicosArray);
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            mostrarErro();
        }
    }

    function aguardarDados(maxTentativas = 10, intervalo = 500) {
        return new Promise((resolve, reject) => {
            let tentativas = 0;

            const verificarDados = () => {
                tentativas++;

                const servicos = @json($ordemServicos ?? []);

                const servicosData = servicos.data || [];

                // Verifica se os dados estão disponíveis e não estão vazios
                if (servicosData && servicosData.length > 0) {
                    // Validação adicional da estrutura dos dados
                    const dadosValidos = servicosData.some(item =>
                        item &&
                        item.servicos &&
                        Array.isArray(item.servicos) &&
                        item.servicos.length > 0
                    );

                    if (dadosValidos) {
                        resolve(servicosData);
                        return;
                    } else {
                        console.warn('Dados carregados mas estrutura inválida, tentativa:', tentativas);
                    }
                } else {
                    console.warn(`Tentativa ${tentativas}: Dados ainda não disponíveis`);
                }

                // Se ainda não carregou e não excedeu o máximo de tentativas
                if (tentativas < maxTentativas) {
                    setTimeout(verificarDados, intervalo);
                } else {
                    console.warn(
                        'Dados não foram carregados após todas as tentativas. Retornando dados disponíveis...'
                    );
                    // Retorna os dados mesmo que não estejam completamente válidos
                    resolve(servicosData);
                }
            };

            verificarDados();
        });
    }

    function mostrarLoading() {
        const tabelaBody = document.getElementById('tabelaBody');
        tabelaBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <div style="border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite;"></div>
                        <span>Carregando dados...</span>
                    </div>
                </td>
            </tr>
        `;

        // Adiciona a animação CSS se não existir
        if (!document.getElementById('loading-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-styles';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    function mostrarErro() {
        const tabelaBody = document.getElementById('tabelaBody');
        tabelaBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #e74c3c;">
                    <span>Erro ao carregar os dados. Tente novamente.</span>
                </td>
            </tr>
        `;
    }

    function filtrarServicosPorId(servicos, idServico) {
        // Filtra pelo id_ordem_servico que aparece nos dados
        return servicos.filter(servico => servico.id_ordem_servico == idServico);
    }

    function formatarData(dataStr) {
        // Remove o 'Z' do final se existir e substitui por 'T' se necessário
        const dateStr = dataStr.endsWith('Z') ? dataStr.slice(0, -1) : dataStr;
        const data = new Date(dateStr.replace(' ', 'T'));

        return data.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    }

    function formatarServicos(servicos) {

        const todosServicos = [];

        servicos.forEach(ordemServico => {
            // Validações robustas da estrutura
            if (!ordemServico) {
                console.warn('Ordem de serviço é null ou undefined');
                return;
            }

            if (!ordemServico.servicos || !Array.isArray(ordemServico.servicos) || ordemServico.servicos
                .length === 0) {
                console.warn('ordemServico.servicos não existe, não é array ou está vazio:', ordemServico
                    .servicos);
                return;
            }

            // Agora itera sobre TODOS os serviços da ordem, não apenas o primeiro
            ordemServico.servicos.forEach(servico => {
                if (!servico) {
                    console.warn('Serviço é undefined');
                    return;
                }

                const servicoFormatado = {
                    dataInclusao: ordemServico["data_inclusao"] ? formatarData(ordemServico[
                        "data_inclusao"]) : 'N/A',
                    idFornecedor: servico.fornecedor?.nome_fornecedor || 'N/A',
                    idManutencao: servico.manutencao?.descricao_manutencao || 'N/A',
                    idServico: servico.servicos?.descricao_servico || 'N/A',
                    quantidadeServico: servico.quantidade_servico || 0,
                    valorTotal: servico.valor_total || 0,
                    valorComDesconto: formatarValorComDesconto(servico.valor_total_com_desconto ||
                        0),
                    finalizado: servico.finalizado ? 'SIM' : 'NÃO',
                    numNFServico: servico.numero_nota_fiscal_servicos || 'N/A',
                    status: servico.status_servico || 'N/A'
                };

                todosServicos.push(servicoFormatado);
            });
        });

        return todosServicos;
    }

    function criarServicoVazio() {
        return {
            dataInclusao: 'N/A',
            idFornecedor: 'N/A',
            idManutencao: 'N/A',
            idServico: 'N/A',
            quantidadeServico: 0,
            valorTotal: 0,
            valorComDesconto: '0,00',
            finalizado: 'N/A',
            numNFServico: 'N/A',
            status: 'N/A'
        };
    }

    function formatarValorComDesconto(valor) {
        return (parseFloat(valor) || 0).toFixed(2).replace('.', ',');
    }

    function preencherTabela(visualizarServicosArray) {
        const tabelaBody = document.getElementById('tabelaBody');
        tabelaBody.innerHTML = '';

        if (visualizarServicosArray.length === 0) {
            tabelaBody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #666;">
                        <span>Nenhum serviço encontrado para este ID.</span>
                    </td>
                </tr>
            `;
            return;
        }

        visualizarServicosArray.forEach(servico => {
            const row = criarLinhaTabela(servico);
            tabelaBody.appendChild(row);
        });
    }

    function criarLinhaTabela(servico) {
        const row = document.createElement("tr");

        row.innerHTML = `
            <x-tables.cell><b>${servico.dataInclusao}</b></x-tables.cell>
            <x-tables.cell><b>${servico.idFornecedor}</b></x-tables.cell>
            <x-tables.cell><b>${servico.idManutencao}</b></x-tables.cell>
            <x-tables.cell><b>${servico.idServico}</b></x-tables.cell>
            <x-tables.cell><b>${servico.quantidadeServico}</b></x-tables.cell>
            <x-tables.cell><b>${servico.valorTotal}</b></x-tables.cell>
            <x-tables.cell><b>${servico.valorComDesconto}</b></x-tables.cell>
            <x-tables.cell><b>${servico.finalizado}</b></x-tables.cell>
            <x-tables.cell><b>${servico.numNFServico}</b></x-tables.cell>
            <x-tables.cell><b>${servico.status}</b></x-tables.cell>
        `;

        return row;
    }
</script>

{{-- delete ordem de servico e imprimir os --}}
<script>
    function destroyOrdemServico(id) {
        const confirmExclusao = confirm(`Deseja excluir a Ordem de Serviço de Código ${id}?`);

        if (!confirmExclusao) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/admin/ordemservicos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(errorText => {
                        throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    showNotification(
                        data.message
                    );

                    // Recarrega a página após exclusão bem-sucedida
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(
                    'Erro',
                    error.message,
                    'error'
                );
            });
    }

    function imprimirOS(id) {
        if (id == 0) {
            return alert('Salve a O.S antes de imprimir a Ordem de Serviço');
        }

        fetch(`/admin/ordemservicos/imprimir/${id}`)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = 'relatorio.pdf';
                a.click();
                window.URL.revokeObjectURL(url);
                modalImprimir.classList.add('hidden');
            })
            .catch(error => {
                console.error('Erro ao buscar os dados:', error);
            });
    }
</script>

{{-- nova API para pegar chassi, filial e km --}}
<script>
    // Transforma a condição do Blade em variável JS

    document.addEventListener('DOMContentLoaded', function() {
        const deveExecutarInit = {{ isset($ordemServico) ? 'true' : 'false' }};

        const chassiInput = document.getElementById('chassi');
        const filialInput = document.getElementById('id_filial_veiculo');
        const kmInput = document.getElementById('km_atual');
        const dataAbertura = document.querySelector('[name="data_abertura"]');

        // const dataObj = new Date(dataAbertura.value);
        // const ano = dataObj.getFullYear();
        // const mes = String(dataObj.getMonth() + 1).padStart(2, '0');
        // const dia = String(dataObj.getDate()).padStart(2, '0');
        // const dataFormatada = `${ano}-${mes}-${dia}`;

        async function fetchVehicleData(idVeiculo) {
            if (this.isRequesting || this.pendingRequest === idVeiculo) return;
            this.isRequesting = true;
            this.pendingRequest = idVeiculo;

            try {
                const response = await fetch(`/admin/ordemservicos/getDadosVeiculo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_veiculo: idVeiculo
                    })
                });

                const responseData = await response.json();

                if (responseData.success) {
                    chassiInput.value = responseData.chassi;
                    filialInput.value = responseData.id_filial;
                    setSmartSelectValue('id_departamento', responseData.id_departamento, {
                        createIfNotFound: true,
                        tempLabel: 'Não Encontrado'
                    });
                } else {
                    console.error('❌ Erro na API getDadosVeiculo:', responseData.message);
                }
            } catch (error) {
                console.error('❌ Falha na requisição getDadosVeiculo:', error);
            } finally {
                this.isRequesting = false;
                this.pendingRequest = null;
            }
        }

        async function fetchKmData(idVeiculo) {

            try {
                const response = await fetch(`/admin/ordemservicos/carregarKm`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_veiculo: idVeiculo,
                        data_abertura: dataAbertura.value
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('❌ Erro na API carregarKm:', errorData.message);
                    if (kmInput) kmInput.value = '0';
                    return;
                }

                const responseData = await response.json();

                if (responseData.success === false) {
                    console.error('❌ Erro na API carregarKm:', responseData.message);
                    if (kmInput) kmInput.value = '0';
                    return;
                }

                if (responseData.success === true) {
                    if (responseData.data && responseData.data.km) {
                        if (kmInput) kmInput.value = responseData.data.km;
                        console.log('✅ Quilometragem carregada com sucesso:', responseData.data.km);
                    } else {
                        console.warn('⚠️ Dados de quilometragem incompletos');
                        if (kmInput) kmInput.value = 'Dados incompletos';
                    }
                } else {
                    console.error('❌ Erro na API carregarKm:', responseData.message || 'Sem sucesso');
                    if (kmInput) kmInput.value = 'Não disponível';
                }
            } catch (error) {
                console.error('❌ Falha na requisição carregarKm:', error);
                if (kmInput) kmInput.value = 'Erro na requisição';
            }
        }


        onSmartSelectChange('id_veiculo', function(data) {
            const idVeiculo = data.value;
            fetchVehicleData(idVeiculo);
            if (!deveExecutarInit) {
                fetchKmData(idVeiculo);
            }
        });


        function init(idVeiculoInicial) {
            if (idVeiculoInicial) {
                fetchVehicleData(idVeiculoInicial);
            }
        }

        // Só executa init se o Blade mandou
        if (deveExecutarInit) {
            const idVeiculoInicial = {{ isset($ordemServico) ? $ordemServico->id_veiculo : 0 }};
            init(idVeiculoInicial);
        }
    });
</script>

{{-- funções para carregar o select do campo id_servicos de acordo com o tipo de os e buscar valor do serviço quando é
contrato --}}
<script>
    if (!window.__os_servicos_initialized) {
        window.__os_servicos_initialized = true;

        document.addEventListener('DOMContentLoaded', function() {
            const tiposOS = {
                borracharia: document.getElementById('borracharia').checked,
                programada: document.getElementById('programada').checked,
                retorno: document.getElementById('retorno').checked,
                socorro: document.getElementById('socorro').checked,
                sinistro: document.getElementById('sinistro').checked,
                investimento: document.getElementById('investimento').checked
            };

            // Adicionar listeners para atualizar o objeto ao clicar e tornar os botões mutuamente exclusivos
            const checkboxes = ['borracharia', 'programada', 'retorno', 'socorro', 'sinistro', 'investimento'];

            // Utility: habilita/desabilita todos exceto o selecionado
            function setOthersDisabled(selectedId, disabled) {
                checkboxes.forEach(otherId => {
                    if (otherId === selectedId) return;
                    const otherEl = document.getElementById(otherId);
                    const otherLabel = document.querySelector(`label[for="${otherId}"]`);
                    if (otherEl) {
                        otherEl.disabled = disabled;
                    }
                    if (otherLabel) {
                        if (disabled) {
                            otherLabel.classList.add('opacity-50', 'cursor-not-allowed');
                            otherLabel.setAttribute('aria-disabled', 'true');
                        } else {
                            otherLabel.classList.remove('opacity-50', 'cursor-not-allowed');
                            otherLabel.removeAttribute('aria-disabled');
                        }
                    }
                });
            }

            const isEditing = {{ isset($ordemServico) ? 'true' : 'false' }};

            checkboxes.forEach(id => {
                const el = document.getElementById(id);
                const tipoOS = document.getElementById('id_tipo_ordem_servico_hidden');

                if (el) {
                    // Estado inicial: se algum já estiver marcado, desabilitar os demais apenas em edição
                    if (el.checked && isEditing) {
                        setOthersDisabled(id, true);
                        inicio(el.value)
                    }

                    el.addEventListener('change', async () => {
                        if (el.checked) {
                            // Desabilita os outros apenas em edição
                            if (isEditing) {
                                setOthersDisabled(id, true);
                            }

                            if (el.value == 6) {
                                tipoOS.value = 3; // Borracharia
                                setSmartSelectValue('id_tipo_ordem_servico', 3)
                            } else {
                                tipoOS.value = 2; // Corretiva
                                setSmartSelectValue('id_tipo_ordem_servico', 2)
                            }

                            updateSmartSelectOptions('id_servicos', [], false);
                            updateSmartSelectOptions('id_produto', [], false);

                            const servicos = await atualizarServicos(el.value);
                            const produtos = await atualizarProdutos(el.value);

                            // Protege contra retorno nulo
                            if (Array.isArray(servicos)) {
                                servicos.forEach(element => {
                                    addSmartSelectOption('id_servicos', element);
                                });
                            }
                            if (Array.isArray(produtos)) {
                                produtos.forEach(element => {
                                    addSmartSelectOption('id_produto', element);
                                });
                            }
                        } else {
                            // Se desmarcou, reabilita os demais apenas em edição
                            if (isEditing) {
                                setOthersDisabled(null, false);
                            }
                            // Opcional: limpar opções
                            updateSmartSelectOptions('id_servicos', [], false);
                            updateSmartSelectOptions('id_produto', [], false);
                        }
                    });
                }
            });

            // api para carregar servicos de acordo com o tipo de os escolhido
            function atualizarServicos(operacao) {
                return fetch(`/admin/ordemservicos/getServicos`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            operacao: operacao
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.servicos) {
                            return data.servicos;
                        }
                        return null;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        return null;
                    });
            };

            // api para carregar produtos de acordo com o tipo de os escolhido
            function atualizarProdutos(operacao) {
                return fetch(`/admin/ordemservicos/getProdutos`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            operacao: operacao
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.produtos) {
                            return data.produtos;
                        }
                        return null;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        return null;
                    });
            };

            // função para carregar os serviços e produtos na edição
            async function inicio(id) {
                const servicos = await atualizarServicos(id);
                const produtos = await atualizarProdutos(id);

                // Protege contra retorno nulo
                if (Array.isArray(servicos)) {
                    servicos.forEach(element => {
                        addSmartSelectOption('id_servicos', element);
                    });
                }
                if (Array.isArray(produtos)) {
                    produtos.forEach(element => {
                        addSmartSelectOption('id_produto', element);
                    });
                }

            };

            // API para buscar valor do servico e preencher o campo servico_valor quando é contrato
            onSmartSelectChange('id_servicos', async function(servico) {
                const idVeiculo = getSmartSelectValue('id_veiculo').value;
                const idFornecedor = getSmartSelectValue('id_fornecedor').value;
                const idServico = servico.value;
                const valorServico = document.querySelector('input[name="servico_valor"]');
                const inputServicoQuantidade = document.getElementById('servico_quantidade');
                const campoTotalServicos = document.getElementById('valor_total_com_desconto');

                function parseMoedaBRservico(valor) {
                    if (!valor) return 0;
                    const valorStr = valor.toString().trim();
                    let limpo = valorStr.replace(/[R$\s]/g, '');
                    const ultimoPonto = limpo.lastIndexOf('.');
                    const ultimaVirgula = limpo.lastIndexOf(',');
                    if (ultimaVirgula > ultimoPonto) {
                        return Number(limpo.replace(/\./g, '').replace(',', '.')) || 0;
                    } else if (ultimoPonto > ultimaVirgula) {
                        return Number(limpo.replace(/,/g, '')) || 0;
                    }
                    return Number(limpo) || 0;
                }

                function calcularTotalServico() {
                    let servico_quantidade = Number(inputServicoQuantidade?.value) || 0;
                    let valorUnitServico = parseMoedaBRservico(valorServico?.value);
                    let valorServicoCalculado = servico_quantidade * valorUnitServico;
                    campoTotalServicos.value = valorServicoCalculado.toFixed(2);
                }

                inputServicoQuantidade?.addEventListener('input', calcularTotalServico);

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content');
                    const response = await fetch('/admin/ordemservicos/valorServicoxfornecedor', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            id_veiculo: idVeiculo,
                            id_fornecedor: idFornecedor,
                            id_servico: idServico
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const responseData = await response.json();

                    if (responseData.success === true) {
                        if (responseData.data) {
                            if (valorServico) {
                                valorServico.value = responseData.data.valorServico;
                                calcularTotalServico();
                            }
                        } else {
                            console.warn('⚠️ Dados não encontrados');
                        }
                    } else {
                        console.warn('⚠️ Requisição não foi bem-sucedida');
                    }
                } catch (error) {
                    console.error('❌ Falha na requisição Valor Servico:', error);
                }
            });

        });

    }
</script>

{{-- nova api para imprimir km --}}
<script>
    async function imprimirKm() {
        const idVeiculo = getSmartSelectValue('id_veiculo').value;
        if (!idVeiculo) {
            alert('Selecione um veículo antes de imprimir o KM.');
            return;
        }
        try {
            const response = await fetch(`/admin/ordemservicos/onimprimirkm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id_veiculo: idVeiculo,
                })
            });

            // Se espera um arquivo PDF, use response.blob()
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/pdf')) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.download = `relatorio_km_${idVeiculo}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
                return;
            }

            // Caso seja JSON
            const responseData = await response.json();

            if (responseData.success === true) {
                if (responseData.data) {
                    alert('Relatório gerado com sucesso!');
                } else {
                    console.warn('⚠️ Dados não encontrados');
                    alert('Dados não encontrados para o veículo selecionado.');
                }
            } else {
                console.warn('⚠️ Requisição não foi bem-sucedida');
                alert('Requisição não foi bem-sucedida.');
            }
        } catch (error) {
            console.error('❌ Falha na requisição imprimir Km:', error);
            alert('Erro ao gerar relatório de KM.');
        }
    }
</script>

{{-- api para reabertura de os --}}
<script>
    async function reabrirOrdemServico(id) {
        try {
            const response = await fetch(`/admin/ordemservicos/reabriros`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idOrdemServico: id
                })
            });

            // Caso seja JSON
            const responseData = await response.json();

            if (responseData.success === true) {
                alert(responseData.message);
            } else {
                console.warn('⚠️', responseData.message);
                alert('Não foi possível reabrir a Ordem de Servico.');
            }
        } catch (error) {
            console.error('❌ Falha ao tentar reabrir a Ordem de Servico:', error);
            alert('Erro ao tentar reabrir a Ordem de Servico.');
        }
    }
</script>

{{-- api para busca de valores de peças --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let idOrdemServico = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }};

        let idFilial = document.getElementById('id_filial_manutencao')?.value ??
            document.getElementById('id_filial')?.value ??
            0;

        const descGrupo = document.getElementById('desc_grupo');
        const qtdEstoque = document.getElementById('qtd_estoque');
        const valorUnitario = document.getElementById('valor_unitario_pecas');
        const idUnidade = document.getElementById('id_unidade');
        const descrUnidade = document.getElementById('descrUnidade');
        const campoTotalPecas = document.getElementById('valor_total_com_desconto_pecas');
        const inputQuantidade = document.getElementById('quantidade');

        function parseMoedaBR(valor) {
            if (!valor) return 0;
            return Number(valor.toString().replace(/\./g, '').replace(',', '.')) || 0;
        }

        function calcularTotal() {
            let quantidade = Number(inputQuantidade?.value) || 0;
            let valorUnit = parseMoedaBR(valorUnitario?.value);

            let valorCalculado = quantidade * valorUnit;

            campoTotalPecas.value = valorCalculado.toFixed(2);
        }

        inputQuantidade?.addEventListener('input', calcularTotal);

        onSmartSelectChange('id_produto', function(dado) {
            let idProduto = dado.value;
            let fornecedor = getSmartSelectValue('id_fornecedor-pecas');
            let idFornecedor = fornecedor ? fornecedor.value : null;

            const url = "{{ route('admin.ordemservicos.carregarUnidadeProduto') }}";

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        idFornecedor,
                        idProduto,
                        idOrdemServico,
                        idFilial
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        descGrupo.value = data.obj.desc_grupo ?? '';
                        qtdEstoque.value = data.obj.estoque_ ?? 0;

                        valorUnitario.value = data.obj.valor_pecas ?? '0,00';

                        idUnidade.value = data.obj.id_unidade ?? '';
                        descrUnidade.value = data.obj.desc_unidade ?? '';

                        calcularTotal();
                    } else {
                        console.warn('⚠️', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao obter dados:', error);
                });
        });
    });
</script>

{{-- Api para adicionar os serviços e peças --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        onSmartSelectChange('id_manutencao', async function(data) {
            const idFornecedor = getSmartSelectValue('id_fornecedor');
            const idManutencao = data.value;

            valor = document.querySelector("input[name='id_ordem_servico']");
            idOrdemServico = valor.value;

            confirmar = confirm(
                'Deseja Inserir os Serviços e Peças Vinculados à esta Manutenção?');

            if (confirmar) {
                if (idManutencao == null) {
                    return alert('Selecione uma Manutenção');
                }

                if (idFornecedor.value == null) {
                    return alert('Selecione um Fornecedor');
                }

                if (idOrdemServico == 'nada') {
                    return alert('Salve a Ordem de Serviço antes de inserir os serviços');
                }

                const dados = [{
                    id_fornecedor: idFornecedor.value,
                    id_manutencao: idManutencao,
                    id_ordem_servico: idOrdemServico
                }];

                try {
                    // Mostrar modal de loading
                    showLoadingModal('Inserindo serviços e peças...');

                    // Configure os headers corretamente
                    const headers = {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    };

                    const response = await fetch(
                        `/admin/ordemservicos/inserirServicosePecas`, {
                            method: 'POST',
                            headers: headers,
                            credentials: 'same-origin',
                            body: JSON.stringify(dados)
                        });

                    if (!response.ok) {
                        hideLoadingModal();
                        throw new Error(`Erro na resposta da API: ${response.status}`);
                    } else {
                        // Mantém o loading visível durante o reload
                        window.location.reload();
                    }

                } catch (err) {
                    hideLoadingModal();
                    console.error('Erro ao buscar dados de manutenção:', err);
                    alert('Erro ao inserir serviços e peças. Tente novamente.');
                }
            }
        });
    });
</script>

{{-- botão de solicitar servicos --}}
<script>
    function onSolicitarServicos() {
        const idOS = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 'null' }};
        const idStatusOS = {{ isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 'null' }};
        const isCancelada =
            {{ isset($ordemServico->is_cancelada) ? ($ordemServico->is_cancelada ? 'true' : 'false') : 'null' }};
        const idVeiculo = {{ isset($ordemServico) ? $ordemServico->id_veiculo : 'null' }};
        const idFilialManutencao =
            {{ isset($ordemServico) && $ordemServico->id_filial_manutencao ? $ordemServico->id_filial_manutencao : 'null' }};
        const idFilialVeiculo =
            {{ isset($ordemServico) && isset($ordemServico->veiculo) && $ordemServico->veiculo->id_filial ? $ordemServico->veiculo->id_filial : 'null' }};

        if (idOS == null) {
            return alert('Salve a O.S antes de finalizar a Ordem de Serviço');
        }

        if (idStatusOS == 13 || isCancelada == true) {
            return alert('Esta ordem de serviço ja foi cancelada');
        }

        // Mostrar modal de loading
        showLoadingModal('Solicitando serviços...');

        // Desabilitar botão que acionou a função (se existir)
        const btn = event.target;
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const url = "{{ route('admin.ordemservicos.solicitar-servicos-os') }}";

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: idOS,
                    idVeiculo: idVeiculo,
                    idFilialManutencao: idFilialManutencao,
                    idFilialVeiculo: idFilialVeiculo
                }),
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingModal();

                if (data.success) { // ✅ CORRETO: Verifica a propriedade 'success'
                    alert(data.message); // ✅ CORRETO: Mostra a mensagem
                    window.location.reload();
                } else {
                    alert(data.message); // ✅ Mensagem de erro
                    // Reabilitar botão em caso de erro
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            })
            .catch(error => {
                hideLoadingModal();
                console.error('Erro ao solicitar serviços:', error);
                alert('Erro ao processar solicitação. Tente novamente.');

                // Reabilitar botão em caso de erro
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
    }

    // Função para mostrar modal de loading
    function showLoadingModal(message = 'Processando...') {
        const modal = document.createElement('div');
        modal.id = 'loadingModal';
        modal.className = 'fixed inset-0 bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4 max-w-sm mx-4">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="text-gray-700 font-medium">${message}</p>
                <p class="text-sm text-gray-500">Por favor, aguarde...</p>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Função para esconder modal de loading
    function hideLoadingModal() {
        const modal = document.getElementById('loadingModal');
        if (modal) {
            modal.remove();
        }
    }
</script>

{{-- botão de solicitar peças --}}
<script>
    function onActionSolicitarPecas() {
        const idOS = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 'null' }};
        const id_filial_manutencao =
            {{ isset($ordemServico) && $ordemServico->id_filial_manutencao ? $ordemServico->id_filial_manutencao : 'null' }};
        const id_filial_veiculo =
            {{ isset($ordemServico) && isset($ordemServico->veiculo) && $ordemServico->veiculo->id_filial ? $ordemServico->veiculo->id_filial : 'null' }};
        const id_veiculo = {{ isset($ordemServico) ? $ordemServico->id_veiculo : 'null' }};
        const idStatusOs = {{ isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 'null' }};
        const isCancelada =
            {{ isset($ordemServico->is_cancelada) ? ($ordemServico->is_cancelada ? 'true' : 'false') : 'null' }};
        const tipoOS = {{ isset($ordemServico) ? $ordemServico->id_tipo_ordem_servico : 'null' }};

        if (idOS == null) {
            return alert('Salve a O.S antes de solicitar as peças');
        }

        if (idStatusOs == 13 || isCancelada == true) {
            return alert('Esta ordem de serviço ja foi cancelada');
        }

        const url = "{{ route('admin.ordemservicos.solicitar-pecas') }}";
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: idOS,
                    idFilialManutencao: id_filial_manutencao,
                    idFilialVeiculo: id_filial_veiculo,
                    idVeiculo: id_veiculo,
                    tipoOS: tipoOS
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao cancelar O.S.:', error);
            });
    }
</script>

{{-- API para validação do KM atual --}}
<script>
    function validaKMAtual() {
        let kmAtual = document.getElementById('km_atual').value;
        let idVeiculo = document.getElementById('id_veiculo').value;
        let dataAbertura = document.getElementById('data_abertura').value;

        if (kmAtual > 0 && idVeiculo > 0 && dataAbertura != '') {
            document.getElementById('btnSalvar').disabled = false;
        }

        const url = "{{ route('admin.ordemservicos.validarKMAtual') }}";
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    km: kmAtual,
                    veiculo: idVeiculo,
                    data: dataAbertura
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    if (!data.valid) {
                        return alert('O KM atual é menor que o do ultimo abastecimento.');
                    }
                } else {
                    return alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao cancelar O.S.:', error);
            });
    }
</script>

{{-- api para imprimir servicos e peças --}}
<script>
    async function onImprimirServPec(id) {
        if (id == 0) {
            return alert('Salve a O.S antes de imprimir a Ordem de Serviço');
        }
        try {
            const url = "{{ route('admin.ordemservicos.onImprimirServPec', ':id') }}"
                .replace(':id', id);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                // Se a resposta não for ok, tentar ler como JSON para pegar a mensagem de erro
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erro ao gerar relatório');
            }

            // Se chegou aqui, é um PDF válido
            const blob = await response.blob();

            // Criar URL do blob
            const pdfUrl = window.URL.createObjectURL(blob);

            // Criar link para download
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download =
                `rel_serv_pec_${id}_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.pdf`;

            // Simular clique
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Limpar URL do blob
            window.URL.revokeObjectURL(pdfUrl);

        } catch (error) {
            console.error('Erro ao imprimir O.S.:', error);
            alert('Erro ao gerar relatório: ' + error.message);
        }
    }
</script>

{{-- API botão cancelar --}}
<script>
    function onCancelarOS() {
        let id = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }};
        let idStatusOS = {{ isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 0 }};
        let isCancelada =
            {{ isset($ordemServico->is_cancelada) ? ($ordemServico->is_cancelada ? 'true' : 'false') : 'null' }};

        if (id == 0) {
            return alert('Salve a O.S antes de cancelar a Ordem de Servico');
        }

        if (idStatusOS == 13 || isCancelada == true) {
            return alert('Esta ordem de serviço ja foi cancelada');
        }

        const confirmacao = confirm(
            'Tem certeza que deseja cancelar esta ordem de serviço?');

        if (confirmacao) {
            cancelar = 1;
        } else {
            cancelar = 0;
        }

        const url = "{{ route('admin.ordemservicos.cancelar-os') }}";
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: id,
                    CancelarOS: cancelar
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    alert(data.message);
                    window.location.href = "{{ route('admin.ordemservicos.index') }}";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao cancelar O.S.:', error);
            });

    }
</script>

{{-- API BOTÃO ENCERRAR --}}
<script>
    function onActionEncerrar() {
        let id = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }};
        let idStatusOS = {{ isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 0 }};
        let isCancelada =
            {{ isset($ordemServico->is_cancelada) ? ($ordemServico->is_cancelada ? 'true' : 'false') : 'null' }};
        let id_filial_manutencao =
            {{ isset($ordemServico) && $ordemServico->id_filial_manutencao ? $ordemServico->id_filial_manutencao : 'null' }};
        let id_veiculo = {{ isset($ordemServico) ? $ordemServico->id_veiculo : 'null' }};


        if (id == 0) {
            return alert('Salve a O.S antes de encerrar a Ordem de Servico');
        }

        if (idStatusOS == 13 || isCancelada == true) {
            return alert('Esta ordem de serviço ja foi cancelada');
        }

        let confirma = 0;
        if (confirm('Deseja encerrar esta Ordem de Serviço?')) {
            confirma = 1;
        }

        const url = "{{ route('admin.ordemservicos.encerrar-os') }}";
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: id,
                    idFilialManutencao: id_filial_manutencao,
                    idVeiculo: id_veiculo,
                    confirma: confirma
                }),
            })
            .then(response => {

                // Primeiro pega o texto puro para ver o que está vindo
                return response.text().then(text => {

                    // Se está vazio, retorna erro
                    if (!text || text.trim() === '') {
                        throw new Error('Resposta vazia do servidor');
                    }

                    // Tenta fazer parse do JSON
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('❌ Erro ao fazer parse do JSON:', e);
                        throw new Error('Resposta inválida do servidor (não é JSON)');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = "{{ route('admin.ordemservicos.index') }}";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Erro ao processar requisição: ' + error.message);
            });
    }
</script>

{{-- API BOTÃO FINALIZAR --}}
<script>
    function onFinalizar() {
        let id = {{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }};
        let idStatusOS = {{ isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 0 }};
        let isCancelada =
            {{ isset($ordemServico->is_cancelada) ? ($ordemServico->is_cancelada ? 'true' : 'false') : 'null' }};

        if (id == 0) {
            return alert('Salve a O.S antes de finalizar a Ordem de Serviço');
        }

        if (idStatusOS == 13 || isCancelada == true) {
            return alert('Esta ordem de serviço ja foi cancelada');
        }

        const confirma_finalizar = confirm(
            'Deseja finalizar esta Ordem de Serviço?\n Após a finalização não será possível qualquer tipo de edição ou lançamento.'
        );

        finalizar = 0;
        if (confirma_finalizar) {
            finalizar = 1;
        }

        const url = "{{ route('admin.ordemservicos.finalizar-os') }}";
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: id,
                    FinalizarOS: finalizar,
                    StatusOS: idStatusOS
                }),
            })
            .then(response => {
                return response.json().then(data => ({
                    response,
                    data
                })).catch(() => {
                    return {
                        response,
                        data: {
                            message: 'Erro na resposta do servidor',
                            error: true
                        }
                    };
                });
            })
            .then(({
                response,
                data
            }) => {
                alert(data.message);
                if (response.status === 200 && !data.error) {
                    window.location.href = "{{ route('admin.ordemservicos.index') }}";
                } else {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Erro ao cancelar O.S.:', error);
            });
    }
</script>

{{-- Carregar manutenção por categoria de veiculo --}}
<script>
    // api para carregar Manutenção de acordo com a categoria do veiculo
    async function getManutencao(idVeiculo) {
        return fetch(`/admin/ordemservicos/getManutencao`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    idVeiculo: idVeiculo
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data) {
                    return data;
                }
                return null;
            })
            .catch(error => {
                console.error('Error:', error);
                return null;
            });
    };

    document.addEventListener('DOMContentLoaded', () => {

        onSmartSelectChange('id_veiculo', async function(veiculo) {

            updateSmartSelectOptions('id_manutencao', [], false);

            const idVeiculo = veiculo.value;

            const manutencoes = await getManutencao(idVeiculo);

            if (Array.isArray(manutencoes)) {
                manutencoes.forEach(element => {
                    addSmartSelectOption('id_manutencao', element);
                });
            }

        });
    });
</script>
