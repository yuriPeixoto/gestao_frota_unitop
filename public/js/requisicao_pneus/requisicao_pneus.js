document.addEventListener('DOMContentLoaded', function () {
    let registrosReqPneusTemporarios = [];
    let registrosTransfPneusItensTemporarios = [];
    let pneusSelecionadosMap = new Map();

    const ReqPneusJson = document.getElementById('requisicaoPneusModelos_json').value;
    const requisicaoPneus = JSON.parse(ReqPneusJson);

    if (requisicaoPneus && requisicaoPneus.length > 0) {
        if (requisicaoPneus && requisicaoPneus.length > 0) {
            requisicaoPneus.forEach(requisicaoPneu => {
                registrosReqPneusTemporarios.push({
                    codRequisicaoPneu: requisicaoPneu.id_requisicao_pneu_modelos,
                    data_inclusao: requisicaoPneu.data_inclusao,
                    data_alteracao: requisicaoPneu.data_alteracao,
                    modelos_requisitados: 'Cód. ' + requisicaoPneu.id_modelo_pneu + ' - ' + requisicaoPneu.modelo.descricao_modelo,
                    quantidade: requisicaoPneu.quantidade,
                    quantidade_baixa: Number(requisicaoPneu.quantidade_baixa) || 0,
                    data_baixa: requisicaoPneu.data_baixa,
                    valor_total: requisicaoPneu.valor_total || 0
                });
            });

            atualizarrequisicaoPneusTabela();
        }

        // Função auxiliar para converter número brasileiro para formato JavaScript
        function converterValorBrasileiroParaFloat(valor) {
            if (!valor) return 0;

            // Converter para string se não for
            const valorStr = String(valor).trim();

            // Se já estiver no formato americano (sem vírgula ou apenas com ponto), retornar parseFloat normal
            if (!valorStr.includes(',')) {
                return parseFloat(valorStr) || 0;
            }

            // Tratar formato brasileiro: 1.234.567,89
            // Primeiro, verificar se tem tanto ponto quanto vírgula
            if (valorStr.includes('.') && valorStr.includes(',')) {
                // Formato: 1.234.567,89 (pontos são separadores de milhares, vírgula é decimal)
                const valorLimpo = valorStr.replace(/\./g, '').replace(',', '.');
                return parseFloat(valorLimpo) || 0;
            }
            // Se tem apenas vírgula, assumir que é separador decimal
            else if (valorStr.includes(',')) {
                // Formato: 1234,89 ou 4157,64
                const valorLimpo = valorStr.replace(',', '.');
                return parseFloat(valorLimpo) || 0;
            }

            // Fallback para parseFloat normal
            return parseFloat(valorStr) || 0;
        }

        // Função auxiliar para calcular valor unitário (valor_venda)
        function calcularValorUnitario(valorTotal, quantidade) {
            const total = converterValorBrasileiroParaFloat(valorTotal);
            const qtd = parseInt(quantidade) || 1; // Evita divisão por zero
            return qtd > 0 ? (total / qtd) : 0;
        }

        function adicionarrequisicaoPneus() {
            const pneusSelecionados = registrosTransfPneusItensTemporarios.filter(pneu => pneu.selecionado);

            if (pneusSelecionados.length === 0) {
                alert('Por favor, selecione pelo menos um pneu antes de adicionar!');
                return;
            }

            const modeloPneu = document.querySelector('[name="modelo_pneu"]').value;
            const quantidade = document.querySelector('[name="quantidade"]').value;
            const valorTotal = document.querySelector('[name="valor_total"]').value;
            const dataBaixa = document.querySelector('[name="data_baixa"]').value;

            if (!modeloPneu || !quantidade) {
                alert('Por favor, preencha todos os campos!');
                return;
            }

            const quantidadeSolicitada = parseInt(quantidade) || 0;
            const quantidadeSelecionada = pneusSelecionados.length;

            if (quantidadeSelecionada > quantidadeSolicitada) {
                alert(`Erro: Você selecionou ${quantidadeSelecionada} pneus, mas a quantidade solicitada é apenas ${quantidadeSolicitada}. Por favor, ajuste a seleção.`);
                return;
            }

            const valorUnitario = calcularValorUnitario(valorTotal, quantidade);

            // Atualizar o valor_venda para cada pneu selecionado
            pneusSelecionados.forEach(pneu => {
                pneusSelecionadosMap.set(pneu.id_pneu, true);
                pneu.valor_venda = valorUnitario;
                console.log(`Pneu ${pneu.id_pneu}: valor_total (original) = "${valorTotal}", valor_total (convertido) = ${converterValorBrasileiroParaFloat(valorTotal)}, quantidade = ${quantidade}, valor_venda = ${valorUnitario}`);
            });

            // IMPORTANTE: Atualizar o campo hidden ANTES de modificar a tabela de requisições
            atualizarPneusSelecionadosHidden();

            // Encontrar ou criar o registro
            const modeloExistenteIndex = registrosReqPneusTemporarios.findIndex(
                registro => registro.modelos_requisitados === modeloPneu
            );

            if (modeloExistenteIndex !== -1) {
                const novoRegistro = {
                    ...registrosReqPneusTemporarios[modeloExistenteIndex],
                    quantidade_baixa: pneusSelecionados.length,
                    data_baixa: dataBaixa,
                    valor_total: valorTotal,
                    data_alteracao: new Date().toISOString()
                };
                registrosReqPneusTemporarios.splice(modeloExistenteIndex, 1, novoRegistro);
            } else {
                registrosReqPneusTemporarios.push({
                    modelos_requisitados: modeloPneu,
                    quantidade: quantidade,
                    quantidade_baixa: pneusSelecionados.length,
                    data_inclusao: new Date().toISOString(),
                    data_alteracao: new Date().toISOString(),
                    data_baixa: dataBaixa,
                    valor_total: valorTotal
                });
            }

            atualizarRequisicaoPneusHidden();
            atualizarrequisicaoPneusTabela();
            atualizarPneusRecebidosTabela();
            limparTabelaPneusRecebidos();
            alert('Pneus adicionados com sucesso!');
        }

        function atualizarrequisicaoPneusTabela() {
            const tbody = document.getElementById('tabelaRequisicaoModeloPneus');
            if (!tbody) return;

            tbody.innerHTML = '';

            registrosReqPneusTemporarios.forEach((registro, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarrequisicaoPneusRegistro(${index})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Selecionar
                        </button>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.codRequisicaoPneu}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarrequisicaoPneusData(registro.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarrequisicaoPneusData(registro.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.modelos_requisitados}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.quantidade}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.quantidade_baixa || 0}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.data_baixa ? formatarrequisicaoPneusData(registro.data_baixa, 'd/m/Y') : ''}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.valor_total}</td>
            `;
                tbody.appendChild(tr);
            });
        }

        function atualizarQuantidadeBaixa() {
            // Obter o modelo atual selecionado
            const modeloPneuAtual = document.querySelector('[name="modelo_pneu"]').value;
            if (!modeloPneuAtual) return;

            // Contar quantos pneus estão selecionados para cada modelo
            const modelosSelecionados = new Map();

            // Percorrer o mapa de pneus selecionados e contar por modelo
            for (const idPneu of pneusSelecionadosMap.keys()) {
                // Extrair o código do modelo do ID do pneu (exemplo: "20948 - 25 - BRID M814 215/75")
                const match = idPneu.match(/(\d+)\s+-\s+(\d+)\s+-\s+(.+)/);
                if (match) {
                    const codigoModelo = match[2]; // O código do modelo (ex: "25")
                    const descricaoModelo = match[3]; // A descrição do modelo (ex: "BRID M814 215/75")
                    const modeloCompleto = `Cód. ${codigoModelo} - ${descricaoModelo}`;

                    // Incrementar o contador para este modelo
                    modelosSelecionados.set(modeloCompleto, (modelosSelecionados.get(modeloCompleto) || 0) + 1);
                }
            }

            // Atualizar a quantidade baixa na tabela para cada modelo
            const tabela = document.querySelector('table');
            if (!tabela) return;

            const linhas = tabela.querySelectorAll('tbody tr');
            linhas.forEach(linha => {
                const celulas = linha.querySelectorAll('td');
                if (celulas.length >= 6) { // Garantir que temos células suficientes
                    const modeloCell = celulas[4]; // Coluna "MODELOS REQUISITADOS"
                    const quantidadeCell = celulas[5]; // Coluna "QUANTIDADE"
                    const quantidadeBaixaCell = celulas[6]; // Coluna "QUANTIDADE BAIXA"

                    if (modeloCell && quantidadeCell && quantidadeBaixaCell) {
                        const modeloTexto = modeloCell.textContent.trim();
                        const quantidadeTotal = parseInt(quantidadeCell.textContent.trim()) || 0;
                        const quantidadeSelecionada = modelosSelecionados.get(modeloTexto) || 0;

                        // Atualizar apenas a célula de quantidade baixa
                        quantidadeBaixaCell.textContent = `${quantidadeSelecionada} / ${quantidadeTotal}`;
                    }
                }
            });
        }

        async function editarrequisicaoPneusRegistro(index) {
            const registro = registrosReqPneusTemporarios[index];

            // Preenche os campos
            document.querySelector('[name="modelo_pneu"]').value = registro.modelos_requisitados;
            document.querySelector('[name="quantidade"]').value = registro.quantidade;

            // Corrigir o formato da data para o input date
            const dataBaixaInput = document.querySelector('[name="data_baixa"]');
            if (dataBaixaInput) {
                dataBaixaInput.value = converterDataParaInputDate(registro.data_baixa);
            }

            document.querySelector('[name="valor_total"]').value = registro.valor_total ?? 0;

            // Extrai o ID do modelo da string "Cód. X - Descrição"
            const modeloIdMatch = registro.modelos_requisitados.match(/Cód\.\s+(\d+)/);
            const modeloId = modeloIdMatch ? modeloIdMatch[1] : null;

            if (!modeloId) {
                alert('Não foi possível identificar o ID do modelo');
                return;
            }

            const transferId = document.querySelector('[name="id_requisicao_pneu"]').value;

            try {
                const response = await fetch(`/admin/requisicaopneusvendassaida/por-modelo/${transferId}/${modeloId}`);

                if (!response.ok) throw new Error('Erro ao carregar pneus');

                const resultado = await response.json();

                if (resultado.success && resultado.data && resultado.data.length > 0) {
                    // Calcular o valor unitário baseado no valor_total e quantidade
                    const valorUnitario = calcularValorUnitario(registro.valor_total, registro.quantidade);

                    registrosTransfPneusItensTemporarios = resultado.data.map(pneu => {
                        const idPneuFormatado = `${pneu.id} - ${pneu.modelo.codigo} - ${pneu.modelo.descricao.trim()} - VIDA: ${pneu.vida}`;

                        return {
                            id_pneu: idPneuFormatado,
                            codigo_modelo: pneu.codigo,
                            modelo: pneu.modelo,
                            idVida: pneu.id_vida,
                            vida: pneu.vida,
                            id_requisicao_pneu_modelos: pneu.id_requisicao_pneu_modelos,
                            valor_venda: valorUnitario, // Usar o valor unitário calculado
                            selecionado: pneu.selecionado || false
                        };
                    });

                    // Atualizar a tabela com os pneus carregados
                    atualizarPneusRecebidosTabela();
                    atualizarContagemPneusSelecionados();
                } else {
                    alert('Nenhum pneu encontrado para este modelo');
                }
            } catch (error) {
                console.error('Erro ao carregar pneus:', error);
                alert('Erro ao carregar pneus. Por favor, tente novamente.');
            }
        }

        function atualizarPneusRecebidosTabela() {
            const tbody = document.getElementById('tabelaRequisicaoPneusItens');
            if (!tbody) {
                console.error('Elemento #tabelaRequisicaoPneusItens não encontrado');
                return;
            }

            tbody.innerHTML = '';

            if (registrosTransfPneusItensTemporarios.length === 0) {
                console.log('Nenhum pneu para exibir');
                return;
            }

            registrosTransfPneusItensTemporarios.forEach((pneu, index) => {
                // Verificar se o pneu está no mapa de selecionados
                if (pneusSelecionadosMap.has(pneu.id_pneu)) {
                    pneu.selecionado = true;
                }

                const tr = document.createElement('tr');
                tr.className = pneu.selecionado ? 'bg-indigo-50' : 'hover:bg-gray-50';
                tr.innerHTML = `
            <td class="px-6 py-4">
                <input type="checkbox" id="pneu-${index}" 
                       class="pneu-checkbox rounded text-indigo-600 focus:ring-indigo-500" 
                       data-index="${index}" 
                       onchange="selecionarPneuRecebido(${index})"
                       ${pneu.selecionado ? 'checked' : ''}>
            </td>
            <td class="px-6 py-4">${pneu.id_pneu}</td>
        `;
                tbody.appendChild(tr);
            });

            verificarSelecionarTodos();
        }

        // Adicione estas funções ao objeto window
        window.selecionarPneuRecebido = function (index) {
            const checkbox = document.getElementById(`pneu-${index}`);
            if (!checkbox) return;

            const pneu = registrosTransfPneusItensTemporarios[index];

            // NOVA VALIDAÇÃO: Verificar limite antes de permitir seleção
            if (checkbox.checked) {
                const quantidadeSolicitada = parseInt(document.querySelector('[name="quantidade"]').value) || 0;
                const quantidadeAtualmenteSelecionada = registrosTransfPneusItensTemporarios.filter(p => p.selecionado).length;

                if (quantidadeAtualmenteSelecionada >= quantidadeSolicitada) {
                    alert(`Limite atingido! Você só pode selecionar ${quantidadeSolicitada} pneus para este modelo.`);
                    checkbox.checked = false;
                    return;
                }
            }

            pneu.selecionado = checkbox.checked;

            // Atualizar o mapa de pneus selecionados
            if (checkbox.checked) {
                pneusSelecionadosMap.set(pneu.id_pneu, true);

                // IMPORTANTE: Calcular o valor_venda baseado na fórmula valor_total / quantidade
                const valorTotal = document.querySelector('[name="valor_total"]').value;
                const quantidade = document.querySelector('[name="quantidade"]').value;
                const valorUnitario = calcularValorUnitario(valorTotal, quantidade);

                // Atualizar o valor_venda com o valor calculado
                pneu.valor_venda = valorUnitario;

                console.log(`Pneu selecionado: ${pneu.id_pneu}, valor_total (original) = "${valorTotal}", valor_total (convertido) = ${converterValorBrasileiroParaFloat(valorTotal)}, quantidade = ${quantidade}, valor_venda = ${valorUnitario}`);
            } else {
                pneusSelecionadosMap.delete(pneu.id_pneu);
                // Ao desselecionar, manter o valor_venda para caso seja selecionado novamente
            }

            // Atualizar a aparência da linha
            const tr = checkbox.closest('tr');
            if (tr) {
                tr.className = checkbox.checked ? 'bg-indigo-50' : 'hover:bg-gray-50';
            }

            // Atualizar apenas a quantidade baixa na tabela
            atualizarQuantidadeBaixa();
            atualizarPneusSelecionadosHidden();
            verificarSelecionarTodos();
        };

        function atualizarContagemPneusSelecionados() {
            // Obter o modelo atual selecionado
            const modeloPneuAtual = document.querySelector('[name="modelo_pneu"]').value;

            if (!modeloPneuAtual) return;

            // Contar quantos pneus estão selecionados para este modelo
            const pneusSelecionados = registrosTransfPneusItensTemporarios.filter(pneu => pneu.selecionado);
            const quantidadeSelecionada = pneusSelecionados.length;

            // Atualizar o registro correspondente
            const modeloIndex = registrosReqPneusTemporarios.findIndex(
                registro => registro.modelos_requisitados === modeloPneuAtual,
            );

            if (modeloIndex !== -1) {
                registrosReqPneusTemporarios[modeloIndex].quantidade_selecionada = quantidadeSelecionada;
            }
        }

        function atualizarPneusSelecionadosHidden() {
            const novosSelecionados = registrosTransfPneusItensTemporarios.filter(pneu => pneu.selecionado);
            const hiddenField = document.getElementById('pneusSelecionados_json');

            if (!hiddenField) {
                console.warn('Elemento pneusSelecionados_json não encontrado no DOM');
                return;
            }

            console.log('Pneus selecionados antes do processamento:', novosSelecionados);

            // Recupera o array atual do input hidden
            let acumulados = [];
            try {
                acumulados = JSON.parse(hiddenField.value || '[]');
            } catch (e) {
                acumulados = [];
            }

            // CORREÇÃO: COMENTAR OU REMOVER esta linha que zera o array
            // acumulados = []; // <-- ESTA LINHA ESTAVA CAUSANDO O PROBLEMA

            // CORREÇÃO 2: Melhorar a lógica de acumulação
            // Obter o modelo atual para filtrar apenas os pneus deste modelo
            const modeloPneuAtual = document.querySelector('[name="modelo_pneu"]').value;

            if (modeloPneuAtual) {
                // Extrair ID do modelo atual
                const modeloIdMatch = modeloPneuAtual.match(/Cód\.\s+(\d+)/);
                const modeloIdAtual = modeloIdMatch ? modeloIdMatch[1] : null;

                if (modeloIdAtual) {
                    // Remover pneus do modelo atual que já estavam no acumulado
                    acumulados = acumulados.filter(pneu => {
                        const idPneuMatch = pneu.id_pneu.match(/\d+\s+-\s+(\d+)\s+-\s+.+/);
                        const modeloIdPneu = idPneuMatch ? idPneuMatch[1] : null;
                        return modeloIdPneu !== modeloIdAtual;
                    });
                }
            }

            // Adicionar todos os selecionados atuais
            novosSelecionados.forEach(novo => {
                const pneuParaAdicionar = {
                    ...novo,
                    valor_venda: novo.valor_venda || 0
                };
                acumulados.push(pneuParaAdicionar);
                console.log(`Adicionando pneu: ${novo.id_pneu}, valor_venda: ${pneuParaAdicionar.valor_venda}`);
            });

            console.log('Pneus selecionados acumulados FINAL:', acumulados);

            // Atualiza o valor do input hidden
            hiddenField.value = JSON.stringify(acumulados);
        }

        window.selecionarTodosPneus = function (selecionar) {
            const valorTotal = document.querySelector('[name="valor_total"]').value;
            const quantidade = document.querySelector('[name="quantidade"]').value;
            const valorUnitario = calcularValorUnitario(valorTotal, quantidade);

            if (selecionar) {
                // NOVA VALIDAÇÃO: Verificar se há pneus suficientes disponíveis
                const quantidadeSolicitada = parseInt(quantidade) || 0;
                const pneusDisponiveis = registrosTransfPneusItensTemporarios.length;

                if (pneusDisponiveis < quantidadeSolicitada) {
                    alert(`Aviso: Existem apenas ${pneusDisponiveis} pneus disponíveis, mas a quantidade solicitada é ${quantidadeSolicitada}.`);
                }

                // Selecionar apenas até o limite da quantidade solicitada
                const limiteSelecao = Math.min(quantidadeSolicitada, pneusDisponiveis);

                // Primeiro, desmarcar todos
                registrosTransfPneusItensTemporarios.forEach(pneu => {
                    pneu.selecionado = false;
                    pneusSelecionadosMap.delete(pneu.id_pneu);
                });

                // Depois, selecionar apenas até o limite
                for (let i = 0; i < limiteSelecao; i++) {
                    const pneu = registrosTransfPneusItensTemporarios[i];
                    pneu.selecionado = true;
                    pneu.valor_venda = valorUnitario;
                    pneusSelecionadosMap.set(pneu.id_pneu, true);
                }

                console.log(`Selecionados ${limiteSelecao} de ${pneusDisponiveis} pneus disponíveis (limite: ${quantidadeSolicitada})`);
            } else {
                // Desselecionar todos
                registrosTransfPneusItensTemporarios.forEach(pneu => {
                    pneu.selecionado = false;
                    pneusSelecionadosMap.delete(pneu.id_pneu);
                });
            }

            // Atualiza todos os checkboxes visíveis
            document.querySelectorAll('.pneu-checkbox').forEach((checkbox, index) => {
                const shouldBeChecked = index < registrosTransfPneusItensTemporarios.length &&
                    registrosTransfPneusItensTemporarios[index].selecionado;
                checkbox.checked = shouldBeChecked;

                // Atualizar aparência das linhas
                const tr = checkbox.closest('tr');
                if (tr) {
                    tr.className = shouldBeChecked ? 'bg-indigo-50' : 'hover:bg-gray-50';
                }
            });

            // Atualiza o campo hidden
            atualizarPneusSelecionadosHidden();
            verificarSelecionarTodos();

            console.log(`Selecionar todos: ${selecionar}, valor_venda = ${valorTotal} / ${quantidade} = ${valorUnitario}`);
        };

        function verificarSelecionarTodos() {
            const todosSelecionados = registrosTransfPneusItensTemporarios.length > 0 &&
                registrosTransfPneusItensTemporarios.every(pneu => pneu.selecionado);

            document.getElementById('selecionarTodos').checked = todosSelecionados;
        }

        // Função auxiliar para converter data ISO para formato yyyy-MM-dd (input date)
        function converterDataParaInputDate(data) {
            if (!data) return '';

            try {
                const dataObj = new Date(data);
                // Verificar se a data é válida
                if (isNaN(dataObj.getTime())) return '';

                const ano = dataObj.getFullYear();
                const mes = String(dataObj.getMonth() + 1).padStart(2, '0');
                const dia = String(dataObj.getDate()).padStart(2, '0');

                return `${ano}-${mes}-${dia}`;
            } catch (e) {
                console.error('Erro ao converter data para input date:', e);
                return '';
            }
        }

        function formatarrequisicaoPneusData(data, formato) {
            try {
                const dataObj = data ? new Date(data) : new Date();
                const pad = (num) => num.toString().padStart(2, '0');

                const dia = pad(dataObj.getUTCDate());
                const mes = pad(dataObj.getUTCMonth() + 1);
                const ano = dataObj.getUTCFullYear();
                const hora = pad(dataObj.getUTCHours());
                const minuto = pad(dataObj.getUTCMinutes());
                const segundo = pad(dataObj.getUTCSeconds());

                switch (formato) {
                    case 'd/m/Y':
                        return `${dia}/${mes}/${ano}`;
                    case 'd/m/Y HH:MM:SS':
                        return `${dia}/${mes}/${ano} ${hora}:${minuto}:${segundo}`;
                    case 'Y-m-d':
                        return `${ano}-${mes}-${dia}`;
                    case 'Y-m-d HH:MM:SS':
                        return `${ano}-${mes}-${dia} ${hora}:${minuto}:${segundo}`;
                    default:
                        return `${dia}/${mes}/${ano} ${hora}:${minuto}:${segundo}`;
                }
            } catch (e) {
                console.log(e);
            }
        }

        function limparTabelaPneusRecebidos() {
            const tbody = document.getElementById('tabelaRequisicaoPneusItens');
            const ModeloRequisitado = document.querySelector('[name="modelo_pneu"]');
            const Quantidade = document.querySelector('[name="quantidade"]');
            const dataBaixa = document.querySelector('[name="data_baixa"]');
            const valorUnitario = document.querySelector('[name="valor_total"]');

            if (tbody) {
                tbody.innerHTML = '';
            }
            if (ModeloRequisitado) {
                ModeloRequisitado.value = '';
            }
            if (Quantidade) {
                Quantidade.value = '';
            }
            if (dataBaixa) {
                dataBaixa.value = '';
            }
            if (valorUnitario) {
                valorUnitario.value = '';
            }

            // Limpar também os arrays temporários relacionados à seleção atual
            registrosTransfPneusItensTemporarios = [];

            // NÃO limpar o pneusSelecionadosMap global nem o campo hidden
            // para manter os pneus já selecionados de outros modelos
        }

        // Adicione esta função para sincronizar os dados com o campo hidden
        function atualizarRequisicaoPneusHidden() {
            const hiddenField = document.getElementById('requisicaoPneusModelos_json');

            if (!hiddenField) {
                console.warn('Campo hidden requisicaoPneusModelos_json não encontrado');
                return;
            }

            // Converte os registros temporários de volta para o formato original do backend
            const dadosParaBackend = registrosReqPneusTemporarios.map(registro => {
                // Extrai o ID do modelo da string "Cód. X - Descrição"
                const modeloIdMatch = registro.modelos_requisitados.match(/Cód\.\s+(\d+)/);
                const modeloId = modeloIdMatch ? parseInt(modeloIdMatch[1]) : null;

                return {
                    id_requisicao_pneu_modelos: registro.codRequisicaoPneu || null,
                    data_inclusao: registro.data_inclusao,
                    data_alteracao: registro.data_alteracao || new Date().toISOString(),
                    id_modelo_pneu: modeloId,
                    quantidade: parseInt(registro.quantidade) || 0,
                    quantidade_baixa: parseInt(registro.quantidade_baixa) || 0,
                    data_baixa: registro.data_baixa || null,
                    valor_total: converterValorBrasileiroParaFloat(registro.valor_total), // CORREÇÃO: usar a função de conversão
                    // Mantém outros campos que podem existir
                    id_requisicao_pneu: registro.id_requisicao_pneu || null,
                    id_filial: registro.id_filial || null,
                    documento: registro.documento || null
                };
            });

            // Atualiza o campo hidden com os dados formatados
            hiddenField.value = JSON.stringify(dadosParaBackend);

            console.log('Campo hidden atualizado:', dadosParaBackend);
        };

        // Também atualize a função que remove itens ou faz outras modificações
        function removerRequisicaoPneu(index) {
            if (confirm('Tem certeza que deseja remover este item?')) {
                registrosReqPneusTemporarios.splice(index, 1);

                // Atualizar o campo hidden após remoção
                atualizarRequisicaoPneusHidden();
                atualizarrequisicaoPneusTabela();
            }
        }

        function atualizarValorVendaPneusSelecionados() {
            const valorTotal = document.querySelector('[name="valor_total"]').value;
            const quantidade = document.querySelector('[name="quantidade"]').value;
            const valorUnitario = calcularValorUnitario(valorTotal, quantidade);

            console.log(`Atualizando valor_venda: valor_total (original) = "${valorTotal}", valor_total (convertido) = ${converterValorBrasileiroParaFloat(valorTotal)}, quantidade = ${quantidade}, valor_venda = ${valorUnitario}`);

            // Atualizar valor_venda para todos os pneus selecionados
            registrosTransfPneusItensTemporarios.forEach(pneu => {
                if (pneu.selecionado) {
                    pneu.valor_venda = valorUnitario;
                }
            });

            // Atualizar o campo hidden
            atualizarPneusSelecionadosHidden();
        }

        function removerPneusDoModelo(modeloDescricao) {
            const hiddenField = document.getElementById('pneusSelecionados_json');
            if (!hiddenField) return;

            let acumulados = [];
            try {
                acumulados = JSON.parse(hiddenField.value || '[]');
            } catch (e) {
                acumulados = [];
            }

            // Extrair ID do modelo da descrição
            const modeloIdMatch = modeloDescricao.match(/Cód\.\s+(\d+)/);
            const modeloId = modeloIdMatch ? modeloIdMatch[1] : null;

            if (modeloId) {
                // Filtrar para remover pneus deste modelo
                acumulados = acumulados.filter(pneu => {
                    const idPneuMatch = pneu.id_pneu.match(/\d+\s+-\s+(\d+)\s+-\s+.+/);
                    const modeloIdPneu = idPneuMatch ? idPneuMatch[1] : null;
                    return modeloIdPneu !== modeloId;
                });

                // Atualizar campo hidden
                hiddenField.value = JSON.stringify(acumulados);
                console.log(`Pneus do modelo ${modeloDescricao} removidos. Restam:`, acumulados);
            }
        }

        function debugPneusSelecionados() {
            const hiddenField = document.getElementById('pneusSelecionados_json');
            if (hiddenField) {
                const acumulados = JSON.parse(hiddenField.value || '[]');
                console.log('DEBUG - Total de pneus selecionados:', acumulados.length);
                console.log('DEBUG - Pneus por modelo:');

                const porModelo = {};
                acumulados.forEach(pneu => {
                    const idPneuMatch = pneu.id_pneu.match(/\d+\s+-\s+(\d+)\s+-\s+(.+)\s+-\s+VIDA:/);
                    if (idPneuMatch) {
                        const modeloId = idPneuMatch[1];
                        const modeloDesc = idPneuMatch[2];
                        const chave = `${modeloId} - ${modeloDesc}`;
                        porModelo[chave] = (porModelo[chave] || 0) + 1;
                    }
                });

                console.table(porModelo);
                return acumulados;
            }
            return [];
        }

        const valorUnitarioField = document.querySelector('[name="valor_total"]');
        const quantidadeField = document.querySelector('[name="quantidade"]');

        if (valorUnitarioField) {
            valorUnitarioField.addEventListener('change', atualizarValorVendaPneusSelecionados);
            valorUnitarioField.addEventListener('input', atualizarValorVendaPneusSelecionados);
        }

        // NOVO: Adicionar listener para o campo quantidade também
        if (quantidadeField) {
            quantidadeField.addEventListener('change', atualizarValorVendaPneusSelecionados);
            quantidadeField.addEventListener('input', atualizarValorVendaPneusSelecionados);
        }

        // Tornando as funções acessíveis no escopo global
        window.adicionarrequisicaoPneus = adicionarrequisicaoPneus;
        window.editarrequisicaoPneusRegistro = editarrequisicaoPneusRegistro;
        window.selecionarPneuRecebido = selecionarPneuRecebido;
        window.atualizarPneusRecebidosTabela = atualizarPneusRecebidosTabela;
        window.atualizarContagemPneusSelecionados = atualizarContagemPneusSelecionados;
        window.atualizarQuantidadeBaixa = atualizarQuantidadeBaixa;
        window.limparTabelaPneusRecebidos = limparTabelaPneusRecebidos;
        window.calcularValorUnitario = calcularValorUnitario; // Exportar a função auxiliar
        window.converterDataParaInputDate = converterDataParaInputDate; // Exportar a função de conversão de data
        window.converterValorBrasileiroParaFloat = converterValorBrasileiroParaFloat; // Exportar a função de conversão de valor
        window.removerPneusDoModelo = removerPneusDoModelo;
        window.debugPneusSelecionados = debugPneusSelecionados;
    }
});