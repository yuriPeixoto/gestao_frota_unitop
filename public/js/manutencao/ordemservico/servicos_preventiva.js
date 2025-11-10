document.addEventListener('DOMContentLoaded', function () {
    let registrosServicosTemporarios = [];
    let emEdicao = false; // Variável de controle para impedir salvamento durante edição
    let registroEmEdicao = null; // Armazena o registro sendo editado para poder restaurá-lo

    const osServicosJson = document.getElementById('tabelaServicos_json').value;

    let servicos = [];
    try {
        servicos = JSON.parse(osServicosJson || '[]');
    } catch (error) {
        console.error('Erro ao fazer parse do JSON de serviços:', error);
        console.log('JSON recebido:', osServicosJson);
    }

    if (servicos && servicos.length > 0) {
        servicos.forEach((servico, index) => {
            try {
                registrosServicosTemporarios.push({
                    idOSServico: servico.id_ordem_servico_serv || 0,
                    idFornecedor: servico.id_fornecedor || '',
                    nomeFornecedor: `Cód. ${servico.id_fornecedor || ''} - ${servico.fornecedor?.nome_fornecedor || 'N/A'}`,
                    idManutencao: servico.id_manutencao || '',
                    descrManutencao: servico.manutencao?.descricao_manutencao || 'N/A',
                    qtdServico: servico.quantidade_servico || 0,
                    idServico: servico.id_servicos || '',
                    descrServico: servico.servicos?.descricao_servico || 'N/A',
                    valorServico: servico.valor_servico || 0,
                    valorDescServico: servico.valor_descontoservico || 0,
                    valorTotDescServico: servico.valor_total_com_desconto || 0,
                    statusServico: servico.status_servico || '',
                    numNFServico: servico.numero_nota_fiscal_servicos || '',
                    finalizado: servico.finalizado || false,
                    isSolicitado: servico.is_solicitado ?? false // Adiciona o campo is_solicitado
                });
            } catch (error) {
                console.error('Erro ao processar serviço no índice', index, ':', error);
                console.log('Serviço problemático:', servico);
            }
        });
        atualizarTabelaServicos();
    }

    function adicionarServicos() {
        const idOSServico = 0;
        const idManutencao = getSmartSelectValue('id_manutencao').value;
        const descrManutencao = getSmartSelectValue('id_manutencao').label;
        const idFornecedor = getSmartSelectValue('id_fornecedor').value;
        const nomeFornecedor = getSmartSelectValue('id_fornecedor').label;
        const idServico = getSmartSelectValue('id_servicos').value;
        const descrServico = getSmartSelectValue('id_servicos').label;
        const qtdServico = document.getElementById('servico_quantidade').value;
        const valorServico = document.getElementById('servico_valor').value;
        const valorDescServico = document.getElementById('valor_descontoservico').value;
        const valorTotDescServico = document.getElementById('valor_total_com_desconto').value;

        if (!idFornecedor) {
            alert('Fornecedor é obrigatório!');
            return;
        }

        if (!idServico) {
            alert('Serviço é obrigatório!');
            return;
        }

        const registroservicos = {
            idOSServico: idOSServico,
            idFornecedor: idFornecedor,
            idManutencao: idManutencao,
            descrManutencao: descrManutencao,
            nomeFornecedor: nomeFornecedor,
            idServico: idServico,
            descrServico: descrServico,
            qtdServico: qtdServico,
            valorServico: valorServico,
            valorDescServico: valorDescServico,
            valorTotDescServico: valorTotDescServico
        };

        registrosServicosTemporarios.push(registroservicos);
        atualizarTabelaServicos();
        limparServicosFormularioTemp();

        // Libera o estado de edição ao adicionar o item
        emEdicao = false;
        registroEmEdicao = null; // Limpa o registro em edição
        atualizarEstadoBotaoSalvar();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        document.getElementById('tabelaServicos_json').value = JSON.stringify(registrosServicosTemporarios);
    }


    function atualizarTabelaServicos() {
        const tbody = document.getElementById('tabelaServicosBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        registrosServicosTemporarios.forEach((registroServicos, index) => {
            const tr = document.createElement('tr');

            // Verifica se o serviço está solicitado para desabilitar a edição e exclusão
            const isSolicitado = registroServicos.isSolicitado ?? false;
            const btnEditarDisabled = isSolicitado ? 'disabled' : '';
            const btnEditarClass = isSolicitado
                ? 'bg-gray-400 cursor-not-allowed opacity-50'
                : 'bg-indigo-600 hover:bg-indigo-700';
            const btnEditarOnClick = isSolicitado
                ? ''
                : `onclick="editarOsServicosRegistro(${index})"`;
            const btnEditarTitle = isSolicitado
                ? 'Este serviço já foi solicitado e não pode ser editado'
                : 'Editar';

            // Configurações para o botão de excluir
            const btnExcluirDisabled = isSolicitado ? 'disabled' : '';
            const btnExcluirClass = isSolicitado
                ? 'bg-gray-400 cursor-not-allowed opacity-50'
                : 'bg-red-600 hover:bg-red-700';
            const btnExcluirOnClick = isSolicitado
                ? ''
                : `onclick="excluirOsServicosRegistro(${index})"`;
            const btnExcluirTitle = isSolicitado
                ? 'Este serviço já foi solicitado e não pode ser excluído'
                : 'Excluir';

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="rowCheckbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-index="${index}">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <button type="button" ${btnEditarOnClick} title="${btnEditarTitle}" ${btnEditarDisabled}
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white ${btnEditarClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                </button> 
            </td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.idOSServico}</td>
            <td class="px-6 py-4">${registroServicos.nomeFornecedor}</td>
            <td class="px-6 py-4">${registroServicos.descrManutencao}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.qtdServico}</td>
            <td class="px-6 py-4">${registroServicos.descrServico}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorDescServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorTotDescServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.finalizado ? 'Sim' : 'Não'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.numNFServico ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.statusServico ?? '-'}</td>
        `;
            tbody.appendChild(tr);
        });

        // 🔥 sempre reatribuir depois de recriar as linhas
        ativarCheckboxSelectAll();
    }

    function limparServicosFormularioTemp() {
        clearSmartSelect('id_fornecedor');
        clearSmartSelect('id_servicos');
        clearSmartSelect('id_manutencao');

        document.getElementById('servico_quantidade').value = '';
        document.getElementById('servico_valor').value = '';
        document.getElementById('valor_descontoservico').value = '';
        document.getElementById('valor_total_com_desconto').value = '';

        // Ao limpar o formulário, também libera o estado de edição
        emEdicao = false;
        registroEmEdicao = null;
        atualizarEstadoBotaoSalvar();
    }

    function excluirOsServicosRegistro(index) {
        const registroservicos = registrosServicosTemporarios[index];

        // Verifica se o serviço já foi solicitado
        if (registroservicos.isSolicitado) {
            alert('Este serviço já foi solicitado e não pode ser excluído.');
            return;
        }

        registrosServicosTemporarios.splice(index, 1);
        atualizarTabelaServicos();
        document.getElementById('tabelaServicos_json').value = JSON.stringify(registrosServicosTemporarios);
    }

    function editarOsServicosRegistro(index) {
        const registroservicos = registrosServicosTemporarios[index];

        // Verifica se o serviço já foi solicitado
        if (registroservicos.isSolicitado) {
            alert('Este serviço já foi solicitado e não pode ser editado.');
            return;
        }

        // Armazena uma cópia do registro antes de removê-lo
        registroEmEdicao = { ...registroservicos, indexOriginal: index };

        setSmartSelectValue('id_fornecedor', registroservicos.idFornecedor, {
            createIfNotFound: true,
            tempLabel: registroservicos.nomeFornecedor
        });

        setSmartSelectValue('id_servicos', registroservicos.idServico, {
            createIfNotFound: true,
            tempLabel: registroservicos.descrServico
        });

        setSmartSelectValue('id_manutencao', registroservicos.idManutencao, {
            createIfNotFound: true,
            tempLabel: registroservicos.descrManutencao
        });

        document.getElementById('servico_quantidade').value = registroservicos.qtdServico;
        document.getElementById('servico_valor').value = registroservicos.valorServico;
        document.getElementById('valor_descontoservico').value = registroservicos.valorDescServico;
        document.getElementById('valor_total_com_desconto').value = registroservicos.valorTotDescServico;

        // Marca que há uma edição em andamento
        emEdicao = true;
        atualizarEstadoBotaoSalvar();

        excluirOsServicosRegistro(index);
    }

    function ativarCheckboxSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.rowCheckbox');

        if (selectAll) {
            selectAll.onchange = function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            };
        }

        // Quando todos forem desmarcados/marcados manualmente
        checkboxes.forEach(cb => {
            cb.onchange = function () {
                if (!cb.checked) {
                    selectAll.checked = false;
                } else if ([...checkboxes].every(c => c.checked)) {
                    selectAll.checked = true;
                }
            };
        });
    }

    function formatarValorBRL(valor) {
        if (valor === null || valor === undefined || valor === '') return 'R$ 0,00';

        // Caso venha como string com vírgulas/pontos
        let numero = parseFloat(valor.toString().replace(/[^\d,-]/g, '').replace(',', '.'));

        if (isNaN(numero)) numero = 0;

        return numero.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function ativarCheckboxSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.rowCheckbox');

        if (!selectAll) return;

        // Quando clicar no cabeçalho → marca ou desmarca todos
        selectAll.onchange = function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        };

        // Quando marcar/desmarcar manualmente → ajusta o cabeçalho
        checkboxes.forEach(cb => {
            cb.onchange = function () {
                if (!cb.checked) {
                    selectAll.checked = false;
                } else if ([...checkboxes].every(c => c.checked)) {
                    selectAll.checked = true;
                }
            };
        });
    }

    function FinalizarServico() {
        const itensSelecionados = obterItensSelecionados();

        if (itensSelecionados.length === 0) {
            alert("Selecione ao menos um serviço para finalizar.");
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const url = `/admin/ordemservicos/onFinalizarServico`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                idSelecionado: itensSelecionados
            }),
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (!data.error) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao Finalizar o serviço:', error);
            });
    }

    function onDeletarServico() {
        const itensSelecionados = obterItensSelecionados();

        if (itensSelecionados.length === 0) {
            alert("Selecione ao menos um serviço para excluir.");
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const url = `/admin/ordemservicos/onDeletarServico`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                idSelecionado: itensSelecionados
            }),
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (!data.error) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao Deletar o serviço:', error);
            });
    }

    function obterItensSelecionados() {
        const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        let selecionados = [];

        checkboxes.forEach(cb => {
            const index = cb.getAttribute('data-index');
            if (index !== null) {
                selecionados.push(registrosServicosTemporarios[index].idOSServico);
            }
        });

        return selecionados;
    }

    /**
     * Atualiza o estado visual do botão de salvar
     * Bloqueia o botão quando há edição em andamento
     */
    function atualizarEstadoBotaoSalvar() {
        const btnSalvar = document.getElementById('btnSalvar');
        const btnCancelarEdicao = document.getElementById('btnCancelarEdicaoServico');
        const alertaEdicao = document.getElementById('alertaEdicaoServico');

        if (!btnSalvar) return;

        if (emEdicao) {
            // Bloqueia o botão de salvar
            btnSalvar.disabled = true;
            btnSalvar.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btnSalvar.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-60');
            btnSalvar.title = 'Complete a edição do serviço antes de salvar';

            // Mostra o botão de cancelar edição
            if (btnCancelarEdicao) {
                btnCancelarEdicao.classList.remove('hidden');
            }

            // Mostra o alerta de edição em andamento
            if (alertaEdicao) {
                alertaEdicao.classList.remove('hidden');
            }
        } else {
            // Libera o botão de salvar
            btnSalvar.disabled = false;
            btnSalvar.classList.remove('bg-gray-400', 'cursor-not-allowed', 'opacity-60');
            btnSalvar.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
            btnSalvar.title = '';

            // Esconde o botão de cancelar edição
            if (btnCancelarEdicao) {
                btnCancelarEdicao.classList.add('hidden');
            }

            // Esconde o alerta de edição
            if (alertaEdicao) {
                alertaEdicao.classList.add('hidden');
            }
        }
    }

    /**
     * Cancela a edição em andamento e limpa o formulário
     */
    function cancelarEdicaoServico() {
        if (confirm('Deseja realmente cancelar a edição? As alterações não salvas serão perdidas.')) {
            // Se há um registro sendo editado, restaura-o na tabela
            if (registroEmEdicao) {
                // Remove a propriedade indexOriginal antes de adicionar de volta
                const { indexOriginal, ...registroParaRestaurar } = registroEmEdicao;

                // Adiciona o registro de volta na posição original ou no final
                if (indexOriginal !== undefined && indexOriginal <= registrosServicosTemporarios.length) {
                    registrosServicosTemporarios.splice(indexOriginal, 0, registroParaRestaurar);
                } else {
                    registrosServicosTemporarios.push(registroParaRestaurar);
                }

                // Atualiza a tabela
                atualizarTabelaServicos();

                // Atualiza o campo hidden
                document.getElementById('tabelaServicos_json').value = JSON.stringify(registrosServicosTemporarios);
            }

            // Limpa o formulário
            limparServicosFormularioTemp();

            // Reseta o estado de edição
            emEdicao = false;
            registroEmEdicao = null;
            atualizarEstadoBotaoSalvar();
        }
    }

    /**
     * Verifica se há edição em andamento antes de permitir o salvamento
     * @returns {boolean} true se pode salvar, false caso contrário
     */
    function verificarEdicaoEmAndamento() {
        if (emEdicao) {
            alert('Você está editando um serviço. Por favor, finalize a edição clicando em "Adicionar" ou limpe o formulário antes de salvar.');
            return false;
        }
        return true;
    }


    window.atualizarTabelaServicos = atualizarTabelaServicos;
    window.adicionarServicos = adicionarServicos;
    window.limparServicosFormularioTemp = limparServicosFormularioTemp;
    window.excluirOsServicosRegistro = excluirOsServicosRegistro;
    window.editarOsServicosRegistro = editarOsServicosRegistro;
    window.formatarValorBRL = formatarValorBRL;
    window.FinalizarServico = FinalizarServico;
    window.onDeletarServico = onDeletarServico;
    window.verificarEdicaoEmAndamento = verificarEdicaoEmAndamento;
    window.cancelarEdicaoServico = cancelarEdicaoServico;
});
