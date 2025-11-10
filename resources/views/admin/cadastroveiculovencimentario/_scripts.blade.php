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
    let veiculoAtual = null;
    let crlvAtual = null;

    function carregarCrlv(idVeiculo) {
        fetch(`/admin/cadastroveiculovencimentario/crlv/${idVeiculo}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erro HTTP: " + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.error || !data.crlv || !Array.isArray(data.crlv) || data.crlv.length === 0) {
                    alert(data.message || "CRLV não encontrado para este veículo.");
                    return;
                }

                const veiculo = data.veiculo ?? {};
                const crlv = data.crlv[0]; // agora é seguro
                veiculoAtual = veiculo;
                crlvAtual = crlv;

                document.querySelector('.renavam-info').textContent = crlv.renavam || '';
                document.querySelector('.licenciamento-info').textContent = crlv.licenciamento || '';
                document.querySelector('.uf-info').textContent = crlv.uf || '';
                document.querySelector('.municipio-info').textContent = crlv.municipio || '';

                if (crlv.url) {
                    const iframe = document.getElementById('pdfFrame');
                    iframe.src = crlv.url;
                    iframe.style.display = 'block';
                    document.getElementById('pdfLoading').style.display = 'none';
                }

                showModal('crlvForm');
            })


            .catch(error => {
                console.error("Erro ao carregar CRLV:", error);
                alert("Erro ao carregar CRLV. Verifique se o veículo possui documento disponível.");
            });
    }



    function openCrlvPorRenavam(veiculo) {
        fetch(`/admin/cadastroveiculovencimentario/${veiculo.renavam}/crlv`)
            .then(response => response.json())
            .then(crlv => {
                openCrlvModal(veiculo, [crlv]); // transformando em array para reusar a função existente
            })
            .catch(error => {
                console.error('Erro ao buscar CRLV:', error);
                alert('Erro ao buscar CRLV do veículo.');
            });
    }


    function onImprimir(id) {

        console.log('id', id);

        let formData = new FormData();

        formData.append('id', id);

        fetch(`{{ route('admin.cadastroveiculovencimentario.imprimir') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw err;
                    });
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



    @if (session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}',
            '{{ session('notification')['type'] }}');
    @endif
</script>

<script>
    function abrirModalPdf(veiculo, crlv) {
        if (!crlv || !crlv.url) {
            alert('Documento CRLV não disponível.');
            return;
        }

        const pdfUrl = crlv.url;
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdfFrame');
        const loading = document.getElementById('pdfLoading');

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        loading.style.display = 'flex';
        iframe.style.display = 'none';

        iframe.src = pdfUrl;
    }


    function fecharModalPdf() {
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdfFrame');

        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Restaurar scroll

        // Limpar iframe após animação
        setTimeout(() => {
            iframe.src = '';
        }, 300);
    }

    function pdfCarregado() {
        const loading = document.getElementById('pdfLoading');
        const iframe = document.getElementById('pdfFrame');

        loading.style.display = 'none';
        iframe.style.display = 'block';
    }

    function imprimirPdf() {
        // Abrir janela de impressão otimizada
        const printWindow = window.open('', '_blank', 'width=900,height=600');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>CRLV Digital - Imprimir</title>
                <style>
                    body { margin: 0; padding: 0; }
                    iframe { width: 100%; height: 100vh; border: none; }
                    @media print {
                        body { margin: 0; }
                        iframe { height: 100vh; page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                <iframe src="${pdfUrl}" onload="setTimeout(() => window.print(), 1000)"></iframe>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    function baixarPdf() {
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'CRLV_Digital.pdf';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Fechar modal com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalPdf();
        }
    });

    // Fechar modal clicando no fundo
    document.getElementById('pdfModal').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalPdf();
        }
    });

    // Prevenir zoom com Ctrl+Scroll no modal
    document.getElementById('pdfModal').addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
            e.preventDefault();
        }
    });
</script>
