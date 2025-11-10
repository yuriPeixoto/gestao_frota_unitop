/**
 * Scripts para gerenciamento de contratos no módulo de fornecedor
 */

// Funções para gerenciar o formulário de contratos
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa os handlers dos contratos
    initContratoHandlers();
});

/**
 * Inicializa os handlers para o formulário de contratos
 */
function initContratoHandlers() {
    // Botão para adicionar contrato
    const btnAdicionarContrato = document.getElementById('btn_adicionar_contrato');
    if (btnAdicionarContrato) {
        btnAdicionarContrato.addEventListener('click', function () {
            adicionarContrato();
        });
    }

    // Validação de datas do contrato
    const dataFinalInput = document.getElementById('contrato_fornecedor_fornecedor_data_final');
    if (dataFinalInput) {
        dataFinalInput.addEventListener('change', function () {
            validarDataContrato();
        });
    }

    // Validação do campo "Contrato Válido"
    const contratoValidoInputs = document.querySelectorAll('input[name="contrato_fornecedor_fornecedor_is_valido"]');
    contratoValidoInputs.forEach(input => {
        input.addEventListener('change', function () {
            validarContratoValido(this.value);
        });
    });
}

/**
 * Adiciona um novo contrato ao fornecedor
 */
function adicionarContrato() {
    // Validar o formulário
    if (!validarFormularioContrato()) {
        return;
    }

    // Obter os dados do formulário
    const formData = new FormData();
    formData.append('id_fornecedor', document.getElementById('contrato_fornecedor_fornecedor_id_fornecedor').value);
    formData.append('data_inicial', document.getElementById('contrato_fornecedor_fornecedor_data_inicial').value);
    formData.append('data_final', document.getElementById('contrato_fornecedor_fornecedor_data_final').value);
    formData.append('is_valido', document.querySelector('input[name="contrato_fornecedor_fornecedor_is_valido"]:checked').value);
    formData.append('valor_contrato', document.getElementById('contrato_fornecedor_fornecedor_valor_contrato').value);

    // Verificar se está editando ou criando um contrato
    const idContrato = document.getElementById('contrato_fornecedor_fornecedor_id_contrato_forn').value;

    // Configurar a requisição
    const url = idContrato
        ? `/admin/contratos/${idContrato}`
        : '/admin/contratos';

    const method = idContrato ? 'PUT' : 'POST';

    // Adicionar arquivo se existir
    const docContratoInput = document.getElementById('contrato_fornecedor_fornecedor_doc_contrato');
    if (docContratoInput.files.length > 0) {
        formData.append('doc_contrato', docContratoInput.files[0]);
    } else if (!idContrato) {
        alert('É obrigatório anexar o documento do contrato!');
        return;
    }

    // Adicionar feedback visual
    const btnSalvar = document.querySelector('#btn-salvar-contrato');
    const originalText = btnSalvar.innerHTML;
    btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    btnSalvar.disabled = true;

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Erro ao processar contrato');
            }

            // Atualizar interface
            if (idContrato) {
                atualizarLinhaContrato(data.contrato);
            } else {
                adicionarLinhaContrato(data.contrato, true);
            }

            limparFormularioContrato();
            showToast('success', data.message || 'Contrato salvo com sucesso!');
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('error', error.message || 'Falha ao salvar contrato');
        })
        .finally(() => {
            btnSalvar.innerHTML = originalText;
            btnSalvar.disabled = false;
        });
}

/**
 * Valida o formulário de contrato
 * @returns {boolean} true se o formulário for válido
 */
function validarFormularioContrato() {
    // Verificar campos obrigatórios
    const valorContrato = document.getElementById('contrato_fornecedor_fornecedor_valor_contrato').value;
    const isValido = document.querySelector('input[name="contrato_fornecedor_fornecedor_is_valido"]:checked');

    if (!valorContrato || parseFloat(valorContrato) <= 0) {
        alert('O valor do contrato deve ser maior que zero.');
        return false;
    }

    if (!isValido) {
        alert('Informe se o contrato é válido.');
        return false;
    }

    // Validar datas
    const dataInicial = document.getElementById('contrato_fornecedor_fornecedor_data_inicial').value;
    const dataFinal = document.getElementById('contrato_fornecedor_fornecedor_data_final').value;

    // Data final deve ser maior que a data inicial, se ambas estiverem preenchidas
    if (dataInicial && dataFinal) {
        const dataInicioObj = new Date(dataInicial);
        const dataFimObj = new Date(dataFinal);

        if (dataFimObj < dataInicioObj) {
            alert('A data final deve ser maior ou igual à data inicial.');
            return false;
        }
    }

    return true;
}

/**
 * Validar a data do contrato
 * Verifica se a data final está no futuro, se não estiver, pergunta se o usuário deseja marcar o contrato como inválido
 */
function validarDataContrato() {
    const dataFinalInput = document.getElementById('contrato_fornecedor_fornecedor_data_final');
    if (!dataFinalInput.value) return;

    const dataFinal = new Date(dataFinalInput.value);
    const hoje = new Date();

    // Zerar as horas para comparar apenas as datas
    hoje.setHours(0, 0, 0, 0);
    dataFinal.setHours(0, 0, 0, 0);

    // Se a data final for anterior à data atual
    if (dataFinal < hoje) {
        const confirmacao = confirm("A data final informada é menor que hoje, o contrato está vencido. Deseja marcar o contrato como inválido?");
        if (confirmacao) {
            // Marcar como inválido
            document.getElementById('contrato_fornecedor_fornecedor_is_valido_nao').checked = true;
        }
    }
}

/**
 * Validar a seleção do campo "Contrato Válido"
 * @param {string} valor 
 */
function validarContratoValido(valor) {
    // Se estiver marcando como válido
    if (valor === '1') {
        const dataFinalInput = document.getElementById('contrato_fornecedor_fornecedor_data_final');
        if (dataFinalInput.value) {
            const dataFinal = new Date(dataFinalInput.value);
            const hoje = new Date();

            // Zerar as horas para comparar apenas as datas
            hoje.setHours(0, 0, 0, 0);
            dataFinal.setHours(0, 0, 0, 0);

            // Se a data final for anterior à data atual
            if (dataFinal < hoje) {
                alert("Atenção: Você está validando um contrato com data anterior ao dia de hoje.");
            }
        }
    }
}

/**
 * Limpa o formulário de contrato
 */
function limparFormularioContrato() {
    document.getElementById('contrato_fornecedor_fornecedor_id_contrato_forn').value = '';
    document.getElementById('contrato_fornecedor_fornecedor_data_inicial').value = '';
    document.getElementById('contrato_fornecedor_fornecedor_data_final').value = '';
    document.getElementById('contrato_fornecedor_fornecedor_valor_contrato').value = '';
    document.getElementById('contrato_fornecedor_fornecedor_saldo_contrato').value = '';
    document.getElementById('contrato_fornecedor_fornecedor_is_valido_sim').checked = true;
    document.getElementById('contrato_fornecedor_fornecedor_doc_contrato').value = '';
}

/**
 * Edita um contrato existente
 * @param {number} idContrato 
 */
function editarContrato(idContrato) {
    // Buscar os dados do contrato
    fetch(`/admin/api/contratos/${idContrato}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar contrato');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o formulário com os dados do contrato
            document.getElementById('contrato_fornecedor_fornecedor_id_contrato_forn').value = data.id_contrato_forn;

            if (data.data_inicial) {
                const dataInicial = new Date(data.data_inicial);
                document.getElementById('contrato_fornecedor_fornecedor_data_inicial').value = dataInicial.toISOString().split('T')[0];
            } else {
                document.getElementById('contrato_fornecedor_fornecedor_data_inicial').value = '';
            }

            if (data.data_final) {
                const dataFinal = new Date(data.data_final);
                document.getElementById('contrato_fornecedor_fornecedor_data_final').value = dataFinal.toISOString().split('T')[0];
            } else {
                document.getElementById('contrato_fornecedor_fornecedor_data_final').value = '';
            }

            document.getElementById('contrato_fornecedor_fornecedor_saldo_contrato').value = data.saldo_contrato || 0;
            document.getElementById('contrato_fornecedor_fornecedor_valor_contrato').value = data.valor_contrato || 0;

            if (data.is_valido) {
                document.getElementById('contrato_fornecedor_fornecedor_is_valido_sim').checked = true;
            } else {
                document.getElementById('contrato_fornecedor_fornecedor_is_valido_nao').checked = true;
            }

            // Rolar até o formulário
            document.getElementById('contrato-form').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Clona um contrato existente
 * @param {number} idContrato 
 */
function clonarContrato(idContrato) {
    if (!confirm('Deseja realmente clonar este contrato?')) {
        return;
    }

    fetch(`/admin/api/contratos/${idContrato}/clonar`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao clonar contrato');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Contrato clonado com sucesso!');
                // Recarregar a página para mostrar o novo contrato
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erro ao clonar contrato');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Exclui um contrato
 * @param {number} idContrato 
 */


// Função auxiliar para feedback (substitua por seu sistema de notificação)
function showFeedback(type, message) {
    alert(message); // Pode ser substituído por um toast ou modal
}

/**
 * Adiciona uma nova linha na tabela de contratos
 * @param {Object} contrato 
 */
function adicionarLinhaContrato(contrato) {
    const tabelaContratos = document.getElementById('contratos-lista');
    if (!tabelaContratos) return;

    // Verificar se a tabela está vazia
    const linhaVazia = tabelaContratos.querySelector('tr td[colspan]');
    if (linhaVazia) {
        tabelaContratos.innerHTML = '';
    }

    // Formatar valores
    const dataInicial = contrato.data_inicial ? new Date(contrato.data_inicial).toLocaleDateString('pt-BR') : '-';
    const dataFinal = contrato.data_final ? new Date(contrato.data_final).toLocaleDateString('pt-BR') : '-';
    const dataInclusao = contrato.data_inclusao ? new Date(contrato.data_inclusao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    const valorFormatado = contrato.valor_contrato
        ? 'R$ ' + parseFloat(contrato.valor_contrato).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    const saldoFormatado = contrato.saldo_contrato
        ? 'R$ ' + parseFloat(contrato.saldo_contrato).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    // Criar a nova linha
    const novaLinha = document.createElement('tr');
    novaLinha.className = 'bg-white border-b hover:bg-gray-50';
    novaLinha.dataset.id = contrato.id_contrato_forn;

    novaLinha.innerHTML = `
        <td class="py-3 px-6">${contrato.id_contrato_forn}</td>
        <td class="py-3 px-6">${valorFormatado}</td>
        <td class="py-3 px-6">${saldoFormatado}</td>
        <td class="py-3 px-6">${dataInicial}</td>
        <td class="py-3 px-6">${dataFinal}</td>
        <td class="py-3 px-6">${contrato.user_cadastro?.name || '-'}</td>
        <td class="py-3 px-6">${contrato.is_valido ? 'Sim' : 'Não'}</td>
        <td class="py-3 px-6">${dataInclusao}</td>
        <td class="py-3 px-6">
            <div class="flex space-x-2">
                <button type="button" onclick="editarContrato(${contrato.id_contrato_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button type="button" onclick="clonarContrato(${contrato.id_contrato_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                </button>
                <button type="button" onclick="excluirContrato(${contrato.id_contrato_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    `;

    // Adicionar à tabela
    tabelaContratos.appendChild(novaLinha);
}

/**
 * Atualiza uma linha existente na tabela de contratos
 * @param {Object} contrato 
 */
function atualizarLinhaContrato(contrato) {
    const linha = document.querySelector(`#contratos-lista tr[data-id="${contrato.id_contrato_forn}"]`);
    if (!linha) {
        // Se a linha não existir, adicionar como nova
        adicionarLinhaContrato(contrato);
        return;
    }

    // Formatar valores
    const dataInicial = contrato.data_inicial ? new Date(contrato.data_inicial).toLocaleDateString('pt-BR') : '-';
    const dataFinal = contrato.data_final ? new Date(contrato.data_final).toLocaleDateString('pt-BR') : '-';

    const valorFormatado = contrato.valor_contrato
        ? 'R$ ' + parseFloat(contrato.valor_contrato).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    const saldoFormatado = contrato.saldo_contrato
        ? 'R$ ' + parseFloat(contrato.saldo_contrato).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    // Atualizar os dados nas colunas
    linha.children[1].textContent = valorFormatado;
    linha.children[2].textContent = saldoFormatado;
    linha.children[3].textContent = dataInicial;
    linha.children[4].textContent = dataFinal;
    linha.children[6].textContent = contrato.is_valido ? 'Sim' : 'Não';
}

/**
 * Remove uma linha da tabela de contratos
 * @param {number} idContrato 
 */
function removerLinhaContrato(idContrato) {
    const linha = document.querySelector(`#contratos-lista tr[data-id="${idContrato}"]`);
    if (linha) {
        linha.remove();

        // Se a tabela ficou vazia, mostrar mensagem
        const tabelaContratos = document.getElementById('contratos-lista');
        if (tabelaContratos && tabelaContratos.children.length === 0) {
            tabelaContratos.innerHTML = `
                <tr class="bg-white border-b">
                    <td colspan="9" class="py-3 px-6 text-center text-gray-500">Nenhum contrato cadastrado</td>
                </tr>
            `;
        }
    }
}