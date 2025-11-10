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

    @if(session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
    @endif
</script>

<script>
    
    function abrirModalPdf(url) {      
        const pdfUrl = url.boleto;
       
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdfFrame');
        const loading = document.getElementById('pdfLoading');
        
        // Mostrar modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevenir scroll da página
        
        // Mostrar loading
        loading.style.display = 'flex';
        iframe.style.display = 'none';
        
        // Carregar PDF
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
                <title>Licenciamento Digital - Imprimir</title>
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
