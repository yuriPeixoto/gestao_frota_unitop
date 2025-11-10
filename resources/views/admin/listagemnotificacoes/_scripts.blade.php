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
    function pdfCarregado() {
        const loading = document.getElementById('pdfLoading');
        const iframe = document.getElementById('pdfFrame');
        
        loading.style.display = 'none';
        iframe.style.display = 'block';
    };

    function abrirModalPdf(url, tag){

        if(tag == 'notificacao'){
            pdfUrl = url.notificacao;
        }

        if(tag == 'penalidade'){
            pdfUrl = url.penalidade;
        }

        if(tag == 'comprovante'){
            pdfUrl = url.comprovante_pagamento;
        }
       
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

<script>
    function baixarBoletosLote() {
        // Remove a verificação de placas selecionadas
        const url = `{{ route('admin.listagemnotificacoes.baixarlote') }}`;
        
        // Adiciona um loading para melhor UX
        showNotification('Aguarde', 'Preparando download dos boletos...', 'info');
        
        window.location.href = url;
    }
</script>

<script>
    const dropdownButtons = document.querySelectorAll('.dropdown-button');

    dropdownButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation(); // Impede que o clique se propague

            // Fecha todos os outros dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.add('hidden');
                }
            });

            // Alterna a visibilidade do dropdown atual
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });

    // Fecha os dropdowns ao clicar fora deles
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });
</script>

<script>
    let multaAtual = null; // Variável global para armazenar os dados da multa atual

    function abrirCondutorForm(listagemNotificacoes) {
        multaAtual = listagemNotificacoes;

        // Popula as informações do veículo
        document.querySelector('.placa-info').textContent = listagemNotificacoes.placa || '';
        document.querySelector('.renavam-info').textContent = listagemNotificacoes.renavam || '';
        document.querySelector('.ait-info').textContent = listagemNotificacoes.ait || '';

        showModal('condutorForm');

        async function indicarMotorista(data) {
            if (!data.condutor || !data.ait) {
                alert('Preencha todos os campos obrigatórios.');
                return;
            }

            try {
                const response = await fetch('{{ route("admin.listagemnotificacoes.indicar-motorista") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    // Fecha o modal
                    window.bladewind.modal.hide('condutorForm');

                    // Opcional: atualiza a lista ou a página
                    location.reload();
                } else {
                    alert(result.message || 'Erro desconhecido');
                }
            } catch (error) {
                console.error('Erro ao indicar motorista:', error);
                alert('Erro ao comunicar com o servidor.');
            }
        }

    }
    
</script>

<script>
    function getCondutorData() {
    const selectCondutor = document.getElementById('condutor');
    const selectDesconto = document.getElementById('desconto');

    const condutorId = selectCondutor.value;
    const descontoId = selectDesconto.value;
    const aitId = multaAtual?.ait;
    
    return {
        condutor: condutorId,
        desconto: descontoId,
        ait: aitId
    };
}

function indicarMotorista(data) {
    if (!data.condutor) {
        showNotification('Erro', 'Selecione um condutor primeiro!', 'error');
        return;
    }
    
    console.log('Condutor:', data.condutor);
    console.log('Desconto:', data.desconto);
    console.log('ait:', data.ait);
    
    // Fazer a requisição
    fetch('{{ route("admin.listagemnotificacoes.indicar-motorista") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.notification.title, 
                data.notification.message, 
                data.notification.type
            );
            
            // Fechar modal
            hideModal('condutorForm');
            
            // Limpar formulário
            document.getElementById('condutor').value = '';
            document.getElementById('desconto').value = '';
            
            // Opcional: Recarregar a tabela se necessário
            window.location.reload();
        } else {
            showNotification('Erro', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showNotification('Erro', 'Ocorreu um erro ao processar a solicitação.', 'error');
    })
    .finally(() => {
        // Restaurar botão
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}
</script>

<script>
    function removerMotorista(ait) {
        let data = { ait: ait };

        // Fazer a requisição
        fetch('{{ route("admin.listagemnotificacoes.remover-motorista") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(
                    data.notification.title, 
                    data.notification.message, 
                    data.notification.type
                );
                               
                // Opcional: Recarregar a tabela se necessário
                window.location.reload();
            } else {
                showNotification('Erro', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro', 'Ocorreu um erro ao processar a solicitação.', 'error');
        })
        .finally(() => {
            // Restaurar botão
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    }
</script>



<script>
    let currentMultaId = null;
    
    function openDescontoModal(multaId) {
        currentMultaId = multaId;
        document.getElementById('multaId').value = multaId;
        showModal('solicitarDesconto');
    }
    
    function submitDescontoForm() {
        const form = document.getElementById('descontoForm');
        const formData = new FormData(form);
        
        fetch('/admin/listagemnotificacoes/solicitar-desconto-40', {// Substitua pela rota correta
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideModal('solicitarDesconto');
            
            if(data.success) {
                // Mostrar notificação de sucesso
                showNotification('Sucesso!', data.message, 'success');
                // Recarregar a página ou atualizar os dados
                window.location.reload();
            } else {
                // Mostrar notificação de erro
                showNotification('Erro!', data.message, 'error');
            }
        })
        .catch(error => {
            hideModal('solicitarDesconto');
            showNotification('Erro!', 'Ocorreu um erro ao processar a solicitação.', 'error');
            console.error('Error:', error);
        });
    }
    
    function showNotification(title, message, type) {
        // Implemente sua função de notificação ou use a do BladeWind se disponível
        // Exemplo simples:
        alert(`${title}: ${message}`);
    }
</script>

<script>
    function gerarFici(event, id_smartec_notificacoes_sne_detran) {
    event.preventDefault();
    
    // Mostra loading
    Swal.fire({
        title: 'Gerando FICI...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`{{ route('admin.listagemnotificacoes.gerarFici') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id_smartec_notificacoes_sne_detran: id_smartec_notificacoes_sne_detran // Corrigido aqui
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            if (data.caminho_pdf) {
                window.open(data.caminho_pdf, '_blank');
            }
            Swal.fire('Sucesso!', 'FICI gerado com sucesso', 'success');
        } else {
            Swal.fire('Erro!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Erro!', 'Falha na comunicação com o servidor', 'error');
    });
}
</script>