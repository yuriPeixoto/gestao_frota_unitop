document.addEventListener('DOMContentLoaded', function () {
    let registrosPecasTemporarios = [];
    let emEdicao = false; // Variável de controle para impedir salvamento durante edição
    let registroEmEdicao = null; // Armazena o registro sendo editado para poder restaurá-lo

    // Verifica se o elemento existe antes de tentar acessá-lo
    const tabelaPecasElement = document.getElementById('tabelaPecas_json');
    const osPecasJson = tabelaPecasElement ? tabelaPecasElement.value : '[]';

    let pecas = [];
    try {
        pecas = JSON.parse(osPecasJson || '[]');
    } catch (error) {
        console.error('Erro ao fazer parse do JSON de peças:', error);
        console.log('JSON recebido:', osPecasJson);
    }

    if (pecas && pecas.length > 0) {
        pecas.forEach((peca, index) => {
            try {
                registrosPecasTemporarios.push({
                    idOSPecas: peca.id_ordem_servico_pecas || 0,
                    idFornecedor: peca.id_fornecedor || '',
                    nomeFornecedor: `Cód. ${peca.id_fornecedor || ''} - ${peca.fornecedor?.nome_fornecedor || 'N/A'}`,
                    idProduto: peca.id_produto || '',
                    descrProduto: peca.produto?.descricao_produto || 'N/A',
                    valorUnitario: peca.valor_pecas || 0,
                    valorDesconto: peca.valor_desconto || 0,
                    qtdPecas: peca.quantidade || 0,
                    valorTotalDesconto: peca.valor_total_com_desconto || 0,
                    idUnidade: peca.id_unidade_produto || '',
                    descrUnidade: peca.produto?.unidade_produto?.descricao_unidade || 'N/A',
                    is_finalizado: peca.is_finalizado || false,
                    numNFPeca: peca.numero_nota_fiscal_pecas || '',
                    situacaoPecas: peca.situacao_pecas || '',
                    jaSolicitada: peca.jasolicitada ?? false, // Adiciona o campo jasolicitada
                    dataInclusao: peca.data_inclusao || null,
                    dataAlteracao: peca.data_alteracao || null
                });
            } catch (error) {
                console.error('Erro ao processar peça no índice', index, ':', error);
                console.log('Peça problemática:', peca);
            }
        });
        atualizarTabelaPecas();
    }

    function adicionarPecas() {
        const idOSPecas = 0;
        const idFornecedor = getSmartSelectValue('id_fornecedor-pecas').value;
        const nomeFornecedor = getSmartSelectValue('id_fornecedor-pecas').label;
        const idProduto = getSmartSelectValue('id_produto').value;
        const descrProduto = getSmartSelectValue('id_produto').label;
        const valorUnitario = document.getElementById('valor_unitario_pecas').value;
        const valorDesconto = document.getElementById('valor_desconto').value;
        const qtdPecas = document.getElementById('quantidade').value;
        const valorTotalDesconto = document.getElementById('valor_total_com_desconto_pecas').value;
        const descrUnidade = document.getElementById('descrUnidade').value;
        const idUnidade = document.getElementById('id_unidade').value;
        const dataInclusao = new Date().toISOString();
        const dataAlteracao = new Date().toISOString();

        if (!idFornecedor) {
            alert('Fornecedor é obrigatório!');
            return;
        }

        if (!idProduto) {
            alert('Produto é obrigatório!');
            return;
        }

        const registroPecas = {
            idOSPecas: idOSPecas,
            idFornecedor: idFornecedor,
            nomeFornecedor: nomeFornecedor,
            idProduto: idProduto,
            descrProduto: descrProduto,
            valorUnitario: valorUnitario,
            valorDesconto: valorDesconto,
            qtdPecas: qtdPecas,
            valorTotalDesconto: valorTotalDesconto,
            descrUnidade: descrUnidade,
            idUnidade: idUnidade,
            dataInclusao: dataInclusao,
            dataAlteracao: dataAlteracao
        };

        registrosPecasTemporarios.push(registroPecas);
        atualizarTabelaPecas();
        limparServicosFormularioTemp();

        // Libera o estado de edição ao adicionar o item
        emEdicao = false;
        registroEmEdicao = null; // Limpa o registro em edição
        atualizarEstadoBotaoSalvar();

        alert('Registro adicionado com sucesso!');

        // Atualiza o campo hidden
        const tabelaPecasInput = document.getElementById('tabelaPecas_json');
        if (tabelaPecasInput) {
            tabelaPecasInput.value = JSON.stringify(registrosPecasTemporarios);
        }
    }


    function atualizarTabelaPecas() {
        const tbody = document.getElementById('tabelaPecasBody');
        if (!tbody) {
            console.error('Elemento tabelaPecasBody não encontrado!');
            return;
        }

        console.log('Atualizando tabela de peças. Total de registros:', registrosPecasTemporarios.length);
        tbody.innerHTML = '';

        registrosPecasTemporarios.forEach((registroPecas, index) => {
            const tr = document.createElement('tr');

            // Verifica se a peça está solicitada para desabilitar a edição e exclusão
            const jaSolicitada = registroPecas.jaSolicitada ?? false;
            const btnEditarDisabled = jaSolicitada ? 'disabled' : '';
            const btnEditarClass = jaSolicitada
                ? 'bg-gray-400 cursor-not-allowed opacity-50'
                : 'bg-indigo-600 hover:bg-indigo-700';
            const btnEditarOnClick = jaSolicitada
                ? ''
                : `onclick="editarOsPecasRegistro(${index})"`;
            const btnEditarTitle = jaSolicitada
                ? 'Esta peça já foi solicitada e não pode ser editada'
                : 'Editar';

            // Configurações para o botão de excluir
            const btnExcluirDisabled = jaSolicitada ? 'disabled' : '';
            const btnExcluirClass = jaSolicitada
                ? 'bg-gray-400 cursor-not-allowed opacity-50'
                : 'bg-red-600 hover:bg-red-700';
            const btnExcluirOnClick = jaSolicitada
                ? ''
                : `onclick="excluirOsPecasRegistro(${index})"`;
            const btnExcluirTitle = jaSolicitada
                ? 'Esta peça já foi solicitada e não pode ser excluída'
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
                <button type="button" ${btnExcluirOnClick} title="${btnExcluirTitle}" ${btnExcluirDisabled}
                    class="btn-excluir inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white ${btnExcluirClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button> 
            </td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.idOSPecas}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarDataBR(registroPecas.dataInclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarDataBR(registroPecas.dataAlteracao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.nomeFornecedor}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.descrProduto}</td>
            <td class="px-6 py-4">${registroPecas.descrUnidade}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroPecas.valorUnitario)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroPecas.valorDesconto) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.qtdPecas ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroPecas.valorTotalDesconto) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.is_finalizado ? 'Sim' : 'Não'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.numNFPeca ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroPecas.situacaoPecas ?? '-'}</td>
        `;
            tbody.appendChild(tr);
        });

        ativarCheckboxSelectAll();
    }

    function limparServicosFormularioTemp() {
        clearSmartSelect('id_fornecedor-pecas');
        clearSmartSelect('id_produto');

        document.getElementById('quantidade').value = '';
        document.getElementById('valor_unitario_pecas').value = '';
        document.getElementById('valor_desconto').value = '';
        document.getElementById('valor_total_com_desconto_pecas').value = '';
        document.getElementById('desc_grupo').value = '';
        document.getElementById('qtd_estoque').value = '';

        // Ao limpar o formulário, também libera o estado de edição
        emEdicao = false;
        registroEmEdicao = null;
        atualizarEstadoBotaoSalvar();
    }

    function excluirOsPecasRegistro(index) {
        const registroPecas = registrosPecasTemporarios[index];

        // Verifica se a peça já foi solicitada
        if (registroPecas.jaSolicitada) {
            alert('Esta peça já foi solicitada e não pode ser excluída.');
            return;
        }

        registrosPecasTemporarios.splice(index, 1);
        atualizarTabelaPecas();
        const tabelaPecasInput = document.getElementById('tabelaPecas_json');
        if (tabelaPecasInput) {
            tabelaPecasInput.value = JSON.stringify(registrosPecasTemporarios);
        }
    }

    function editarOsPecasRegistro(index) {
        const registroPecas = registrosPecasTemporarios[index];

        // Verifica se a peça já foi solicitada
        if (registroPecas.jaSolicitada) {
            alert('Esta peça já foi solicitada e não pode ser editada.');
            return;
        }

        // Armazena uma cópia do registro antes de removê-lo
        registroEmEdicao = { ...registroPecas, indexOriginal: index };

        setSmartSelectValue('id_fornecedor-pecas', registroPecas.idFornecedor, {
            createIfNotFound: true,
            tempLabel: registroPecas.nomeFornecedor
        });

        setSmartSelectValue('id_produto', registroPecas.idProduto, {
            createIfNotFound: true,
            tempLabel: registroPecas.descrProduto
        });

        document.getElementById('quantidade').value = registroPecas.qtdPecas;
        document.getElementById('valor_unitario_pecas').value = registroPecas.valorUnitario;
        document.getElementById('valor_desconto').value = registroPecas.valorDesconto;
        document.getElementById('valor_total_com_desconto_pecas').value = registroPecas.valorTotalDesconto;

        // Marca que há uma edição em andamento
        emEdicao = true;
        atualizarEstadoBotaoSalvar();

        excluirOsPecasRegistro(index);
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

        if (typeof valor === 'number') {
            if (!isFinite(valor)) return 'R$ 0,00';
            return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2 });
        }

        let str = String(valor).trim()
            .replace(/\s+/g, '')
            .replace(/R\$\s?/gi, '');

        str = str.replace(/[^0-9,.-]/g, '');

        if (str === '' || str === '-' || str === '.' || str === ',') return 'R$ 0,00';

        let numero;

        if (str.includes(',') && str.includes('.')) {
            const lastComma = str.lastIndexOf(',');
            const lastDot = str.lastIndexOf('.');
            if (lastComma > lastDot) {

                str = str.replace(/\./g, '').replace(',', '.');
            } else {

                str = str.replace(/,/g, '');
            }
            numero = parseFloat(str);
        } else if (str.includes(',')) {
            str = str.replace(/\./g, '').replace(',', '.');
            numero = parseFloat(str);
        } else if (str.includes('.')) {
            const parts = str.split('.');
            const last = parts[parts.length - 1];
            if (/^\d{3}$/.test(last) && parts.length > 1) {
                str = str.replace(/\./g, '');
                numero = parseFloat(str);
            } else {
                numero = parseFloat(str);
            }
        } else {
            numero = parseFloat(str);
        }

        if (isNaN(numero) || !isFinite(numero)) numero = 0;

        return numero.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
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

    function onDeletarPecas() {
        const itensSelecionados = obterItensSelecionados();
        console.log(itensSelecionados);

        if (itensSelecionados.length === 0) {
            alert("Selecione ao menos um serviço para excluir.");
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const url = `/admin/ordemservicos/onDeletarPecas`;

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
                selecionados.push(registrosPecasTemporarios[index].idOSPecas);
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
        const btnCancelarEdicao = document.getElementById('btnCancelarEdicaoPecas');
        const alertaEdicao = document.getElementById('alertaEdicaoPecas');

        if (!btnSalvar) return;

        if (emEdicao) {
            // Bloqueia o botão de salvar
            btnSalvar.disabled = true;
            btnSalvar.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
            btnSalvar.classList.add('bg-gray-400', 'cursor-not-allowed', 'opacity-60');
            btnSalvar.title = 'Complete a edição da peça antes de salvar';

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
    function cancelarEdicaoPecas() {
        if (confirm('Deseja realmente cancelar a edição? As alterações não salvas serão perdidas.')) {
            // Se há um registro sendo editado, restaura-o na tabela
            if (registroEmEdicao) {
                // Remove a propriedade indexOriginal antes de adicionar de volta
                const { indexOriginal, ...registroParaRestaurar } = registroEmEdicao;

                // Adiciona o registro de volta na posição original ou no final
                if (indexOriginal !== undefined && indexOriginal <= registrosPecasTemporarios.length) {
                    registrosPecasTemporarios.splice(indexOriginal, 0, registroParaRestaurar);
                } else {
                    registrosPecasTemporarios.push(registroParaRestaurar);
                }

                // Atualiza a tabela
                atualizarTabelaPecas();

                // Atualiza o campo hidden
                const tabelaPecasInput = document.getElementById('tabelaPecas_json');
                if (tabelaPecasInput) {
                    tabelaPecasInput.value = JSON.stringify(registrosPecasTemporarios);
                }
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
            alert('Você está editando uma peça. Por favor, finalize a edição clicando em "Adicionar" ou limpe o formulário antes de salvar.');
            return false;
        }
        return true;
    }

    /**
     * Formata data ISO para o padrão brasileiro (dd/mm/aaaa hh:mm:ss)
     * @param {string} dataISO - Data no formato ISO (2024-01-15T14:30:00)
     * @returns {string} Data formatada no padrão brasileiro ou '-' se inválida
     */
    function formatarDataBR(dataISO) {
        if (!dataISO || dataISO === '' || dataISO === null || dataISO === undefined) {
            return '-';
        }

        try {
            const data = new Date(dataISO);

            // Verifica se a data é válida
            if (isNaN(data.getTime())) {
                return '-';
            }

            const dia = String(data.getDate()).padStart(2, '0');
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const ano = data.getFullYear();
            const horas = String(data.getHours()).padStart(2, '0');
            const minutos = String(data.getMinutes()).padStart(2, '0');
            const segundos = String(data.getSeconds()).padStart(2, '0');

            return `${dia}/${mes}/${ano} ${horas}:${minutos}:${segundos}`;
        } catch (error) {
            console.error('Erro ao formatar data:', error);
            return '-';
        }
    }


    window.atualizarTabelaPecas = atualizarTabelaPecas;
    window.adicionarPecas = adicionarPecas;
    window.limparServicosFormularioTemp = limparServicosFormularioTemp;
    window.excluirOsPecasRegistro = excluirOsPecasRegistro;
    window.editarOsPecasRegistro = editarOsPecasRegistro;
    window.formatarValorBRL = formatarValorBRL;
    window.onDeletarPecas = onDeletarPecas;
    window.verificarEdicaoEmAndamento = verificarEdicaoEmAndamento;
    window.cancelarEdicaoPecas = cancelarEdicaoPecas;
});