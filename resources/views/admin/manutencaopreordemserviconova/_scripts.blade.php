<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja excluir a Pré O.S?')) {
            excluirpreosnova(id);
        }
    }

    function excluirpreosnova(id) {
        fetch(`/admin/manutencaopreordemserviconova/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/pdf'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(' Pré O.S excluído com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Pré O.S');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Pré O.S');
            });
    }

    function excludeFromList(id) {
        const item = document.getElement
    }

    function confirmarAssumir(id) {
        if (confirm('Deseja assumir a Pré O.S?')) {
            assumirPreOs(id);
        }
    }

    function assumirPreOs(id) {
        fetch(`/admin/manutencaopreordemserviconova/assumirpreos/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(' Pré O.S assumida com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao assumir Pré O.S');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao assumir Pré O.S');
            });
    }

    function gerarCorretiva(id) {
        if (confirm(
                'Tem Certeza de que Gostaria de Seguir em Frente com esta Operação? Se não existir uma O.S Corretiva aberta para este veículo, uma nova O.S Corretiva será gerada com os serviços selecionados na Pré-O.S.'
            )) {
            corretiva(id);
        }
    }

    function corretiva(id) {
        fetch(`/admin/manutencaopreordemserviconova/gerarcorretiva/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data);
                    alert('A O.S foi gerada com sucesso');
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Erro ao gerar O.S Corretiva');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao gerar O.S Corretiva');
            });
    }
</script>

<script>
    function getInfoVeiculos() {
        const idVeiculo = document.getElementById('id_veiculo').value;
        const category = document.getElementById('categoria');
        const model = document.getElementById('modelo');
        const tipoEquipamento = document.getElementById('tipoEquipamento');

        const data = {
            id_veiculo: idVeiculo
        };

        if (idVeiculo) {
            fetch(`/admin/manutencaopreordemserviconova/getInfoVeiculo`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(responseData => { // Renomeei para responseData para ficar mais claro
                    if (!responseData.error) {
                        category.value = responseData.data.categoria;
                        model.value = responseData.data.modelo;
                        tipoEquipamento.value = responseData.data.tipo_equipamento;
                    } else {
                        alert(responseData.message || 'Erro ao obter informações do veículo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao obter informações do veículo');
                });
        }
    }

    function getTelefoneMotorista() {
        const idMotorista = document.getElementById('id_motorista').value;
        const telefone = document.getElementById('telefone_motorista');

        const data = {
            id_motorista: idMotorista
        };

        if (idMotorista) {
            fetch(`/admin/manutencaopreordemserviconova/getTelefoneMotorista`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(responseData => { // Renomeei para responseData para ficar mais claro
                    if (!responseData.error) {
                        telefone.value = responseData.data.telefone_motorista;;
                    } else {
                        alert(responseData.message || 'Erro ao obter informações do motorista');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao obter informações do motorista');
                });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        getInfoVeiculos();
        getTelefoneMotorista();
    });
</script>

<script>
    const preOrdemServicoServicos = @json($ordemServicoServicos ?? []);

    const servico = @json($servico ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularServicoTabela();
    });

    function popularServicoTabela() {
        const tbody = document.getElementById('tabelaServicoBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        preOrdemServicoServicos.forEach((item, index) => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap"> - </td>
            <td class="px-6 py-4 whitespace-nowrap">${servico[item.id_servico] ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.observacao ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <div class="cursor-pointer edit-servico" data-index="${index}" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </div>
                        <div class="cursor-pointer delete-produto" data-index="${index}" title="Excluir">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            // Event listener para editar
            tr.querySelector(".edit-servico").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                editarServico(index);
            });

            // Event listener para excluir
            tr.querySelector(".delete-produto").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                removerServico(index);
            });

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerServico(index) {
        preOrdemServicoServicos.splice(index, 1);
        atualizarCampoHidden();
        popularServicoTabela();
    }

    function editarServico(index) {
        const item = preOrdemServicoServicos[index];
        removerServico(item);

        // Preenche os campos com os dados do item selecionado
        const textareaObservacao = document.querySelector('[name="observacao"]');

        // Define os valores nos campos
        setSmartSelectValue('id_servico', item.id_servico);
        textareaObservacao.value = item.observacao;

        // Adiciona um atributo para identificar que estamos editando
        selectServico.dataset.editingIndex = index;

        // Muda o texto do botão para "Atualizar"
        const botaoAdicionar = document.querySelector('button[onclick="adicionarServico()"]');
        botaoAdicionar.textContent = 'Atualizar Serviço';
        botaoAdicionar.setAttribute('onclick', 'atualizarServico()');

        // Adiciona botão de cancelar
        if (!document.getElementById('btnCancelarEdicao')) {
            const btnCancelar = document.createElement('button');
            btnCancelar.id = 'btnCancelarEdicao';
            btnCancelar.type = 'button';
            btnCancelar.className =
                'ml-2 inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150';
            btnCancelar.textContent = 'Cancelar';
            btnCancelar.onclick = cancelarEdicao;

            botaoAdicionar.parentNode.appendChild(btnCancelar);
        }


    }

    function atualizarServico() {
        const selectServico = document.querySelector('[name="id_servico"]');
        const textareaObservacao = document.querySelector('[name="observacao"]');
        const index = parseInt(selectServico.dataset.editingIndex);

        if (!selectServico.value || !textareaObservacao.value) {
            alert('Preencha todos os campos para atualizar o serviço.');
            return;
        }

        // Atualiza o item no array
        preOrdemServicoServicos[index] = {
            ...preOrdemServicoServicos[index],
            id_servico: selectServico.value,
            observacao: textareaObservacao.value,
        };

        // Limpa os campos e restaura o estado original
        limparCampos();
        cancelarEdicao();

        // Atualiza o campo hidden com o JSON
        atualizarCampoHidden();

        // Atualiza a tabela
        popularServicoTabela();
    }

    function cancelarEdicao() {
        const selectServico = document.querySelector('[name="id_servico"]');
        const botaoAdicionar = document.querySelector('button[onclick="atualizarServico()"]');
        const btnCancelar = document.getElementById('btnCancelarEdicao');

        // Remove o atributo de edição
        delete selectServico.dataset.editingIndex;

        // Restaura o botão original
        if (botaoAdicionar) {
            botaoAdicionar.textContent = 'Adicionar Serviço';
            botaoAdicionar.setAttribute('onclick', 'adicionarServico()');
        }

        // Remove o botão cancelar
        if (btnCancelar) {
            btnCancelar.remove();
        }

        // Limpa os campos
        limparCampos();
    }

    function limparCampos() {
        clearSmartSelect('id_servico');
        const textareaObservacao = document.querySelector('[name="observacao"]');

        textareaObservacao.value = '';
    }

    function atualizarCampoHidden() {
        document.getElementById('servicos_json').value = JSON.stringify(preOrdemServicoServicos);
    }

    function adicionarServico() {

        const id_servico = getSmartSelectValue('id_servico').value;
        const observacao = document.querySelector('[name="observacao"]').value;

        if (!id_servico || !observacao) {
            alert('Preencha todos os campos para adicionar o serviço.');
            return;
        }

        const novoItem = {
            id_servico: id_servico,
            observacao: observacao,
            data_inclusao: new Date(),
        };

        preOrdemServicoServicos.push(novoItem);

        // Limpa os campos após adicionar
        limparCampos();

        // Atualiza o campo hidden com o JSON
        atualizarCampoHidden();

        popularServicoTabela();
    }
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('utils', {
            loading: false,

            // Método para mostrar alertas
            _mostrarAlerta(mensagem) {
                alert(mensagem);
            },

            // Método para criar e mostrar loading
            _mostrarLoading() {
                // Remove loading anterior se existir
                this._removerLoading();

                const loadingMessage = document.createElement('div');
                loadingMessage.id = 'loading-message';
                loadingMessage.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 20px;
                    border-radius: 8px;
                    z-index: 9999;
                    text-align: center;
                    font-family: Arial, sans-serif;
                `;
                loadingMessage.innerHTML = `
                    <div style="margin-bottom: 10px;">
                        <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
                    </div>
                    <div>Gerando relatório...</div>
                    <div style="font-size: 12px; margin-top: 5px;">Isso pode levar alguns minutos</div>
                `;

                // Adicionar animação de carregamento se não existir
                if (!document.getElementById('loading-animation-style')) {
                    const style = document.createElement('style');
                    style.id = 'loading-animation-style';
                    style.textContent = `
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `;
                    document.head.appendChild(style);
                }

                document.body.appendChild(loadingMessage);
            },

            // Método para remover loading
            _removerLoading() {
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
            },

            // Método para fazer download do arquivo
            _fazerDownload(blob, nomeArquivo) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = nomeArquivo;

                // Adicionar ao DOM temporariamente para garantir compatibilidade
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                // Limpar o URL do blob
                window.URL.revokeObjectURL(url);
            },

            // Método para obter token CSRF
            _obterCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                    'content');
                if (!token) {
                    throw new Error('Token CSRF não encontrado');
                }
                return token;
            },

            // Método genérico para fazer requisições
            async _fazerRequisicao(url, data, timeout = 300000) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this._obterCSRFToken(),
                            'Accept': 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json'
                        },
                        body: JSON.stringify(data),
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        // Tentar ler como JSON para pegar mensagem de erro
                        try {
                            const errorData = await response.json();
                            throw new Error(errorData.message ||
                                `Erro ${response.status}: ${response.statusText}`);
                        } catch {
                            throw new Error(`Erro ${response.status}: ${response.statusText}`);
                        }
                    }

                    return response;
                } catch (error) {
                    clearTimeout(timeoutId);
                    throw error;
                }
            },

            // Método para imprimir PDF
            async imprimirPreOs(id) {
                this.loading = true;

                try {
                    const data = {
                        id: id
                    };

                    this._mostrarLoading();

                    const response = await this._fazerRequisicao(
                        '/admin/manutencaopreordemserviconova/imprimir', data);
                    const blob = await response.blob();

                    this._fazerDownload(blob,
                        `relatorio_pre_os_${new Date().toISOString().split('T')[0]}.pdf`);

                } catch (error) {
                    console.error('Erro ao gerar relatório PDF:', error);

                    if (error.name === 'AbortError') {
                        this._mostrarAlerta(
                            'Timeout: A requisição demorou mais de 5 minutos para responder. Tente filtrar por um período menor.'
                        );
                    } else {
                        this._mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this._removerLoading();
                }
            },
        });
    });
</script>

<!-- JavaScript para controle das abas -->
<script>
    function openTab(event, tabName) {
        // Oculta todos os conteúdos das abas
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });

        // Remove a classe ativa de todas as abas
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.classList.remove('border-indigo-500', 'text-indigo-600');
            link.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700',
                'hover:border-gray-300');
        });

        // Exibe o conteúdo da aba clicada
        document.getElementById(tabName).style.display = 'block';

        // Adiciona a classe ativa à aba clicada
        event.currentTarget.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700',
            'hover:border-gray-300');
        event.currentTarget.classList.add('border-indigo-500', 'text-indigo-600');
    }

    // Ativa a aba "Cadastro Pré-O.S" ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        const defaultTab = document.querySelector('.tab-link'); // Seleciona a primeira aba
        const defaultTabContent = document.getElementById('cadastro'); // Seleciona o conteúdo da primeira aba

        if (defaultTab && defaultTabContent) {
            defaultTab.classList.add('border-indigo-500', 'text-indigo-600');
            defaultTabContent.style.display = 'block';
        }
    });


    function confirmaFinalizar(id) {
        if (confirm('Deseja Finalizar a Pré O.S?')) {
            finalizarPreOs(id);
        }
    }

    function finalizarPreOs(id) {
        fetch(`/admin/manutencaopreordemserviconova/finalizaros/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(' Pré O.S finalizada com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao Finalizar Pré O.S');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao Finalizar Pré O.S');
            });
    }
</script>

{{-- função para formatação do campo telefone motorista --}}
<script>
    document.getElementById('telefone_motorista').addEventListener('input', function(e) {
        var phone = e.target.value.replace(/\D/g, ''); // Remove caracteres não numéricos
        var formattedPhone;

        // Verifica se é celular (11 dígitos) ou fixo (10 dígitos)
        if (phone.length === 11) {
            formattedPhone = phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3'); // Celular
        } else if (phone.length === 10) {
            formattedPhone = phone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3'); // Fixo
        } else {
            formattedPhone = phone; // Mantém como está se não for válido
        }

        e.target.value = formattedPhone;
    });
</script>
