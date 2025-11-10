/**
 * Scripts para gerenciamento de peças no módulo de fornecedor
 */

// Funções para gerenciar o formulário de peças
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os handlers das peças
    initPecaHandlers();
});

/**
 * Inicializa os handlers para o formulário de peças
 */
function initPecaHandlers() {
    // Botão para adicionar peça
    const btnAdicionarPeca = document.getElementById('btn_adicionar_peca');
    if (btnAdicionarPeca) {
        btnAdicionarPeca.addEventListener('click', function() {
            adicionarPeca();
        });
    }

    // Carregamento dinâmico de produtos ao selecionar um grupo
    const grupoPecasSelect = document.getElementById('id_grupo_pecas');
    if (grupoPecasSelect) {
        grupoPecasSelect.addEventListener('change', function() {
            carregarProdutos(this.value);
        });
    }
    
    // Carregamento dinâmico de modelos ao selecionar um contrato
    const contratoPecaSelect = document.getElementById('id_contrato_peca');
    if (contratoPecaSelect) {
        contratoPecaSelect.addEventListener('change', function() {
            carregarContratosModelo(this.value, 'id_contrato_modelo_peca');
        });
    }
}

/**
 * Carrega os produtos do grupo selecionado
 * @param {number} idGrupoPecas 
 */
function carregarProdutos(idGrupoPecas) {
    if (!idGrupoPecas) {
        // Limpar o select de produtos
        const produtoSelect = document.getElementById('id_produto');
        produtoSelect.innerHTML = '<option value="">Selecione...</option>';
        return;
    }
    
    // Fazer requisição para buscar os produtos do grupo
    fetch(`/admin/api/grupos-pecas/${idGrupoPecas}/produtos`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao buscar produtos');
        }
        return response.json();
    })
    .then(data => {
        // Preencher o select de produtos
        const produtoSelect = document.getElementById('id_produto');
        produtoSelect.innerHTML = '<option value="">Selecione...</option>';
        
        data.forEach(produto => {
            const option = document.createElement('option');
            option.value = produto.id_produto;
            option.textContent = produto.descricao;
            produtoSelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar produtos. Por favor, tente novamente.');
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
 * Adiciona uma nova peça ao fornecedor
 */
function adicionarPeca() {
    // Validar o formulário
    if (!validarFormularioPeca()) {
        return;
    }

    // Obter os dados do formulário
    const formData = new FormData();
    formData.append('id_fornecedor', document.getElementById('contrato_fornecedor_fornecedor_id_fornecedor').value);
    formData.append('id_contrato', document.getElementById('id_contrato_peca').value);
    formData.append('id_grupo_pecas', document.getElementById('id_grupo_pecas').value);
    formData.append('id_produto', document.getElementById('id_produto').value);
    formData.append('valor_peca', document.getElementById('valor_peca').value);
    formData.append('ativo', document.querySelector('input[name="ativo_peca"]:checked').value);
    
    const idContratoModelo = document.getElementById('id_contrato_modelo_peca').value;
    if (idContratoModelo) {
        formData.append('id_contrato_modelo', idContratoModelo);
    }
    
    // Verificar se está editando ou criando
    const idPecasForn = document.getElementById('id_pecas_forn').value;
    if (idPecasForn) {
        formData.append('id_pecas_forn', idPecasForn);
    }

    // Enviar a requisição
    const url = idPecasForn 
        ? `/admin/api/pecas/${idPecasForn}` 
        : '/admin/api/pecas';
    
    const method = idPecasForn ? 'PUT' : 'POST';
    
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
            throw new Error('Erro ao salvar peça');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Se for uma edição, atualizar a linha na tabela
            if (idPecasForn) {
                atualizarLinhaPeca(data.peca);
            } 
            // Se for uma inclusão, adicionar nova linha na tabela
            else {
                adicionarLinhaPeca(data.peca);
            }
            
            // Limpar o formulário
            limparFormularioPeca();
            
            // Exibir mensagem de sucesso
            alert(data.message || 'Peça salva com sucesso!');
        } else {
            throw new Error(data.message || 'Erro ao salvar peça');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert(error.message);
    });
}

/**
 * Valida o formulário de peça
 * @returns {boolean} true se o formulário for válido
 */
function validarFormularioPeca() {
    // Verificar campos obrigatórios
    const idContrato = document.getElementById('id_contrato_peca').value;
    const idGrupoPecas = document.getElementById('id_grupo_pecas').value;
    const idProduto = document.getElementById('id_produto').value;
    const valorPeca = document.getElementById('valor_peca').value;
    
    if (!idContrato) {
        alert('Selecione um contrato.');
        return false;
    }
    
    if (!idGrupoPecas) {
        alert('Selecione um grupo de peças.');
        return false;
    }
    
    if (!idProduto) {
        alert('Selecione uma peça.');
        return false;
    }
    
    if (!valorPeca || parseFloat(valorPeca) <= 0) {
        alert('Informe um valor válido para a peça.');
        return false;
    }
    
    return true;
}

/**
 * Limpa o formulário de peça
 */
function limparFormularioPeca() {
    document.getElementById('id_pecas_forn').value = '';
    document.getElementById('id_contrato_peca').value = '';
    document.getElementById('id_contrato_modelo_peca').innerHTML = '<option value="">Selecione...</option>';
    document.getElementById('id_grupo_pecas').value = '';
    document.getElementById('id_produto').innerHTML = '<option value="">Selecione...</option>';
    document.getElementById('valor_peca').value = '';
    document.getElementById('ativo_peca_sim').checked = true;
}

/**
 * Edita uma peça existente
 * @param {number} idPecasForn 
 */
function editarPeca(idPecasForn) {
    // Buscar os dados da peça
    fetch(`/admin/api/pecas/${idPecasForn}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao buscar peça');
        }
        return response.json();
    })
    .then(data => {
        // Preencher o formulário com os dados da peça
        document.getElementById('id_pecas_forn').value = data.id_pecas_forn;
        document.getElementById('id_contrato_peca').value = data.id_contrato;
        
        // Carregar os contratos-modelo e depois selecionar o correto
        carregarContratosModelo(data.id_contrato, 'id_contrato_modelo_peca');
        
        // Selecionar o grupo e carregar os produtos
        document.getElementById('id_grupo_pecas').value = data.id_grupo_pecas;
        carregarProdutos(data.id_grupo_pecas);
        
        // Após um pequeno delay para garantir que os selects sejam preenchidos
        setTimeout(() => {
            if (data.id_contrato_modelo) {
                document.getElementById('id_contrato_modelo_peca').value = data.id_contrato_modelo;
            }
            
            if (data.id_produto) {
                document.getElementById('id_produto').value = data.id_produto;
            }
        }, 500);
        
        document.getElementById('valor_peca').value = data.valor_peca;
        
        if (data.ativo) {
            document.getElementById('ativo_peca_sim').checked = true;
        } else {
            document.getElementById('ativo_peca_nao').checked = true;
        }
        
        // Rolar até o formulário
        document.getElementById('peca-form').scrollIntoView({ behavior: 'smooth' });
    })
    .catch(error => {
        console.error('Erro:', error);
        alert(error.message);
    });
}

/**
 * Exclui uma peça
 * @param {number} idPecasForn 
 */
function excluirPeca(idPecasForn) {
    if (!confirm('Deseja realmente excluir esta peça?')) {
        return;
    }
    
    fetch(`/admin/api/pecas/${idPecasForn}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao excluir peça');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remover a linha da tabela
            removerLinhaPeca(idPecasForn);
            alert('Peça excluída com sucesso!');
        } else {
            throw new Error(data.message || 'Erro ao excluir peça');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert(error.message);
    });
}

/**
 * Adiciona uma nova linha na tabela de peças
 * @param {Object} peca 
 */
function adicionarLinhaPeca(peca) {
    const tabelaPecas = document.getElementById('pecas-lista');
    if (!tabelaPecas) return;
    
    // Verificar se a tabela está vazia
    const linhaVazia = tabelaPecas.querySelector('tr td[colspan]');
    if (linhaVazia) {
        tabelaPecas.innerHTML = '';
    }
    
    // Formatar valores
    const valorFormatado = peca.valor_peca 
        ? 'R$ ' + parseFloat(peca.valor_peca).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) 
        : 'R$ 0,00';
    
    // Criar a nova linha
    const novaLinha = document.createElement('tr');
    novaLinha.className = 'bg-white border-b hover:bg-gray-50';
    novaLinha.dataset.id = peca.id_pecas_forn;
    
    // Dados dos relacionamentos
    const grupoDescricao = peca.grupo_pecas?.descricao_grupo || '-';
    const produtoDescricao = peca.produto?.descricao || '-';
    const contratoId = peca.contrato?.id_contrato_forn || '-';
    const modeloDescricao = peca.contrato_modelo?.modelo?.descricao_modelo_veiculo || '-';
    
    novaLinha.innerHTML = `
        <td class="py-3 px-6">${peca.id_pecas_forn}</td>
        <td class="py-3 px-6">${grupoDescricao}</td>
        <td class="py-3 px-6">${produtoDescricao}</td>
        <td class="py-3 px-6">${contratoId}</td>
        <td class="py-3 px-6">${modeloDescricao}</td>
        <td class="py-3 px-6">${valorFormatado}</td>
        <td class="py-3 px-6">${peca.ativo ? 'Sim' : 'Não'}</td>
        <td class="py-3 px-6">
            <div class="flex space-x-2">
                <button type="button" onclick="editarPeca(${peca.id_pecas_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
                <button type="button" onclick="excluirPeca(${peca.id_pecas_forn})"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </td>
    `;
    
    // Adicionar à tabela
    tabelaPecas.appendChild(novaLinha);
}

/**
 * Atualiza uma linha existente na tabela de peças
 * @param {Object} peca 
 */
function atualizarLinhaPeca(peca) {
    const linha = document.querySelector(`#pecas-lista tr[data-id="${peca.id_pecas_forn}"]`);
    if (!linha) {
        // Se a linha não existir, adicionar como nova
        adicionarLinhaPeca(peca);
        return;
    }
    
    // Formatar valores
    const valorFormatado = peca.valor_peca 
        ? 'R$ ' + parseFloat(peca.valor_peca).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) 
        : 'R$ 0,00';
    
    // Dados dos relacionamentos
    const grupoDescricao = peca.grupo_pecas?.descricao_grupo || '-';
    const produtoDescricao = peca.produto?.descricao || '-';
    const contratoId = peca.contrato?.id_contrato_forn || '-';
    const modeloDescricao = peca.contrato_modelo?.modelo?.descricao_modelo_veiculo || '-';
    
    // Atualizar os dados nas colunas
    linha.children[1].textContent = grupoDescricao;
    linha.children[2].textContent = produtoDescricao;
    linha.children[3].textContent = contratoId;
    linha.children[4].textContent = modeloDescricao;
    linha.children[5].textContent = valorFormatado;
    linha.children[6].textContent = peca.ativo ? 'Sim' : 'Não';
}

/**
 * Remove uma linha da tabela de peças
 * @param {number} idPecasForn 
 */
function removerLinhaPeca(idPecasForn) {
    const linha = document.querySelector(`#pecas-lista tr[data-id="${idPecasForn}"]`);
    if (linha) {
        linha.remove();
        
        // Se a tabela ficou vazia, mostrar mensagem
        const tabelaPecas = document.getElementById('pecas-lista');
        if (tabelaPecas && tabelaPecas.children.length === 0) {
            tabelaPecas.innerHTML = `
                <tr class="bg-white border-b">
                    <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhuma peça cadastrada</td>
                </tr>
            `;
        }
    }
}