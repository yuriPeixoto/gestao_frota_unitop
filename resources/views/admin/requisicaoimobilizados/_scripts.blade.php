<script>
    const relacaoImobilizados = @json($relacaoImobilizados->relacaoImobilizadosItens ?? []);

    const produtoDescricao = @json($produtoDescricao ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularHistoricoTabela();
    });

    function popularHistoricoTabela() {
        const tbody = document.getElementById('tabelaHistoricoBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        relacaoImobilizados.forEach((item, index) => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${item.id_produtos} - ${produtoDescricao[item.id_produtos] ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <div class="cursor-pointer delete-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            tr.querySelector(".delete-produto").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                removerHistorico(index);
            });

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerHistorico(index) {
        relacaoImobilizados.splice(index, 1);
        popularHistoricoTabela();
    }

    function atualizarCampoHidden() {
        document.getElementById('historicos_json').value = JSON.stringify(relacaoImobilizados);
    }

    function adicionarHistorico() {
        const id_produtos = document.querySelector('[name="id_produtos"]').value;
        // const data_inclusao = document.querySelector('[name="data_inclusao"]' ?? '').value;

        if (!id_produtos) {
            alert('Preencha todos os campos para adicionar o histórico.');
            return;
        }

        const novoItem = {
            id_produtos: id_produtos,
            data_inclusao: new Date(),
        };

        relacaoImobilizados.push(novoItem);
        popularHistoricoTabela();
    }
</script>

<script>
    // exclusao da Manutenção
    function destroyOrdemServico(id) {
        showModal('delete-autorizacao');
        autorizacaooId = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function confirmarExclusao(id) {
        excluirOrdemServico(id);
    }

    function excluirOrdemServico(id) {
        fetch(`{{ route('admin.requisicaoimobilizados.destroy', ':id') }}`.replace(':id', autorizacaooId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                });
            }
            return response.json();
        }).then(data => {
            if (data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }).catch(error => {
            console.error('Full error:', error);

            showNotification(
                'Erro',
                error.message,
                'error'
            );
        });
    }

    @if(session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
    @endif
</script>

<script>
    let idSelecionado = null;


            function editPneu(id) {
                window.location.href = `{{ route('admin.requisicaoimobilizados.edit', ':id') }}`.replace(':id', id)
            }

            function executeSearch() {
            const searchTerm = document.getElementById('search-input').value;
            const currentUrl = new URL(window.location.href);

            if (searchTerm) {
                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }

            window.location.href = currentUrl.toString();
        }

        function clearSearch() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            window.location.href = currentUrl.toString();
        }

        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                executeSearch();
            }
        });
</script>

<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado - Inicializando scripts');
        
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
            console.log('Elementos de loading/resultado encontrados');
            
            // Esconder o loading e mostrar os resultados após carregamento da página
            setTimeout(function() {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
                console.log('Tabela de resultados exibida');
            }, 300);
            
            // Lidar com eventos HTMX
            document.body.addEventListener('htmx:beforeRequest', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {
                    console.log('HTMX request iniciada - mostrando loading');
                    loadingElement.style.display = 'flex';
                    resultsElement.classList.add('opacity-0');
                }
            });
            
            document.body.addEventListener('htmx:afterSwap', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {
                    console.log('HTMX swap concluído - escondendo loading');
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }
            });
            
            // Backup em caso de falha no HTMX
            document.body.addEventListener('htmx:responseError', function(event) {
                console.log('HTMX erro - escondendo loading');
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
        console.log('Visualizar itens:', id);
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

<script>
    // Coloque isso em um arquivo .js ou dentro de uma tag <script> no seu blade
    window.onEnviaraprovacao = function(id) {

        fetch(`{{ route('admin.requisicaoimobilizados.enviarAprovacao') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
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
            const message = error.notification.message || 'Ocorreu um erro ao aprovar a requisição';
            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };

</script>