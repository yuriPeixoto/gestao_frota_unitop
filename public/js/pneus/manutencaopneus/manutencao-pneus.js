window.onload = function () {
    const inputhidden = document.getElementById("historicos_json").value;
    const pneuManutencao = inputhidden != '' ? JSON.parse(inputhidden) : [];

    const pneuCache = new Map();

    // Variável para controlar modo de edição
    let editandoIndex = -1;

    function atualizarTabela() {
        const tabela = document.getElementById('tabelaHistoricoBody');

        if (!tabela) {
            console.error('Elemento #tabelaHistoricoBody não encontrado');
            return;
        }

        tabela.innerHTML = '';

        pneuManutencao.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${item.data_inclusao}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.data_alteracao ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.id_pneu}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.modelo_descricao || 'Carregando...'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.tipo_reforma_descricao || 'Carregando...'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <!-- Botão Editar -->
                        <div class="cursor-pointer edit-pneu text-blue-600 hover:text-blue-800" data-index="${index}" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </div>
                        <!-- Botão Excluir -->
                        <div class="cursor-pointer delete-pneu text-red-600 hover:text-red-800" data-index="${index}" title="Excluir">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244 2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            // Event listener para editar
            tr.querySelector(".edit-pneu").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                editarHistorico(index);
            });

            // Event listener para excluir
            tr.querySelector(".delete-pneu").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                excluir(index);
            });

            tabela.appendChild(tr);
        });
    }

    function excluir(index) {
        if (confirm('Tem certeza que deseja excluir este item?')) {
            pneuManutencao.splice(index, 1);
            document.getElementById('historicos_json').value = JSON.stringify(pneuManutencao);
            atualizarTabela();

            // Se estava editando este item, cancelar edição
            if (editandoIndex === index) {
                cancelarEdicao();
            } else if (editandoIndex > index) {
                // Ajustar índice se estava editando item posterior
                editandoIndex--;
            }
        }
    }

    function editarHistorico(index) {
        // Se já está editando outro item, cancela
        if (editandoIndex !== -1 && editandoIndex !== index) {
            if (!confirm('Você está editando outro item. Deseja cancelar a edição atual?')) {
                return;
            }
            cancelarEdicao();
        }

        const item = pneuManutencao[index];
        editandoIndex = index;

        // Preencher formulário com dados do item
        setSmartSelectValue('id_pneu', item.id_pneu, {
            createIfNotFound: true,
            tempLabel: item.id_pneu
        });
        setSmartSelectValue('id_pneu', item.id_pneu || '');
        setSmartSelectValue('id_tipo_manutencao', item.id_tipo_manutencao || '');

        document.querySelector('[name="id_tipo_modelo_pneu"]').value = item.modelo_descricao || '';

        // Alterar texto do botão para "Atualizar"
        alterarBotaoParaEdicao();

        // Scroll para o formulário
        const firstInput = document.querySelector('[name="id_filial"]');
        if (firstInput) {
            firstInput.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            firstInput.focus();
        }

        console.log('Editando item:', item);
    }

    function alterarBotaoParaEdicao() {
        const botao = document.querySelector('[onclick="adicionarHistorico()"]') ||
            document.querySelector('button[onclick="adicionarHistorico()"]') ||
            document.getElementById('btn-adicionar-historico');

        if (botao) {
            botao.textContent = 'Atualizar Histórico';
            botao.classList.remove('bg-blue-500', 'hover:bg-blue-700');
            botao.classList.add('bg-green-500', 'hover:bg-green-700');

            // Adicionar botão cancelar se não existir
            if (!document.getElementById('btn-cancelar-edicao')) {
                const btnCancelar = document.createElement('button');
                btnCancelar.type = 'button';
                btnCancelar.id = 'btn-cancelar-edicao';
                btnCancelar.textContent = 'Cancelar';
                btnCancelar.className = 'ml-2 px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white font-bold rounded';
                btnCancelar.onclick = cancelarEdicao;

                // Inserir após o botão principal
                if (botao.parentNode) {
                    botao.parentNode.insertBefore(btnCancelar, botao.nextSibling);
                }
            }
        }
    }

    function cancelarEdicao() {
        editandoIndex = -1;

        // Limpar SmartSelect ou input normal
        teste = getSmartSelectValue('id_pneu');
        setSmartSelectValue('id_pneu', teste.values);
        document.querySelector('[name="id_tipo_modelo_pneu"]').value = '';

        // Restaurar botão original
        const botao = document.querySelector('button[type="button"]') ||
            document.getElementById('btn-adicionar-historico');
        if (botao && botao.textContent === 'Atualizar Histórico') {
            botao.textContent = 'Adicionar Histórico';
            botao.classList.remove('bg-green-500', 'hover:bg-green-700');
            botao.classList.add('bg-blue-500', 'hover:bg-blue-700');
        }

        // Remover botão cancelar
        const btnCancelar = document.getElementById('btn-cancelar-edicao');
        if (btnCancelar) {
            btnCancelar.remove();
        }
    }

    async function adicionarHistorico() {
        const id_filial = document.querySelector('[name="id_filial"]').value;
        const id_fornecedor = document.querySelector('[name="id_fornecedor"]').value;
        //const nf_envio = document.querySelector('[name="nf_envio"]').value;
        //const chave_nf_envio = document.querySelector('[name="chave_nf_envio"]').value;
        const id_pneu = document.querySelector('[name="id_pneu"]').value;
        const id_modelo_pneu = document.querySelector('[name="id_tipo_modelo_pneu"]').value;
        const id_tipo_manutencao = document.querySelector('[name="id_tipo_manutencao"]').value;

        // Validar campos obrigatórios
        if (
            id_filial != '' &&
            id_fornecedor != '' &&
            //nf_envio != '' &&
            //chave_nf_envio != '' &&
            id_pneu != '' &&
            id_tipo_manutencao != ''
        ) {
            try {
                // Buscar dados do pneu de forma assíncrona
                const modeloPneu = await buscarDadosPneu(id_pneu);
                const tipoReforma = await buscarTipoReforma(id_tipo_manutencao);

                let manutencao = {
                    data_inclusao: new Date().toLocaleDateString(),
                    id_filial: id_filial,
                    id_fornecedor: id_fornecedor,
                    //nf_envio: nf_envio,
                    //chave_nf_envio: chave_nf_envio,
                    id_pneu: id_pneu,
                    id_modelo_pneu: id_modelo_pneu,
                    id_tipo_manutencao: id_tipo_manutencao,
                    modelo_descricao: modeloPneu,
                    tipo_reforma_descricao: tipoReforma
                };

                if (editandoIndex !== -1) {
                    // Modo edição - atualizar item existente
                    manutencao.data_alteracao = new Date().toLocaleDateString();
                    pneuManutencao[editandoIndex] = manutencao;
                    console.log('Item atualizado:', manutencao);
                    cancelarEdicao();
                } else {
                    // Modo adição - adicionar novo item
                    pneuManutencao.push(manutencao);
                    console.log('Item adicionado:', manutencao);

                    // Limpar formulário apenas se não estava editando
                    limparFormulario();
                }

                // Atualizar campo hidden e tabela
                document.getElementById('historicos_json').value = JSON.stringify(pneuManutencao);
                atualizarTabela();

            } catch (error) {
                console.error('Erro ao buscar dados do pneu:', error);
                alert('Erro ao buscar dados do pneu. Tente novamente.');
            }

            return;
        }

        alert('Preencha todos os campos!');
    }

    function limparFormulario() {
        // Limpar apenas os campos que devem ser limpos após adicionar
        if (typeof setSmartSelectValue === 'function') {
            setSmartSelectValue('id_pneu', null);
            setSmartSelectValue('id_tipo_manutencao', null);
        } else {
            const pneuInput = document.querySelector('[name="id_pneu"]');
            const tipoSelect = document.querySelector('[name="id_tipo_manutencao"]');
            if (pneuInput) pneuInput.value = '';
            if (tipoSelect) tipoSelect.value = '';
        }
        document.querySelector('[name="id_tipo_modelo_pneu"]').value = '';
    }

    async function buscarDadosPneu(id) {
        // Verificar cache primeiro
        if (pneuCache.has(id)) {
            return pneuCache.get(id);
        }

        try {
            // Obter o token CSRF dinamicamente
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            // Adicionar CSRF token se disponível
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/admin/pneus/api/${id}`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Erro na resposta da API: ${response.status} - ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Dados recebidos da API:', data);

            let modeloDescricao = 'Modelo não informado';

            // Verificar a estrutura dos dados retornados
            if (data) {
                // Se retornou com structure de resource individual
                if (data.modelo && data.modelo.descricao_modelo) {
                    modeloDescricao = data.modelo.descricao_modelo.trim();
                }
                // Se retornou com structure de collection
                else if (data.data && data.data.modelo && data.data.modelo.descricao_modelo) {
                    modeloDescricao = data.data.modelo.descricao_modelo.trim();
                }
                // Fallback para estrutura original
                else if (data.modelo_pneu && data.modelo_pneu.descricao_modelo) {
                    modeloDescricao = data.modelo_pneu.descricao_modelo.trim();
                }
            }

            // Armazenar no cache
            pneuCache.set(id, modeloDescricao);
            return modeloDescricao;

        } catch (error) {
            console.error(`Erro ao buscar dados do pneu ${id}:`, error);
            const errorMessage = 'Erro ao buscar modelo';
            pneuCache.set(id, errorMessage);
            return errorMessage;
        }
    }

    async function buscarTipoReforma(id) {
        // Verificar cache primeiro
        if (pneuCache.has(id)) {
            return pneuCache.get(id);
        }

        try {
            // Obter o token CSRF dinamicamente
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            // Adicionar CSRF token se disponível
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(`/admin/tiporeformapneus/api/${id}`, {
                method: 'GET',
                headers: headers,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`Erro na resposta da API: ${response.status} - ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Dados recebidos da API tipo reforma:', data);

            let tipoReforma = 'Tipo Reforma não informado';

            // Verificar a estrutura dos dados retornados
            if (data) {
                tipoReforma = data.descricao_tipo_reforma;
            }

            // Armazenar no cache
            pneuCache.set(id, tipoReforma);
            return tipoReforma;

        } catch (error) {
            console.error(`Erro ao buscar dados do pneu ${id}:`, error);
            const errorMessage = 'Erro ao buscar o tipo de Reforma';
            pneuCache.set(id, errorMessage);
            return errorMessage;
        }
    }

    // Inicializar tabela
    atualizarTabela();

    // Expor funções globalmente
    window.adicionarHistorico = adicionarHistorico;
    window.atualizarTabela = atualizarTabela;
    window.excluir = excluir;
    window.editarHistorico = editarHistorico;
    window.cancelarEdicao = cancelarEdicao;
    window.buscarDadosPneu = buscarDadosPneu;
}