<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('utils', {
            loading: false,

            // Função utilitária para mostrar loading
            showLoading() {
                // Verificar se já existe uma mensagem de loading
                if (document.getElementById('loading-message')) {
                    return;
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

                // Adicionar animação de carregamento apenas se não existir
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

            // Função utilitária para remover loading
            hideLoading() {
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
            },

            // Função utilitária para validar dados
            validateData(data) {
                if (!data.data_inclusao || !data.data_final_abastecimento) {
                    alert('Por favor, informe a data inicial e final para emissão do relatório.');
                    return false;
                }
                return true;
            },

            // Função utilitária para capturar dados do formulário (PDF)
            getFormDataPDF() {
                return {
                    id_filial: document.querySelector('[name="id_filial"]')?.value || '',
                    id_departamento: document.querySelector('[name="id_departamento"]')?.value || '',
                    id_tipo_equipamento: document.querySelector('[name="id_tipo_equipamento"]')
                        ?.value || '',
                    data_inclusao: document.querySelector('[name="data_inclusao"]')?.value || '',
                    data_final_abastecimento: document.querySelector(
                        '[name="data_final_abastecimento"]')?.value || '',
                };
            },

            // Função utilitária para capturar dados do formulário (Excel)
            getFormDataExcel() {
                return {
                    id_filial: document.querySelector('[name="id_filial"]')?.value || '',
                    id_departamento: document.querySelector('[name="id_departamento"]')?.value || '',
                    id_tipo_equipamento: document.querySelector('[name="id_tipo_equipamento"]')
                        ?.value || '',
                    data_inclusao: document.querySelector('[name="data_inclusao"]')?.value || '',
                    data_final_abastecimento: document.querySelector(
                        '[name="data_final_abastecimento"]')?.value || '',
                };
            },

            // Função utilitária para fazer download
            downloadFile(blob, filename) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = filename;

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                window.URL.revokeObjectURL(url);
            },

            // Função utilitária para processar resposta
            processResponse(response) {
                console.log('Status da resposta:', response.status);
                console.log('Headers da resposta:', response.headers);

                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message ||
                            `Erro ${response.status}: ${response.statusText}`);
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
                        contentType.includes('application/pdf') ||
                        contentType.includes('application/vnd.ms-excel') ||
                        contentType.includes(
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') ||
                        contentType.includes('application/octet-stream')
                    )) {
                    return response.blob();
                } else {
                    throw new Error('Tipo de resposta não esperado: ' + contentType);
                }
            },

            imprimirAbastecimentoEquipamento() {
                this.loading = true;

                const data = this.getFormDataPDF();
                console.log('Dados sendo enviados:', data);

                if (!this.validateData(data)) {
                    this.loading = false;
                    return;
                }

                this.showLoading();

                fetch(`/admin/abastecimentoequipamento/imprimir`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/pdf, application/json'
                        },
                        body: JSON.stringify(data),
                    })
                    .then(response => this.processResponse(response))
                    .then(blob => {
                        this.hideLoading();

                        const filename =
                            `abastecimento_equipamento_${new Date().toISOString().split('T')[0]}.pdf`;
                        this.downloadFile(blob, filename);

                        console.log('Download iniciado com sucesso');
                    })
                    .catch(error => {
                        console.error('Erro detalhado:', error);

                        if (typeof error.message === 'string') {
                            alert('Erro: ' + error.message);
                        } else {
                            alert(
                                'Erro ao gerar relatório. Verifique o console para mais detalhes.');
                        }
                    })
                    .finally(() => {
                        this.loading = false;
                        this.hideLoading();
                        console.log('Processo finalizado');
                    });
            },

            imprimirAbastecimentoEquipamentoExcel() {
                this.loading = true;

                const data = this.getFormDataExcel();
                console.log('Dados sendo enviados:', data);

                if (!this.validateData(data)) {
                    this.loading = false;
                    return;
                }

                this.showLoading();

                // Criar AbortController para o timeout (5 minutos)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => {
                    controller.abort();
                }, 300000); // 5 minutos

                fetch(`/admin/abastecimentomanualrelatorio/imprimirexcel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json'
                        },
                        body: JSON.stringify(data),
                        signal: controller.signal
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        return this.processResponse(response);
                    })
                    .then(blob => {
                        this.hideLoading();

                        const filename =
                            `relatorio_abastecimento_${new Date().toISOString().split('T')[0]}.xls`;
                        this.downloadFile(blob, filename);

                        console.log('Download iniciado com sucesso');
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);

                        console.error('Erro detalhado:', error);

                        if (error.name === 'AbortError') {
                            alert(
                                'Timeout: A requisição demorou mais de 5 minutos para responder. O relatório pode ter muitos dados. Tente filtrar por um período menor ou entre em contato com o suporte.');
                        } else {
                            if (typeof error.message === 'string') {
                                alert('Erro: ' + error.message);
                            } else {
                                alert(
                                    'Erro ao gerar relatório. Verifique o console para mais detalhes.');
                            }
                        }
                    })
                    .finally(() => {
                        this.loading = false;
                        this.hideLoading();
                        console.log('Processo finalizado');
                    });
            }
        });
    });
</script>
