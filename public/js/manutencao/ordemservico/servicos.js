// Variáveis globais para gerenciar os serviços
let registrosServicosTemporarios = [];
let emEdicao = false; // Variável de controle para impedir salvamento durante edição
let registroEmEdicao = null; // Armazena o registro sendo editado para poder restaurá-lo

/**
 * Função para adicionar serviços à tabela
 */
function adicionarServicos() {
    const idOSServico = 0;
    const idFornecedor = getSmartSelectValue('id_fornecedor').value;
    const nomeFornecedor = getSmartSelectValue('id_fornecedor').label;
    const idServico = getSmartSelectValue('id_servicos').value;
    const descrServico = getSmartSelectValue('id_servicos').label;
    const qtdServico = document.getElementById('servico_quantidade').value;
    const valorServico = document.getElementById('servico_valor').value;
    const valorDescServico = document.getElementById('valor_descontoservico').value;
    const valorTotDescServico = document.getElementById('valor_total_com_desconto').value;
    const dataInclusao = new Date().toISOString();
    const dataAlteracao = new Date().toISOString();

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
        nomeFornecedor: nomeFornecedor,
        idServico: idServico,
        descrServico: descrServico,
        qtdServico: qtdServico,
        valorServico: valorServico,
        valorDescServico: valorDescServico,
        valorTotDescServico: valorTotDescServico,
        dataInclusao: dataInclusao,
        dataAlteracao: dataAlteracao
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

/**
 * Atualiza a exibição da tabela de serviços
 */
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
            <td class="px-6 py-4">${formatarDataBR(registroServicos.dataInclusao)}</td>
            <td class="px-6 py-4">${formatarDataBR(registroServicos.dataAlteracao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.nomeFornecedor}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.qtdServico}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.descrServico}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorDescServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${formatarValorBRL(registroServicos.valorTotDescServico) ?? 0}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.finalizado ? 'Sim' : 'Não'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.numNFServico ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${registroServicos.statusServico ?? '-'}</td>
        `;
        tbody.appendChild(tr);
    });

    ativarCheckboxSelectAll();
}

function limparServicosFormularioTemp() {
    clearSmartSelect('id_fornecedor');
    clearSmartSelect('id_servicos');

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
    const itensSelecionados = obterItensSelecionadosServicos();
    console.log(itensSelecionados);

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
    const itensSelecionados = obterItensSelecionadosServicos();

    if (itensSelecionados.length === 0) {
        alert("Selecione ao menos um serviço salvo para excluir. (Novos registros não salvos não podem ser excluídos por esta função)");
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

function obterItensSelecionadosServicos() {
    const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
    let selecionados = [];
    const idsSet = new Set(); // Usar Set para evitar duplicatas

    checkboxes.forEach(cb => {
        const index = cb.getAttribute('data-index');
        if (index !== null) {
            const registro = registrosServicosTemporarios[index];

            // Verificação de segurança
            if (registro && registro.idOSServico !== undefined && registro.idOSServico !== null) {
                // Só adiciona se o ID for válido (maior que 0 para registros existentes) e não duplicado
                if (registro.idOSServico > 0 && !idsSet.has(registro.idOSServico)) {
                    idsSet.add(registro.idOSServico);
                    selecionados.push(registro.idOSServico);
                }
            }
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

/**
 * Inicialização ao carregar a página
 */
document.addEventListener('DOMContentLoaded', function () {
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
                    nomeFornecedor: `Cód. ${servico.id_fornecedor || ''} - ${servico.fornecedor?.nome_fornecedor ?? 'Fornecedor não informado'}`,
                    qtdServico: servico.quantidade_servico || 0,
                    idServico: servico.id_servicos || '',
                    descrServico: 'Código ' + (servico.id_servicos || 'N/A') + ' - ' + (servico.servicos?.descricao_servico ?? 'Serviço não identificado'),
                    valorServico: servico.valor_servico || 0,
                    valorDescServico: servico.valor_descontoservico || 0,
                    valorTotDescServico: servico.valor_total_com_desconto || 0,
                    statusServico: servico.status_servico || '',
                    numNFServico: servico.numero_nota_fiscal_servicos || '',
                    finalizado: servico.finalizado || false,
                    isSolicitado: servico.is_solicitado ?? false,
                    dataInclusao: servico.data_inclusao || '',
                    dataAlteracao: servico.data_alteracao || ''
                });
            } catch (error) {
                console.error('Erro ao processar serviço no índice', index, ':', error);
                console.log('Serviço problemático:', servico);
            }
        });
        atualizarTabelaServicos();
    }
});