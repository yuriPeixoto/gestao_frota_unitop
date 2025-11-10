<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('utils', {
            loading: false,
            
            // Função auxiliar para capturar dados do formulário
            _getFormData() {
                return {
                    id_filial: document.querySelector('[name="id_filial"]')?.value || '',
                    id_bomba: document.querySelector('[name="id_bomba"]')?.value || '',
                    id_tipo_combustivel: document.querySelector('[name="id_tipo_combustivel"]')?.value || '',
                    data_inclusao: document.querySelector('[name="data_inclusao"]')?.value || '',
                    data_final_abastecimento: document.querySelector('[name="data_final_abastecimento"]')?.value || '',
                };
            },

            // Função auxiliar para validar datas
            _validateDates(data) {
                if (!data.data_inclusao || !data.data_final_abastecimento) {
                    alert('Por favor, informe a data inicial e final para emissão do relatório.');
                    return false;
                }
                return true;
            },

            // Função auxiliar para criar mensagem de carregamento
            _createLoadingMessage() {
                // Remove mensagem anterior se existir
                const existingMessage = document.getElementById('loading-message');
                if (existingMessage) {
                    existingMessage.remove();
                }

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
                
                // Adicionar animação de carregamento (apenas uma vez)
                if (!document.getElementById('loading-spinner-style')) {
                    const style = document.createElement('style');
                    style.id = 'loading-spinner-style';
                    style.textContent = `
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                document.body.appendChild(loadingMessage);
                return loadingMessage;
            },

            // Função auxiliar para remover mensagem de carregamento
            _removeLoadingMessage() {
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
            },

            // Função auxiliar para fazer download de arquivo
            _downloadFile(blob, filename) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = filename;
                
                // Adicionar ao DOM temporariamente para garantir que funcione em todos os browsers
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                // Limpar o URL do blob
                window.URL.revokeObjectURL(url);
            },

            // Função auxiliar para tratar erros
            _handleError(error, timeoutId = null) {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                
                this._removeLoadingMessage();
                
                console.error('Erro detalhado:', error);
                
                if (error.name === 'AbortError') {
                    alert('Timeout: A requisição demorou mais de 5 minutos para responder. O relatório pode ter muitos dados. Tente filtrar por um período menor ou entre em contato com o suporte.');
                } else if (typeof error.message === 'string') {
                    alert('Erro: ' + error.message);
                } else {
                    alert('Erro ao gerar relatório. Verifique o console para mais detalhes.');
                }
            },

            // Função auxiliar para processar resposta
            _processResponse(response) {
                console.log('Status da resposta:', response.status);
                console.log('Headers da resposta:', response.headers);
                
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                    }).catch(() => {
                        throw new Error(`Erro ${response.status}: ${response.statusText}`);
                    });
                }
                
                const contentType = response.headers.get('content-type');
                console.log('Tipo de conteúdo:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(jsonData => {
                        if (jsonData.success === false) {
                            throw new Error(jsonData.message || 'Erro desconhecido');
                        }
                        throw new Error('Resposta inesperada do servidor');
                    });
                }
                
                return response.blob();
            },

            listagemencerrantes() {
                // Inicia o loading
                this.loading = true;
                
                // Capturar os dados do formulário
                const data = this._getFormData();
                console.log('Dados sendo enviados:', data);

                // Validar se as datas foram preenchidas
                if (!this._validateDates(data)) {
                    this.loading = false;
                    return;
                }
                
                // Mostrar mensagem de carregamento
                this._createLoadingMessage();

                fetch(`/admin/listagemencerrantes/imprimir`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/pdf, application/json'
                    },
                    body: JSON.stringify(data),
                })
                .then(response => this._processResponse(response))
                .then(blob => {
                    this._removeLoadingMessage();
                    
                    // Verificar se é realmente um PDF
                    if (blob.type && !blob.type.includes('application/pdf')) {
                        throw new Error('Arquivo recebido não é um PDF válido');
                    }
                    
                    this._downloadFile(blob, `listagemencerrantes_${new Date().toISOString().split('T')[0]}.pdf`);
                    console.log('Download iniciado com sucesso');
                })
                .catch(error => this._handleError(error))
                .finally(() => {
                    this.loading = false;
                    console.log('Processo finalizado');
                });
            },

            listagemencerrantesExcel() {
                // Inicia o loading
                this.loading = true;
                
                // Capturar os dados do formulário
                const data = this._getFormData();
                console.log('Dados sendo enviados:', data);

                // Validar se as datas foram preenchidas
                if (!this._validateDates(data)) {
                    this.loading = false;
                    return;
                }

                // Mostrar mensagem de carregamento
                this._createLoadingMessage();

                // Criar AbortController para o timeout (5 minutos)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => {
                    controller.abort();
                }, 300000); // 5 minutos

                fetch(`/admin/listagemencerrantes/imprimirexcel`, {
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
                    clearTimeout(timeoutId);
                    
                    console.log('Status da resposta:', response.status);
                    console.log('Headers da resposta:', response.headers);
                    
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                        }).catch(() => {
                            throw new Error(`Erro ${response.status}: ${response.statusText}`);
                        });
                    }
                    
                    const contentType = response.headers.get('content-type');
                    console.log('Tipo de conteúdo:', contentType);
                    
                    if (contentType && contentType.includes('application/json')) {
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
                        return response.blob();
                    } else {
                        throw new Error('Tipo de resposta não esperado: ' + contentType);
                    }
                })
                .then(blob => {
                    this._removeLoadingMessage();
                    this._downloadFile(blob, `listagemencerrantes_${new Date().toISOString().split('T')[0]}.xls`);
                    console.log('Download iniciado com sucesso');
                })
                .catch(error => this._handleError(error, timeoutId))
                .finally(() => {
                    this.loading = false;
                    this._removeLoadingMessage();
                    console.log('Processo finalizado');
                });
            }
        });
    });
</script>