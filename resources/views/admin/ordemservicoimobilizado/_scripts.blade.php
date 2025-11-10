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
    const manutencaoImobilizadoItens = @json($manutencaoImobilizadoItens ?? []);

    const produtoImobilizadoDescricao = @json($produtoImobilizadoDescricao ?? []);

    const tipoDescricao = @json($tipoDescricao ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularImobilizadoTabela();
    });

    function popularImobilizadoTabela() {
        const tbody = document.getElementById('tabelaImobilizadoBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        manutencaoImobilizadoItens.forEach((item, index) => {

            const tr = document.createElement('tr');

            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${tipoDescricao[item.id_tipo_manutencao_imobilizado] ?? '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${item.id_produtos_imobilizados} - ${produtoImobilizadoDescricao[item.id_produtos_imobilizados] ?? '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <div class="cursor-pointer delete-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            tr.querySelector(".delete-produto").addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                removerImobilizado(index);
            });

            tbody.appendChild(tr);
        });

        atualizarCampoHidden();
    }

    function removerImobilizado(index) {
        manutencaoImobilizadoItens.splice(index, 1);
        popularImobilizadoTabela();
    }

    function atualizarCampoHidden() {
        document.getElementById('imobilizados_json').value = JSON.stringify(manutencaoImobilizadoItens);
    }

    function adicionarImobilizado() {
        const id_produtos_imobilizados = document.querySelector('[name="id_produtos_imobilizados"]').value;
        const id_tipo_manutencao_imobilizado = document.querySelector('[name="id_tipo_manutencao_imobilizado"]').value;

        if (!id_produtos_imobilizados) {
            alert('Preencha todos os campos para adicionar o histórico.');
            return;
        }

        const novoItem = {
            id_produtos_imobilizados: id_produtos_imobilizados,
            id_tipo_manutencao_imobilizado: id_tipo_manutencao_imobilizado,
            data_inclusao: new Date(),
        };

        manutencaoImobilizadoItens.push(novoItem);
        popularImobilizadoTabela();
    }
</script>

<script>
    // Coloque isso em um arquivo .js ou dentro de uma tag <script> no seu blade
    window.onFinalizar = function(id) {
        let formData = new FormData();

        formData.append('id', id);

        fetch(`{{ route('admin.ordemservicoimobilizado.finalizar') }}`, {
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
                'Ocorreu um erro ao solicitar a peças';

            if (typeof showNotification === 'function') {
                showNotification('Erro', message, 'error');
            } else {
                alert(message);
            }
        });
    };

    window.onSolicitar = function(id) {
        if (!confirm('Deseja solicitar as peças?')) {
            return;
        }

        let formData = new FormData();
        formData.append('id_filial', 1); // se precisar enviar algo além do id

        fetch(`{{ route('admin.ordemservicoimobilizado.solicitar', ['id' => '__id__']) }}`.replace('__id__', id), {
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
            alert(data.message);
        })
        .catch(error => {
            alert(error.message || 'Erro ao solicitar peça');
        });
    };




</script>

<script>
    // Função para verificar se pode voltar ao estoque (GET)
    window.onVoltarEstoque = function(id) {
        let formData = new FormData();

        formData.append('id', id);

        fetch(`{{ route('admin.ordemservicoimobilizado.voltar-estoque') }}`, {
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


</script>