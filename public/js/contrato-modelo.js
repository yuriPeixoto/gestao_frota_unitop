/**
 * Scripts para gerenciamento de vínculos entre contratos e modelos no módulo de fornecedor
 */

// Funções para gerenciar o formulário de contrato-modelo
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa os handlers dos contratos-modelo
    initContratoModeloHandlers();
});

/**
 * Inicializa os handlers para o formulário de contratos-modelo
 */
function initContratoModeloHandlers() {
    // Botão para adicionar contrato-modelo
    const btnAdicionarContratoModelo = document.getElementById('btn_adicionar_contrato_modelo');
    if (btnAdicionarContratoModelo) {
        btnAdicionarContratoModelo.addEventListener('click', function () {
            adicionarContratoModelo();
        });
    }

    // Carregar modelos ao selecionar um contrato
    const contratoSelect = document.getElementById('id_contrato');
    if (contratoSelect) {
        contratoSelect.addEventListener('change', function () {
            // Se implementar filtragem de modelos por contrato, será feito aqui
        });
    }
}

/**
 * Adiciona um novo vínculo contrato-modelo
 */
function adicionarContratoModelo() {
    const id_modelo = document.getElementById('id_modelo').value;
    const id_contrato = document.getElementById('id_contrato').value;
    const ativo = document.querySelector('input[name="ativo"]:checked').value;

    // cria um input hidden para cada campo
    let container = document.getElementById('contratos-modelos-container');
    let index = container.children.length;

    container.insertAdjacentHTML('beforeend', `
        <input type="hidden" name="contratos_modelos[${index}][id_modelo]" value="${id_modelo}">
        <input type="hidden" name="contratos_modelos[${index}][id_contrato]" value="${id_contrato}">
        <input type="hidden" name="contratos_modelos[${index}][ativo]" value="${ativo}">
    `);

    alert("Contrato Modelo adicionado! (vai ser salvo junto com fornecedor)");
}


/**
 * Valida o formulário de contrato-modelo
 * @returns {boolean} true se o formulário for válido
 */
function validarFormularioContratoModelo() {
    // Verificar campos obrigatórios
    const idModelo = document.getElementById('id_modelo').value;
    const idContrato = document.getElementById('id_contrato').value;
    const ativo = document.querySelector('input[name="ativo"]:checked');

    if (!idModelo) {
        alert('Selecione um modelo de veículo.');
        return false;
    }

    if (!idContrato) {
        alert('Selecione um contrato.');
        return false;
    }

    if (!ativo) {
        alert('Informe se o vínculo está ativo.');
        return false;
    }

    return true;
}

/**
 * Limpa o formulário de contrato-modelo
 */
function limparFormularioContratoModelo() {
    document.getElementById('id_contrato_modelo').value = '';
    document.getElementById('id_modelo').value = '';
    document.getElementById('id_contrato').value = '';
    document.getElementById('ativo_sim').checked = true;
}

/**
 * Edita um vínculo contrato-modelo existente
 * @param {number} idContratoModelo 
 */
function editarContratoModelo(idContratoModelo) {
    // Buscar os dados do contrato-modelo
    fetch(`/admin/api/contratosmodelo/${idContratoModelo}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o formulário com os dados do contrato-modelo
            document.getElementById('id_contrato_modelo').value = data.id_contrato_modelo;
            document.getElementById('id_modelo').value = data.id_modelo;
            document.getElementById('id_contrato').value = data.id_contrato;

            if (data.ativo) {
                document.getElementById('ativo_sim').checked = true;
            } else {
                document.getElementById('ativo_nao').checked = true;
            }

            // Rolar até o formulário
            document.getElementById('contrato-modelo-form').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Clona um vínculo contrato-modelo existente
 * @param {number} idContratoModelo 
 */
function clonarContratoModelo(idContratoModelo) {
    if (!confirm('Deseja realmente clonar este vínculo contrato-modelo?')) {
        return;
    }

    fetch(`/admin/api/contratosmodelo/${idContratoModelo}/clonar`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao clonar vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Vínculo contrato-modelo clonado com sucesso!');
                // Recarregar a página para mostrar o novo vínculo
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erro ao clonar vínculo contrato-modelo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Exclui um vínculo contrato-modelo
 * @param {number} idContratoModelo 
 */
function excluirContratoModelo(idContratoModelo) {
    if (!confirm('Deseja realmente excluir este vínculo contrato-modelo?')) {
        return;
    }

    fetch(`/admin/api/contratosmodelo/${idContratoModelo}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remover a linha da tabela
                removerLinhaContratoModelo(idContratoModelo);
                alert('Vínculo contrato-modelo excluído com sucesso!');
            } else {
                throw new Error(data.message || 'Erro ao excluir vínculo contrato-modelo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Adiciona uma nova linha na tabela de contratos-modelo
 * @param {Object} contratoModelo 
 */
function adicionarLinhaContratoModelo(contratoModelo) {
    const tabelaContratosModelo = document.getElementById('contratos-modelo-lista');
    if (!tabelaContratosModelo) return;

    // Verificar se a tabela está vazia
    const linhaVazia = tabelaContratosModelo.querySelector('tr td[colspan]');
    if (linhaVazia) {
        tabelaContratosModelo.innerHTML = '';
    }

    // Formatar valores
    const dataInclusao = contratoModelo.data_inclusao ? new Date(contratoModelo.data_inclusao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    const dataAlteracao = contratoModelo.data_alteracao ? new Date(contratoModelo.data_alteracao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    // Obter informações do modelo e contrato
    const modeloDescricao = contratoModelo.modelo ?
        `${contratoModelo.modelo.id_modelo_veiculo} - ${contratoModelo.modelo.descricao_modelo_veiculo}` :
        `${contratoModelo.id_modelo} - Modelo não encontrado`;

    // Criar a nova linha
    const novaLinha = document.createElement('tr');
    novaLinha.className = 'bg-white border-b hover:bg-gray-50';
    novaLinha.dataset.id = contratoModelo.id_contrato_modelo;

    novaLinha.innerHTML = `
        <td class="py-3 px-6">${contratoModelo.id_contrato_modelo}</td>
        <td class="py-3 px-6">${modeloDescricao}</td>
        <td class="py-3 px-6">${contratoModelo.id_contrato}</td>
        <td class="py-3 px-6">${contratoModelo.ativo ? 'Sim' : 'Não'}</td>
        <td class="py-3 px-6">${dataInclusao}</td>
        <td class="py-3 px-6">${dataAlteracao}</td>
        <td class="py-3 px-6">
            <div class="flex space-x-2">
                <button type="button" onclick="editarContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button type="button" onclick="clonarContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                </button>
                <button type="button" onclick="excluirContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    `;

    // Adicionar à tabela
    tabelaContratosModelo.appendChild(novaLinha);
}

/**
 * Atualiza uma linha existente na tabela de contratos-modelo
 * @param {Object} contratoModelo 
 */
function atualizarLinhaContratoModelo(contratoModelo) {
    const linha = document.querySelector(`#contratos-modelo-lista tr[data-id="${contratoModelo.id_contrato_modelo}"]`);
    if (!linha) {
        // Se a linha não existir, adicionar como nova
        adicionarLinhaContratoModelo(contratoModelo);
        return;
    }

    // Formatar valores
    const dataAlteracao = contratoModelo.data_alteracao ? new Date(contratoModelo.data_alteracao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    // Obter informações do modelo
    const modeloDescricao = contratoModelo.modelo ?
        `${contratoModelo.modelo.id_modelo_veiculo} - ${contratoModelo.modelo.descricao_modelo_veiculo}` :
        `${contratoModelo.id_modelo} - Modelo não encontrado`;

    // Atualizar os dados nas colunas
    linha.children[1].textContent = modeloDescricao;
    linha.children[2].textContent = contratoModelo.id_contrato;
    linha.children[3].textContent = contratoModelo.ativo ? 'Sim' : 'Não';
    linha.children[5].textContent = dataAlteracao;
}

/**
 * Remove uma linha da tabela de contratos-modelo
 * @param {number} idContratoModelo 
 */
function removerLinhaContratoModelo(idContratoModelo) {
    const linha = document.querySelector(`#contratos-modelo-lista tr[data-id="${idContratoModelo}"]`);
    if (linha) {
        linha.remove();

        // Se a tabela ficou vazia, mostrar mensagem
        const tabelaContratosModelo = document.getElementById('contratos-modelo-lista');
        if (tabelaContratosModelo && tabelaContratosModelo.children.length === 0) {
            tabelaContratosModelo.innerHTML = `
                <tr class="bg-white border-b">
                    <td colspan="7" class="py-3 px-6 text-center text-gray-500">Nenhum vínculo contrato-modelo cadastrado</td>
                </tr>
            `;
        }
    }
}

/**
 * Valida o formulário de contrato-modelo
 * @returns {boolean} true se o formulário for válido
 */
function validarFormularioContratoModelo() {
    // Verificar campos obrigatórios
    const idModelo = document.getElementById('id_modelo').value;
    const idContrato = document.getElementById('id_contrato').value;
    const ativo = document.querySelector('input[name="ativo"]:checked');

    if (!idModelo) {
        alert('Selecione um modelo de veículo.');
        return false;
    }

    if (!idContrato) {
        alert('Selecione um contrato.');
        return false;
    }

    if (!ativo) {
        alert('Informe se o vínculo está ativo.');
        return false;
    }

    return true;
}

/**
 * Limpa o formulário de contrato-modelo
 */
function limparFormularioContratoModelo() {
    document.getElementById('id_contrato_modelo').value = '';
    document.getElementById('id_modelo').value = '';
    document.getElementById('id_contrato').value = '';
    document.getElementById('ativo_sim').checked = true;
}

/**
 * Edita um vínculo contrato-modelo existente
 * @param {number} idContratoModelo 
 */
function editarContratoModelo(idContratoModelo) {
    // Buscar os dados do contrato-modelo
    fetch(`/admin/api/contratosmodelo/${idContratoModelo}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o formulário com os dados do contrato-modelo
            document.getElementById('id_contrato_modelo').value = data.id_contrato_modelo;
            document.getElementById('id_modelo').value = data.id_modelo;
            document.getElementById('id_contrato').value = data.id_contrato;

            if (data.ativo) {
                document.getElementById('ativo_sim').checked = true;
            } else {
                document.getElementById('ativo_nao').checked = true;
            }

            // Rolar até o formulário
            document.getElementById('contrato-modelo-form').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Clona um vínculo contrato-modelo existente
 * @param {number} idContratoModelo 
 */
function clonarContratoModelo(idContratoModelo) {
    if (!confirm('Deseja realmente clonar este vínculo contrato-modelo?')) {
        return;
    }

    fetch(`/admin/api/contratosmodelo/${idContratoModelo}/clonar`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao clonar vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Vínculo contrato-modelo clonado com sucesso!');
                // Recarregar a página para mostrar o novo vínculo
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erro ao clonar vínculo contrato-modelo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Exclui um vínculo contrato-modelo
 * @param {number} idContratoModelo 
 */
function excluirContratoModelo(idContratoModelo) {
    if (!confirm('Deseja realmente excluir este vínculo contrato-modelo?')) {
        return;
    }

    fetch(`/admin/api/contratosmodelo/${idContratoModelo}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir vínculo contrato-modelo');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remover a linha da tabela
                removerLinhaContratoModelo(idContratoModelo);
                alert('Vínculo contrato-modelo excluído com sucesso!');
            } else {
                throw new Error(data.message || 'Erro ao excluir vínculo contrato-modelo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Adiciona uma nova linha na tabela de contratos-modelo
 * @param {Object} contratoModelo 
 */
function adicionarLinhaContratoModelo(contratoModelo) {
    const tabelaContratosModelo = document.getElementById('contratos-modelo-lista');
    if (!tabelaContratosModelo) return;

    // Verificar se a tabela está vazia
    const linhaVazia = tabelaContratosModelo.querySelector('tr td[colspan]');
    if (linhaVazia) {
        tabelaContratosModelo.innerHTML = '';
    }

    // Formatar valores
    const dataInclusao = contratoModelo.data_inclusao ? new Date(contratoModelo.data_inclusao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    const dataAlteracao = contratoModelo.data_alteracao ? new Date(contratoModelo.data_alteracao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    // Obter informações do modelo e contrato
    const modeloDescricao = contratoModelo.modelo ?
        `${contratoModelo.modelo.id_modelo_veiculo} - ${contratoModelo.modelo.descricao_modelo_veiculo}` :
        `${contratoModelo.id_modelo} - Modelo não encontrado`;

    // Criar a nova linha
    const novaLinha = document.createElement('tr');
    novaLinha.className = 'bg-white border-b hover:bg-gray-50';
    novaLinha.dataset.id = contratoModelo.id_contrato_modelo;

    novaLinha.innerHTML = `
        <td class="py-3 px-6">${contratoModelo.id_contrato_modelo}</td>
        <td class="py-3 px-6">${modeloDescricao}</td>
        <td class="py-3 px-6">${contratoModelo.id_contrato}</td>
        <td class="py-3 px-6">${contratoModelo.ativo ? 'Sim' : 'Não'}</td>
        <td class="py-3 px-6">${dataInclusao}</td>
        <td class="py-3 px-6">${dataAlteracao}</td>
        <td class="py-3 px-6">
            <div class="flex space-x-2">
                <button type="button" onclick="editarContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button type="button" onclick="clonarContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                    </svg>
                </button>
                <button type="button" onclick="excluirContratoModelo(${contratoModelo.id_contrato_modelo})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    `;

    // Adicionar à tabela
    tabelaContratosModelo.appendChild(novaLinha);
}

/**
 * Atualiza uma linha existente na tabela de contratos-modelo
 * @param {Object} contratoModelo 
 */
function atualizarLinhaContratoModelo(contratoModelo) {
    const linha = document.querySelector(`#contratos-modelo-lista tr[data-id="${contratoModelo.id_contrato_modelo}"]`);
    if (!linha) {
        // Se a linha não existir, adicionar como nova
        adicionarLinhaContratoModelo(contratoModelo);
        return;
    }

    // Formatar valores
    const dataAlteracao = contratoModelo.data_alteracao ? new Date(contratoModelo.data_alteracao).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : '-';

    // Obter informações do modelo
    const modeloDescricao = contratoModelo.modelo ?
        `${contratoModelo.modelo.id_modelo_veiculo} - ${contratoModelo.modelo.descricao_modelo_veiculo}` :
        `${contratoModelo.id_modelo} - Modelo não encontrado`;

    // Atualizar os dados nas colunas
    linha.children[1].textContent = modeloDescricao;
    linha.children[2].textContent = contratoModelo.id_contrato;
    linha.children[3].textContent = contratoModelo.ativo ? 'Sim' : 'Não';
    linha.children[5].textContent = dataAlteracao;
}

/**
 * Remove uma linha da tabela de contratos-modelo
 * @param {number} idContratoModelo 
 */
function removerLinhaContratoModelo(idContratoModelo) {
    const linha = document.querySelector(`#contratos-modelo-lista tr[data-id="${idContratoModelo}"]`);
    if (linha) {
        linha.remove();

        // Se a tabela ficou vazia, mostrar mensagem
        const tabelaContratosModelo = document.getElementById('contratos-modelo-lista');
        if (tabelaContratosModelo && tabelaContratosModelo.children.length === 0) {
            tabelaContratosModelo.innerHTML = `
                <tr class="bg-white border-b">
                    <td colspan="7" class="py-3 px-6 text-center text-gray-500">Nenhum vínculo contrato-modelo cadastrado</td>
                </tr>
            `;
        }
    }
}