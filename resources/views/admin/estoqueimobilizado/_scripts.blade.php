<script>
    const relacaoImobilizados = @json($relacaoImobilizados->relacaoImobilizadosItens ?? []);
    const produtoDescricao = @json($produtoDescricao ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularHistoricoTabela();
    });

    function popularHistoricoTabela() {
        const tbody = document.getElementById('tabelaHistoricoBody');
        tbody.innerHTML = '';

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        relacaoImobilizados.forEach((item, index) => {
            const tr = document.createElement('tr');
            const storageUrlBase = "{{ asset('storage') }}";
            const descricao = produtoDescricao[item.id_produtos_imobilizados ?? 'Não informado'] ?? 'Não informado';
            const descricaoReduzida = descricao.substring(0, 20);

            tr.innerHTML = `
                <input type="hidden" name="id_relacao_imobilizados_itens"
                    value="${item.id_relacao_imobilizados_itens}">

                <td class="px-6 py-4 whitespace-nowrap cursor-pointer select-produto" data-id="${item.id_produtos}">
                    ${item.id_produtos} - ${produtoDescricao[item.id_produtos] ?? '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap cursor-pointer select-produto" data-id="${item.id_produtos}">
                    ${descricaoReduzida }
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <div class="cursor-pointer edit-produto " data-index="${index}" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor" fill="currentColor" class="size-4">
                                <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path
                                    d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z" />
                            </svg>
                        </div>

                        <div class="cursor-pointer print-termo" data-index="${index}" title="Imprimir Termo">
                            <a href="${storageUrlBase}/${item.caminho_imobilizado}"  target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor" fill="currentColor" class="size-4">
                                    <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path 
                                        d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                                </svg>                        
                            </a>
                        </div>
                        <div class="cursor-pointer upload data-index="${index}" title="Upload Termo" onclick="visualizarTermo(${ item.id_relacao_imobilizados_itens})">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor" fill="currentColor" class="size-4">
                                <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path
                                    d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 242.7-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7 288 32zM64 352c-35.3 0-64 28.7-64 64l0 32c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-32c0-35.3-28.7-64-64-64l-101.5 0-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352 64 352zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                            </svg>
                        </div>
                        <div class="cursor-pointer upload data-index="${index} " title="Estornar para O.S." onclick="onEstornarOs()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor" fill="currentColor" class="size-4">
                                <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                <path
                                    d="M448 96c0-35.3-28.7-64-64-64L64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-320zM320 256c0 6.7-2.8 13-7.7 17.6l-112 104c-7 6.5-17.2 8.2-25.9 4.4s-14.4-12.5-14.4-22l0-208c0-9.5 5.7-18.2 14.4-22s18.9-2.1 25.9 4.4l112 104c4.9 4.5 7.7 10.9 7.7 17.6z" />
                            </svg>
                        </div>

                    </div>

                </td>
            `;

            // Quando clicar no ID do produto, preenche o campo id_produtos
            tr.querySelector(".select-produto").addEventListener("click", (event) => {
                const idProduto = event.currentTarget.getAttribute("data-id");
                const descricao = produtoDescricao[idProduto] ?? 'Sem Descrição';

                document.querySelector('[name="id_produtos"]').value = `${idProduto} - ${descricao}`;
            });

            // Quando clicar no ícone de edição, preenche todos os campos
            tr.querySelector(".edit-produto").addEventListener("click", (event) => {
                event.stopPropagation();
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                preencherCamposEdicao(index);
            });

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideModal('upload-termo-responsabilidade');
                hideModal('vizualizar-requisicao-itens');
            }
        });

        visualizarTermo = function(id) {
            showModal('upload-termo-responsabilidade');
            
            // Aqui você pode carregar os serviços via AJAX se necessário
            // ou usar dados já disponíveis na página
            const requisicaoImobilizadosItens = @json($requisicaoImobilizadosItens ?? []);
            const requisicaoItensFiltrados = requisicaoImobilizadosItens.filter(requisicao => requisicao.value == id);
            
            preencherTabelaTermo(requisicaoItensFiltrados);
        };

        function preencherTabelaTermo(requisicaoItensFiltrados) {
            
            const item = requisicaoItensFiltrados[0];
            
            document.getElementById('relacao_imobilizados').value = item.label;
            document.getElementById('id_relacao_imobilizado_itens').value = item.value || '';
            document.getElementById('produtos_imobilizados').value = item.produtoImobilizado;
            document.getElementById('cod_patrimonio').value = item.patrimonio || '';
            document.getElementById('id_produtos').value = item.produto || '';
        }

    }

    function atualizarCampoHidden() {
        document.getElementById('historicos_json').value = JSON.stringify(relacaoImobilizados);
    }


    function preencherCamposEdicao(index) {
        const item = relacaoImobilizados[index];
        if (!item) return;

        // Preenche o formulário com os dados do item selecionado
        document.querySelector('[name="id_produtos"]').value = item.id_produtos;
        document.querySelector('[name="id_produtos_imobilizados"]').value = item.id_produtos_imobilizados|| '';
        document.querySelector('[name="id_pessoal"]').value = item.id_pessoal || '';
        document.querySelector('[name="id_lider"]').value = item.id_lider || '';
        document.querySelector('[name="id_departamento"]').value = item.id_departamento || '';
        document.querySelector('[name="id_veiculo"]').value = item.id_veiculo || '';
    }

    function getInputByName(name) {
        const input = document.querySelector(`[name="${name}"]`);
        return input ? input.value : null;
    }

    window.onSalvarProduto = function() {
        const id_relacao_imobilizados = getInputByName("id_relacao_imobilizados");
        const id_relacao_imobilizados_itens = getInputByName("id_relacao_imobilizados_itens");

        const id_produtos_imobilizados = getInputByName("id_produtos_imobilizados");
        if (id_produtos_imobilizados == null) {
            const id_produtos_imobilizados = '';
        }

        let id_reponsavel = getInputByName("id_reponsavel"); 
        if (id_reponsavel === null) {
            id_reponsavel = '';
        }

        let id_lider = getInputByName("id_lider");
        if (id_lider === null) {
            id_lider = '';
        }

        let id_departamento = getInputByName("id_departamento");
        if (id_departamento === null) {
            id_departamento = '';
        }

        let id_veiculo = getInputByName("id_veiculo"); 
        if (id_veiculo === null) {
            id_veiculo = '';
        }


        let formData = new FormData();

        formData.append('id_relacao_imobilizados', id_relacao_imobilizados);
        formData.append('id_relacao_imobilizados_itens', id_relacao_imobilizados_itens);
        formData.append('id_produtos_imobilizados', id_produtos_imobilizados);
        formData.append('id_reponsavel', id_reponsavel);
        formData.append('id_lider', id_lider);
        formData.append('id_departamento', id_departamento);
        formData.append('id_veiculo', id_veiculo);

        // Mostrar o conteúdo do FormData
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        fetch(`{{ route('admin.saidarelacaoimobilizado.salvar-produto') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (typeof showNotification === 'function' && data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );
            } else if (data.notification) {
                alert(data.notification.message);
            }

            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            }
        })
        .catch(error => {
            const message =
                error?.notification?.message ||
                error?.message ||
                'Ocorreu um erro ao aprovar a requisição';

            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };

    function salvarEdicao() {
        const id_relacao_imobilizados_itens = getInputByName("id_relacao_imobilizados_itens");
        const id_produtos = getInputByName("id_produtos");
        const id_produtos_imobilizados = getInputByName("id_produtos_imobilizados");
        const id_reponsavel = getInputByName("id_reponsavel"); // corrigido
        const id_lider = getInputByName("id_lider");
        const id_departamento = getInputByName("id_departamento");
        const id_veiculo = getInputByName("id_veiculo");

        if (!id_relacao_imobilizados_itens) {
            alert("Selecione um produto para editar.");
            return;
        }
        
        // Encontra o item na lista e atualiza
        const index = relacaoImobilizados.findIndex(item => item.id_relacao_imobilizados_itens == id_relacao_imobilizados_itens);
        if (index !== -1) {

            relacaoImobilizados[index] = {
                ...relacaoImobilizados[index], // Mantém os dados existentes
                id_produtos_imobilizados: id_produtos_imobilizados,
                id_reponsavel: id_reponsavel,
                id_lider: id_lider,
                id_departamento: id_departamento,
                id_veiculo: id_veiculo,
            };
            
            alert("Item atualizado com sucesso!");
            popularHistoricoTabela(); // Atualiza a tabela

        }

    }


    window.onSalvarTermo = function() {
        const relacaoImobilizados = document.querySelector('[name="relacao_imobilizados"]').value;
        const relacaoImobilizadosItens = document.querySelector('[name="id_relacao_imobilizado_itens"]').value;
        let input = document.getElementById('arquivo');
        let formData = new FormData();

        let arquivo = input.files[0];

        if (!arquivo) {
            alert('Por favor, selecione um arquivo.');
            return;
        }

        formData.append('relacaoImobilizados', relacaoImobilizados);
        formData.append('relacaoImobilizadosItens', relacaoImobilizadosItens);
        formData.append('arquivo', arquivo);

        // Mostrar o conteúdo do FormData
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        fetch(`{{ route('admin.saidarelacaoimobilizado.salvar-termo') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (typeof showNotification === 'function' && data.notification) {
                showNotification(data.notification.title, data.notification.message, data.notification.type);
            } else if (data.notification) {
                alert(data.notification.message);
            }

            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            }
        })
        .catch(error => {
            const message = error.notification?.message || 'Ocorreu um erro ao salvar o termo';
            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };


</script>

<script>
    // Coloque isso em um arquivo .js ou dentro de uma tag <script> no seu blade
        window.onFinalizar = function(id) {
        const produtosImobilizados = document.querySelector('[name="id_produtos_imobilizados"]').value;
        let formData = new FormData();

        formData.append('id', id);
        formData.append('produtosImobilizados', produtosImobilizados);

        // Mostrar o conteúdo do FormData
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        fetch(`{{ route('admin.saidarelacaoimobilizado.finalizar') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (typeof showNotification === 'function' && data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );
            } else if (data.notification) {
                alert(data.notification.message);
            }

            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            }
        })
        .catch(error => {
            const message =
                error?.notification?.message ||
                error?.message ||
                'Ocorreu um erro ao finalizar a requisição';

            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };

    window.onEstornarOs = function(id) {

        const resposta = confirm("Tem certeza que deseja estornar este item de volta para a O.S? Esta operação excluirá o item da Requisição.");
        if (!resposta) {
            return;
        }

        // Obter os valores dos campos
        const idRelacaoImobilizados = document.querySelector('[name="id_relacao_imobilizados"]').value;
        const idRelacaoImobilizadosItens = document.querySelector('[name="id_relacao_imobilizados_itens"]').value;

        let formData = new FormData();

        formData.append('idRelacaoImobilizados', idRelacaoImobilizados);
        formData.append('idRelacaoImobilizadosItens', idRelacaoImobilizadosItens);

        // Mostrar o conteúdo do FormData
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        fetch(`{{ route('admin.saidarelacaoimobilizado.estornar-os') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (typeof showNotification === 'function' && data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );
            } else if (data.notification) {
                alert(data.notification.message);
            }

            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            }
        })
        .catch(error => {
            const message =
                error?.notification?.message ||
                error?.message ||
                'Ocorreu um erro ao estornar O.S. a requisição';

            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };
</script>

<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        
        // Inicializar manipulação da tabela
        initTableLoading();
    });
    
    // ======================================================
    // GESTÃO DE LOADING DA TABELA DE RESULTADOS
    // ======================================================
    function initTableLoading() {
        const loadingElement = document.getElementById('table-loading');
        const resultsElement = document.getElementById('results-table');
        
        if (loadingElement && resultsElement) {
            
            // Esconder o loading e mostrar os resultados após carregamento da página
            setTimeout(function() {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
            }, 300);
            
            // Lidar com eventos HTMX
            document.body.addEventListener('htmx:beforeRequest', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {
                    loadingElement.style.display = 'flex';
                    resultsElement.classList.add('opacity-0');
                }
            });
            
            document.body.addEventListener('htmx:afterSwap', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {

                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }
            });
            
            // Backup em caso de falha no HTMX
            document.body.addEventListener('htmx:responseError', function(event) {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
            });
        } else {
            console.log('Elementos de loading/resultado não encontrados');
        }
    }
    
</script>

<script>
    window.visualizarItens = function(id) {

        showModal('vizualizar-requisicao-itens');
            
            // Aqui você pode carregar os serviços via AJAX se necessário
            // ou usar dados já disponíveis na página
            const requisicaoImobilizadosItens = @json($requisicaoImobilizadosItens ?? []);
            const requisicaoItensFiltrados = requisicaoImobilizadosItens.filter(requisicao => requisicao.value == id);
            preencherTabelaServicos(requisicaoItensFiltrados);
    };
    
    function preencherTabelaServicos(requisicaoItensFiltrados) {
        const tabelaBody = document.getElementById('tabelaBody');
        if (!tabelaBody) return;
        
        // Limpa a tabela
        tabelaBody.innerHTML = '';
        
        if (requisicaoItensFiltrados.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="10" class="py-3 px-6 text-center text-gray-500">Nenhum serviço encontrado</td>`;
            tabelaBody.appendChild(tr);
            return;
        }
        
        // Popula a tabela com os produtos imobilizado
        requisicaoItensFiltrados.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'bg-white border-b hover:bg-gray-50';
            
            // Formata a data
            const dataInclusao = new Date(item.data_inclusao);
            const dataFormatada = dataInclusao.toLocaleDateString('pt-BR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            tr.innerHTML = `
            <td class="py-3 px-6">${item.label || 'N/A'}</td>
            <td class="py-3 px-6">${item.value || 'N/A'}</td>
            <td class="py-3 px-6">${item.produto|| 'N/A'}</td>
            <td class="py-3 px-6">${dataFormatada}</td>
            `;
            
            tabelaBody.appendChild(tr);
        });
    }
</script>