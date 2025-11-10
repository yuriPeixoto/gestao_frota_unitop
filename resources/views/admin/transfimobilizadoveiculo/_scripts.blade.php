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
    document.addEventListener('DOMContentLoaded', () => {
        const currencyFirstLoad = document.querySelectorAll('.monetario');
        currencyFirstLoad.forEach(input => {
            if (input.value.trim() !== '') {
                let valor = parseFloat(input.value); // Converte o valor diretamente para número

                // Formata o valor para BRL
                input.value = new Intl.NumberFormat('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(valor);
            }
        });
    });
</script>

<script>
    @if(session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
    @endif
</script>

<script>

    function abrirModal(id, placa) {
        showModal(`mudar-status-${id}`);

        domEl(`.bw-mudar-status-${id} .title`).innerText = placa;

    }

    function onVerificarSituacao(id, status, placa, filialDestino) {

        console.log('onVerificarSituacao', 'id', id, 'status', status, 'placa', placa, 'filialDestino', filialDestino);

        if (!confirm(`Atenção: essa ação transferirá o veículo ${placa} para a Filial ${filialDestino}. Deseja continuar? `)) {
            return;
        }

        let formData = new FormData();

        formData.append('id', id);

        fetch(`{{ route('admin.transfimobilizadoveiculo.verificarSituacao') }}`, {
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

            setTimeout(() => {
                window.location.reload();
            }, 500);
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
    }


</script>

