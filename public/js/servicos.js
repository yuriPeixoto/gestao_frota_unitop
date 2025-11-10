/**
 * Scripts para gerenciamento de serviços no módulo de fornecedor
 */

// Funções para gerenciar o formulário de serviços
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa os handlers dos serviços
    initServicoHandlers();
});

/**
 * Inicializa os handlers para o formulário de serviços
 */
function initServicoHandlers() {
    // Botão para adicionar serviço
    const btnAdicionarServico = document.getElementById('btn_adicionar_servico');
    if (btnAdicionarServico) {
        btnAdicionarServico.addEventListener('click', function () {
            adicionarServico();
        });
    }

    // Carregamento dinâmico de serviços ao selecionar um grupo
    const grupoServicoSelect = document.getElementById('id_grupo_servico');
    if (grupoServicoSelect) {
        grupoServicoSelect.addEventListener('change', function () {
            carregarServicos(this.value);
        });
    }

    // Carregamento dinâmico de modelos ao selecionar um contrato
    const contratoServicoSelect = document.getElementById('id_contrato_servico');
    if (contratoServicoSelect) {
        contratoServicoSelect.addEventListener('change', function () {
            carregarContratosModelo(this.value, 'id_contrato_modelo_servico');
        });
    }
}

/**
 * Carrega os serviços do grupo selecionado
 * @param {number} idGrupoServico 
 */
function carregarServicos(idGrupoServico) {
    if (!idGrupoServico) {
        // Limpar o select de serviços
        const servicoSelect = document.getElementById('id_servico');
        servicoSelect.innerHTML = '<option value="">Selecione...</option>';
        return;
    }

    // Fazer requisição para buscar os serviços do grupo
    fetch(`/admin/api/grupos/${idGrupoServico}/servicos`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar serviços');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o select de serviços
            const servicoSelect = document.getElementById('id_servico');
            servicoSelect.innerHTML = '<option value="">Selecione...</option>';

            data.forEach(servico => {
                const option = document.createElement('option');
                option.value = servico.id_servico;
                option.textContent = servico.descricao;
                servicoSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar serviços. Por favor, tente novamente.');
        });
}

/**
 * Carrega os contratos-modelo vinculados ao contrato selecionado
 * @param {number} idContrato 
 * @param {string} selectId - ID do elemento select para preencher
 */
function carregarContratosModelo(idContrato, selectId) {
    if (!idContrato) {
        // Limpar o select de contratos-modelo
        const contratoModeloSelect = document.getElementById(selectId);
        contratoModeloSelect.innerHTML = '<option value="">Selecione...</option>';
        return;
    }

    // Fazer requisição para buscar os contratos-modelo do contrato
    fetch(`/admin/api/contratos/${idContrato}/modelos`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar contratos-modelo');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o select de contratos-modelo
            const contratoModeloSelect = document.getElementById(selectId);
            contratoModeloSelect.innerHTML = '<option value="">Selecione...</option>';

            data.forEach(contratoModelo => {
                const option = document.createElement('option');
                option.value = contratoModelo.id_contrato_modelo;
                option.textContent = `${contratoModelo.id_contrato_modelo} - ${contratoModelo.modelo.descricao_modelo_veiculo}`;
                contratoModeloSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar contratos-modelo. Por favor, tente novamente.');
        });
}

/**
 * Adiciona um novo serviço ao fornecedor
 */
function adicionarServico() {
    // Validar o formulário
    if (!validarFormularioServico()) {
        return;
    }

    // Obter os dados do formulário
    const formData = new FormData();
    formData.append('id_fornecedor', document.getElementById('contrato_fornecedor_fornecedor_id_fornecedor').value);
    formData.append('id_contrato', document.getElementById('id_contrato_servico').value);
    formData.append('id_grupo', document.getElementById('id_grupo_servico').value);
    formData.append('id_servico', document.getElementById('id_servico').value);
    formData.append('valor_servico', document.getElementById('valor_servico').value);
    formData.append('ativo', document.querySelector('input[name="ativo_servico"]:checked').value);

    const idContratoModelo = document.getElementById('id_contrato_modelo_servico').value;
    if (idContratoModelo) {
        formData.append('id_contrato_modelo', idContratoModelo);
    }

    // Verificar se está editando ou criando
    const idServicoForn = document.getElementById('id_servico_forn').value;
    if (idServicoForn) {
        formData.append('id_servico_forn', idServicoForn);
    }

    // Enviar a requisição
    const url = idServicoForn
        ? `/admin/api/servicos/${idServicoForn}`
        : '/admin/api/servicos';

    const method = idServicoForn ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao salvar serviço');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Se for uma edição, atualizar a linha na tabela
                if (idServicoForn) {
                    atualizarLinhaServico(data.servico);
                }
                // Se for uma inclusão, adicionar nova linha na tabela
                else {
                    adicionarLinhaServico(data.servico);
                }

                // Limpar o formulário
                limparFormularioServico();

                // Exibir mensagem de sucesso
                alert(data.message || 'Serviço salvo com sucesso!');
            } else {
                throw new Error(data.message || 'Erro ao salvar serviço');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Valida o formulário de serviço
 * @returns {boolean} true se o formulário for válido
 */
function validarFormularioServico() {
    // Verificar campos obrigatórios
    const idContrato = document.getElementById('id_contrato_servico').value;
    const idGrupo = document.getElementById('id_grupo_servico').value;
    const idServico = document.getElementById('id_servico').value;
    const valorServico = document.getElementById('valor_servico').value;

    if (!idContrato) {
        alert('Selecione um contrato.');
        return false;
    }

    if (!idGrupo) {
        alert('Selecione um grupo de serviço.');
        return false;
    }

    if (!idServico) {
        alert('Selecione um serviço.');
        return false;
    }

    if (!valorServico || parseFloat(valorServico) <= 0) {
        alert('Informe um valor válido para o serviço.');
        return false;
    }

    return true;
}

/**
 * Limpa o formulário de serviço
 */
function limparFormularioServico() {
    document.getElementById('id_servico_forn').value = '';
    document.getElementById('id_contrato_servico').value = '';
    document.getElementById('id_contrato_modelo_servico').innerHTML = '<option value="">Selecione...</option>';
    document.getElementById('id_grupo_servico').value = '';
    document.getElementById('id_servico').innerHTML = '<option value="">Selecione...</option>';
    document.getElementById('valor_servico').value = '';
    document.getElementById('ativo_servico_sim').checked = true;
}

/**
 * Edita um serviço existente
 * @param {number} idServicoForn 
 */
function editarServico(idServicoForn) {
    // Buscar os dados do serviço
    fetch(`/admin/api/servicos/${idServicoForn}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao buscar serviço');
            }
            return response.json();
        })
        .then(data => {
            // Preencher o formulário com os dados do serviço
            document.getElementById('id_servico_forn').value = data.id_servico_forn;
            document.getElementById('id_contrato_servico').value = data.id_contrato;

            // Carregar os contratos-modelo e depois selecionar o correto
            carregarContratosModelo(data.id_contrato, 'id_contrato_modelo_servico');

            // Selecionar o grupo e carregar os serviços
            document.getElementById('id_grupo_servico').value = data.id_grupo;
            carregarServicos(data.id_grupo);

            // Após um pequeno delay para garantir que os selects sejam preenchidos
            setTimeout(() => {
                if (data.id_contrato_modelo) {
                    document.getElementById('id_contrato_modelo_servico').value = data.id_contrato_modelo;
                }

                if (data.id_servico) {
                    document.getElementById('id_servico').value = data.id_servico;
                }
            }, 500);

            document.getElementById('valor_servico').value = data.valor_servico;

            if (data.ativo) {
                document.getElementById('ativo_servico_sim').checked = true;
            } else {
                document.getElementById('ativo_servico_nao').checked = true;
            }

            // Rolar até o formulário
            document.getElementById('servico-form').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Exclui um serviço
 * @param {number} idServicoForn 
 */
function excluirServico(idServicoForn) {
    if (!confirm('Deseja realmente excluir este serviço?')) {
        return;
    }

    fetch(`/admin/api/servicos/${idServicoForn}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao excluir serviço');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remover a linha da tabela
                removerLinhaServico(idServicoForn);
                alert('Serviço excluído com sucesso!');
            } else {
                throw new Error(data.message || 'Erro ao excluir serviço');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert(error.message);
        });
}

/**
 * Adiciona uma nova linha na tabela de serviços
 * @param {Object} servico 
 */
function adicionarLinhaServico(servico) {
    const tabelaServicos = document.getElementById('servicos-lista');
    if (!tabelaServicos) return;

    // Verificar se a tabela está vazia
    const linhaVazia = tabelaServicos.querySelector('tr td[colspan]');
    if (linhaVazia) {
        tabelaServicos.innerHTML = '';
    }

    // Formatar valores
    const valorFormatado = servico.valor_servico
        ? 'R$ ' + parseFloat(servico.valor_servico).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    // Criar a nova linha
    const novaLinha = document.createElement('tr');
    novaLinha.className = 'bg-white border-b hover:bg-gray-50';
    novaLinha.dataset.id = servico.id_servico_forn;

    // Dados dos relacionamentos
    const grupoDescricao = servico.grupo_servico?.descricao_grupo || '-';
    const servicoDescricao = servico.servico?.descricao || '-';
    const contratoId = servico.contrato?.id_contrato_forn || '-';
    const modeloDescricao = servico.contrato_modelo?.modelo?.descricao_modelo_veiculo || '-';

    novaLinha.innerHTML = `
        <td class="py-3 px-6">${servico.id_servico_forn}</td>
        <td class="py-3 px-6">${grupoDescricao}</td>
        <td class="py-3 px-6">${servicoDescricao}</td>
        <td class="py-3 px-6">${contratoId}</td>
        <td class="py-3 px-6">${modeloDescricao}</td>
        <td class="py-3 px-6">${valorFormatado}</td>
        <td class="py-3 px-6">${servico.ativo ? 'Sim' : 'Não'}</td>
        <td class="py-3 px-6">
            <div class="flex space-x-2">
                <button type="button" onclick="editarServico(${servico.id_servico_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button type="button" onclick="excluirServico(${servico.id_servico_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    `;

    // Adicionar à tabela
    tabelaServicos.appendChild(novaLinha);
}

/**
 * Atualiza uma linha existente na tabela de serviços
 * @param {Object} servico 
 */
function atualizarLinhaServico(servico) {
    const linha = document.querySelector(`#servicos-lista tr[data-id="${servico.id_servico_forn}"]`);
    if (!linha) {
        // Se a linha não existir, adicionar como nova
        adicionarLinhaServico(servico);
        return;
    }

    // Formatar valores
    const valorFormatado = servico.valor_servico
        ? 'R$ ' + parseFloat(servico.valor_servico).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : 'R$ 0,00';

    // Dados dos relacionamentos
    const grupoDescricao = servico.grupo_servico?.descricao_grupo || '-';
    const servicoDescricao = servico.servico?.descricao || '-';
    const contratoId = servico.contrato?.id_contrato_forn || '-';
    const modeloDescricao = servico.contrato_modelo?.modelo?.descricao_modelo_veiculo || '-';

    // Atualizar os dados nas colunas
    linha.children[1].textContent = grupoDescricao;
    linha.children[2].textContent = servicoDescricao;
    linha.children[3].textContent = contratoId;
    linha.children[4].textContent = modeloDescricao;
    linha.children[5].textContent = valorFormatado;
    linha.children[6].textContent = servico.ativo ? 'Sim' : 'Não';
}

/**
 * Remove uma linha da tabela de serviços
 * @param {number} idServicoForn 
 */
function removerLinhaServico(idServicoForn) {
    const linha = document.querySelector(`#servicos-lista tr[data-id="${idServicoForn}"]`);
    if (linha) {
        linha.remove();

        // Se a tabela ficou vazia, mostrar mensagem
        const tabelaServicos = document.getElementById('servicos-lista');
        if (tabelaServicos && tabelaServicos.children.length === 0) {
            tabelaServicos.innerHTML = `
                <tr class="bg-white border-b">
                    <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhum serviço cadastrado</td>
                </tr>
            `;
        }
    }
}