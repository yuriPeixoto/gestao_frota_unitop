<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('utils', {
        loading: false,
        
        imprimirAbastecimentoManual() {
            // Inicia o loading
            this.loading = true;
            
            // Capturar os dados do formulário
            const data = {
                id_veiculo: document.querySelector('[name="id_veiculo"]')?.value || '',
                tipo: document.querySelector('[name="tipo"]')?.value || '',
                data_inclusao: document.querySelector('[name="data_inclusao"]')?.value || '',
                data_final_abastecimento: document.querySelector('[name="data_final_abastecimento"]')?.value || '',
            };

            console.log('Dados sendo enviados:', data);

            // Validar se as datas foram preenchidas
            if (!data.data_inclusao || !data.data_final_abastecimento) {
                alert('Por favor, informe a data inicial e final para emissão do relatório.');
                this.loading = false;
                return;
            }

            // Mostrar mensagem de carregamento para o usuário
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
            
            // Adicionar animação de carregamento
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            document.body.appendChild(loadingMessage);

            // Criar AbortController para o timeout (aumentado para 5 minutos)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                controller.abort();
            }, 300000); // 5 minutos (300 segundos)

            fetch(`/admin/listagemkmhistorico/imprimir`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json'
                },
                body: JSON.stringify(data),
                signal: controller.signal
            })
            .then(response => {
                // Limpar o timeout se a resposta chegou
                clearTimeout(timeoutId);
                
                console.log('Status da resposta:', response.status);
                console.log('Headers da resposta:', response.headers);
                
                if (!response.ok) {
                    // Se a resposta não for ok, tentar ler como JSON para pegar a mensagem de erro
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                    }).catch(() => {
                        // Se não conseguir ler como JSON, usar mensagem padrão
                        throw new Error(`Erro ${response.status}: ${response.statusText}`);
                    });
                }
                
                // Verificar o tipo de conteúdo da resposta
                const contentType = response.headers.get('content-type');
                console.log('Tipo de conteúdo:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    // Se for JSON, provavelmente é uma mensagem de erro
                    return response.json().then(jsonData => {
                        if (jsonData.success === false) {
                            throw new Error(jsonData.message || 'Erro desconhecido');
                        }
                        throw new Error('Resposta inesperada do servidor');
                    });
                } else if (contentType && (
                    contentType.includes('application/vnd.ms-excel') || 
                    contentType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') ||
                    contentType.includes('application/octet-stream')
                )) {
                    // Se for XLS/XLSX, processar como blob
                    return response.blob();
                } else {
                    throw new Error('Tipo de resposta não esperado: ' + contentType);
                }
            })
            .then(blob => {
                // Remover mensagem de carregamento
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
                
                // Criar URL do blob e fazer download
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = `listagem_km_historico_${new Date().toISOString().split('T')[0]}.xls`;
                
                // Adicionar ao DOM temporariamente para garantir que funcione em todos os browsers
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                // Limpar o URL do blob
                window.URL.revokeObjectURL(url);
                
                console.log('Download iniciado com sucesso');
                
                // Mostrar mensagem de sucesso
                alert('Relatório gerado com sucesso!');
            })
            .catch(error => {
                // Limpar o timeout em caso de erro
                clearTimeout(timeoutId);
                
                // Remover mensagem de carregamento
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
                
                console.error('Erro detalhado:', error);
                
                // Verificar se foi erro de timeout
                if (error.name === 'AbortError') {
                    alert('Timeout: A requisição demorou mais de 5 minutos para responder. O relatório pode ter muitos dados. Tente filtrar por um período menor ou entre em contato com o suporte.');
                } else {
                    // Mostrar erro para o usuário
                    if (typeof error.message === 'string') {
                        alert('Erro: ' + error.message);
                    } else {
                        alert('Erro ao gerar relatório. Verifique o console para mais detalhes.');
                    }
                }
            })
            .finally(() => {
                // Para o loading independente de sucesso ou erro
                this.loading = false;
                
                // Garantir que a mensagem de carregamento seja removida
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
                
                console.log('Processo finalizado');
            });
        }
    });
});
</script>