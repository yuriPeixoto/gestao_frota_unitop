document.addEventListener('DOMContentLoaded', function () {
    let registrosTransferenciaPneusTemporarios = [];
    let registrosTransfPneusItensTemporarios = [];
    let pneusSelecionadosMap = new Map();

    const TransfereicaPneusJson = document.getElementById('tranfPneus_json').value;
    const transferenciaPneus = JSON.parse(TransfereicaPneusJson);


    if (transferenciaPneus && transferenciaPneus.length > 0) {
        if (transferenciaPneus && transferenciaPneus.length > 0) {
            transferenciaPneus.forEach(transferenciaPneu => {
                registrosTransferenciaPneusTemporarios.push({
                    data_inclusao: transferenciaPneu.data_inclusao,
                    data_alteracao: transferenciaPneu.data_alteracao,
                    modelos_requisitados: 'Cód. ' + transferenciaPneu.modelo_pneu.id_modelo_pneu + ' - ' + transferenciaPneu.modelo_pneu.descricao_modelo,
                    quantidade: transferenciaPneu.quantidade,
                    quantidade_baixa: Number(transferenciaPneu.quantidade_baixa) || 0
                });
            });
            atualizarTransferenciaPneusTabela();
        }
        function adicionarTransferenciaPneus() {
            const pneusSelecionados = registrosTransfPneusItensTemporarios.filter(pneu => pneu.selecionado);

            if (pneusSelecionados.length === 0) {
                alert('Por favor, selecione pelo menos um pneu antes de adicionar!');
                return;
            }

            const modeloPneu = document.querySelector('[name="modelo_pneu"]').value;
            const quantidade = document.querySelector('[name="quantidade"]').value;

            if (!modeloPneu || !quantidade) {
                alert('Por favor, preencha todos os campos!');
                return;
            }

            // Atualizar o mapa de seleções
            pneusSelecionados.forEach(pneu => {
                pneusSelecionadosMap.set(pneu.id_pneu, true);
            });

            // Encontrar ou criar o registro
            const modeloExistenteIndex = registrosTransferenciaPneusTemporarios.findIndex(
                registro => registro.modelos_requisitados === modeloPneu
            );

            if (modeloExistenteIndex !== -1) {
                const novoRegistro = {
                    ...registrosTransferenciaPneusTemporarios[modeloExistenteIndex],
                    quantidade_baixa: pneusSelecionados.length // Atualiza valor
                };

                // Substitui o registro antigo pelo novo (imutabilidade)
                registrosTransferenciaPneusTemporarios.splice(modeloExistenteIndex, 1, novoRegistro);
            } else {
                // Criar novo registro
                registrosTransferenciaPneusTemporarios.push({
                    modelos_requisitados: modeloPneu,
                    quantidade: quantidade,
                    quantidade_baixa: pneusSelecionados.length, // Inicializa a quantidade baixa
                    data_inclusao: data_inclusao ?? new Date().toISOString(),
                    data_alteracao: data_alteracao ?? new Date().toISOString()
                });
            }

            // Atualizar exibição
            atualizarTransferenciaPneusTabela();
            atualizarPneusRecebidosTabela();
            limparTabelaPneusRecebidos();
            alert('Pneus adicionados com sucesso!');
        }

        function atualizarTransferenciaPneusTabela() {
            const tbody = document.getElementById('tabelaTransferenciaPneusBody');
            if (!tbody) return;

            tbody.innerHTML = '';

            registrosTransferenciaPneusTemporarios.forEach((registro, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button type="button" onclick="editarTransferenciaPneusRegistro(${index})"
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
                <td class="px-6 py-4 whitespace-nowrap">${formatarTransferenciaPneusData(registro.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatarTransferenciaPneusData(registro.data_alteracao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.modelos_requisitados}</td>
                <td class="px-6 py-4 whitespace-nowrap">${registro.quantidade}</td>
                 <td class="px-6 py-4 whitespace-nowrap">${registro.quantidade_baixa || 0} / ${registro.quantidade}</td>
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

        async function editarTransferenciaPneusRegistro(index) {
            const registro = registrosTransferenciaPneusTemporarios[index];

            // Preenche os campos
            document.querySelector('[name="modelo_pneu"]').value = registro.modelos_requisitados;
            document.querySelector('[name="quantidade"]').value = registro.quantidade;

            // Extrai o ID do modelo da string "Cód. X - Descrição"
            const modeloIdMatch = registro.modelos_requisitados.match(/Cód\.\s+(\d+)/);
            const modeloId = modeloIdMatch ? modeloIdMatch[1] : null;

            if (!modeloId) {
                alert('Não foi possível identificar o ID do modelo');
                return;
            }

            const transferId = document.querySelector('[name="id_transferencia_pneus"]').value;

            try {
                // Modificar a URL para incluir o ID do modelo específico
                const response = await fetch(`/admin/transferenciapneus/por-modelo/${transferId}/${modeloId}`);

                if (!response.ok) throw new Error('Erro ao carregar pneus');

                const resultado = await response.json();

                // Verificar se a resposta foi bem-sucedida e contém dados
                if (resultado.success && resultado.data && resultado.data.length > 0) {
                    console.log('Pneus recebidos:', resultado.data);
                    // Converter os dados da API para o formato esperado pelo array temporário
                    registrosTransfPneusItensTemporarios = resultado.data.map(pneu => {
                        // Formatar a string do pneu no formato original
                        const idPneuFormatado = `${pneu.id} - ${pneu.modelo.codigo} - ${pneu.modelo.descricao.trim()} - VIDA: ${pneu.vida}`;

                        // Verificar se este pneu já foi selecionado anteriormente
                        const estaSelecionado = pneu.selecionado;

                        return {
                            id_pneu: idPneuFormatado,
                            codigo_modelo: pneu.codigo,
                            modelo: pneu.modelo,
                            vida: pneu.vida,
                            id_transferencia_modelo: pneu.id_transferencia_modelo,
                            selecionado: estaSelecionado // Restaura o estado de seleção
                        };
                    });

                    // Atualizar a tabela com os pneus carregados
                    atualizarPneusRecebidosTabela();

                    // Atualizar a contagem na tabela de transferência
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
            const tbody = document.getElementById('tabelaPneusRecebidosBody');
            if (!tbody) {
                console.error('Elemento #tabelaPneusRecebidosBody não encontrado');
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
            pneu.selecionado = checkbox.checked;

            // Atualizar o mapa de pneus selecionados
            if (checkbox.checked) {
                pneusSelecionadosMap.set(pneu.id_pneu, true);
            } else {
                pneusSelecionadosMap.delete(pneu.id_pneu);
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
            const modeloIndex = registrosTransferenciaPneusTemporarios.findIndex(
                registro => registro.modelos_requisitados === modeloPneuAtual
            );

            if (modeloIndex !== -1) {
                registrosTransferenciaPneusTemporarios[modeloIndex].quantidade_selecionada = quantidadeSelecionada;

            }
        }

        function atualizarPneusSelecionadosHidden() {
            const novosSelecionados = registrosTransfPneusItensTemporarios.filter(pneu => pneu.selecionado);
            const hiddenField = document.getElementById('pneusSelecionados_json');

            if (!hiddenField) {
                console.warn('Elemento pneusSelecionados_json não encontrado no DOM');
                return;
            }

            // Recupera o array atual do input hidden
            let acumulados = [];
            try {
                acumulados = JSON.parse(hiddenField.value || '[]');
            } catch (e) {
                acumulados = [];
            }

            // Adiciona apenas os novos, evitando duplicidade por id_pneu
            novosSelecionados.forEach(novo => {
                if (!acumulados.some(item => item.id_pneu === novo.id_pneu)) {
                    acumulados.push(novo);
                }
            });

            console.log('Pneus selecionados acumulados:', acumulados);

            // Atualiza o valor do input hidden
            hiddenField.value = JSON.stringify(acumulados);
        }


        window.selecionarTodosPneus = function (selecionar) {
            // Atualiza todos os registros
            registrosTransfPneusItensTemporarios.forEach(pneu => {
                pneu.selecionado = selecionar;
            });

            // Atualiza todos os checkboxes visíveis
            document.querySelectorAll('.pneu-checkbox').forEach(checkbox => {
                checkbox.checked = selecionar;
            });

            // Atualiza o campo hidden (se existir)
            atualizarPneusSelecionadosHidden();
        };

        function verificarSelecionarTodos() {
            const todosSelecionados = registrosTransfPneusItensTemporarios.length > 0 &&
                registrosTransfPneusItensTemporarios.every(pneu => pneu.selecionado);

            document.getElementById('selecionarTodos').checked = todosSelecionados;
        }

        function formatarTransferenciaPneusData(data) {
            // Se não houver data, ou se for inválida, use a data atual
            if (!data || new Date(data).toString() === 'Invalid Date') {
                return new Date().toLocaleString('pt-BR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    timeZone: 'America/Cuiaba'
                });
            }

            const dataObj = new Date(data);
            return dataObj.toLocaleString('pt-BR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'America/Cuiaba'
            });
        }


        function limparTabelaPneusRecebidos() {
            const tbody = document.getElementById('tabelaPneusRecebidosBody');
            const ModeloRequisitado = document.querySelector('[name="modelo_pneu"]');
            const Quantidade = document.querySelector('[name="quantidade"]');

            if (tbody) {
                tbody.innerHTML = '';
            }
            if (ModeloRequisitado) {
                ModeloRequisitado.value = '';
            }
            if (Quantidade) {
                Quantidade.value = '';
            }
        };


        // Tornando as funções acessíveis no escopo global
        window.adicionarTransferenciaPneus = adicionarTransferenciaPneus;
        window.editarTransferenciaPneusRegistro = editarTransferenciaPneusRegistro;
        window.selecionarPneuRecebido = selecionarPneuRecebido;
        window.atualizarPneusRecebidosTabela = atualizarPneusRecebidosTabela;
        window.atualizarContagemPneusSelecionados = atualizarContagemPneusSelecionados;
        window.atualizarQuantidadeBaixa = atualizarQuantidadeBaixa;
        window.limparTabelaPneusRecebidos = limparTabelaPneusRecebidos;
    }
});

