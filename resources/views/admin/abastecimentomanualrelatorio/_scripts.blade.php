<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('utils', {
            loading: false,
            
            // Método para coletar dados do formulário
            _coletarDadosFormulario() {
                return {
                    id_filial: document.querySelector('[name="id_filial"]')?.value || '',
                    id_categoria: document.querySelector('[name="id_categoria"]')?.value || '',
                    id_veiculo: document.querySelector('[name="id_veiculo"]')?.value || '',
                    id_tipo_equipamento: document.querySelector('[name="id_tipo_equipamento"]')?.value || '',
                    data_inclusao: document.querySelector('[name="data_inclusao"]')?.value || '',
                    data_final_abastecimento: document.querySelector('[name="data_final_abastecimento"]')?.value || '',
                    id_combustivel: document.querySelector('[name="id_combustivel"]')?.value || '',
                    id_departamento: document.querySelector('[name="id_departamento"]')?.value || '',
                    tipo_abastecimento: document.querySelector('[name="tipo_abastecimento"]')?.value || ''
                };
            },

            // Método para validar datas
            _validarDatas(data) {
                if (!data.data_inclusao || !data.data_final_abastecimento) {
                    this._mostrarAlerta('Por favor, informe a data inicial e final para emissão do relatório.');
                    return false;
                }
                return true;
            },

            // Método para mostrar alertas
            _mostrarAlerta(mensagem) {
                alert(mensagem);
            },

            // Método para criar e mostrar loading
            _mostrarLoading() {
                // Remove loading anterior se existir
                this._removerLoading();
                
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
                
                // Adicionar animação de carregamento se não existir
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

            // Método para remover loading
            _removerLoading() {
                const loadingMsg = document.getElementById('loading-message');
                if (loadingMsg) {
                    loadingMsg.remove();
                }
            },

            // Método para fazer download do arquivo
            _fazerDownload(blob, nomeArquivo) {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = nomeArquivo;
                
                // Adicionar ao DOM temporariamente para garantir compatibilidade
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                // Limpar o URL do blob
                window.URL.revokeObjectURL(url);
            },

            // Método para obter token CSRF
            _obterCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) {
                    throw new Error('Token CSRF não encontrado');
                }
                return token;
            },

            // Método genérico para fazer requisições
            async _fazerRequisicao(url, data, timeout = 300000) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this._obterCSRFToken(),
                            'Accept': 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json'
                        },
                        body: JSON.stringify(data),
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        // Tentar ler como JSON para pegar mensagem de erro
                        try {
                            const errorData = await response.json();
                            throw new Error(errorData.message || `Erro ${response.status}: ${response.statusText}`);
                        } catch {
                            throw new Error(`Erro ${response.status}: ${response.statusText}`);
                        }
                    }

                    return response;
                } catch (error) {
                    clearTimeout(timeoutId);
                    throw error;
                }
            },

            // Método para imprimir PDF
            async imprimirAbastecimentoManual() {
                this.loading = true;
                
                try {
                    const data = this._coletarDadosFormulario();
                    
                    this._mostrarLoading();
                    
                    const response = await this._fazerRequisicao('/admin/abastecimentomanualrelatorio/imprimir', data);
                    const blob = await response.blob();
                    
                    this._fazerDownload(blob, `relatorio_abastecimento_${new Date().toISOString().split('T')[0]}.pdf`);
                    
                } catch (error) {
                    console.error('Erro ao gerar relatório PDF:', error);
                    
                    if (error.name === 'AbortError') {
                        this._mostrarAlerta('Timeout: A requisição demorou mais de 5 minutos para responder. Tente filtrar por um período menor.');
                    } else {
                        this._mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this._removerLoading();
                }
            },

            // Método para imprimir Excel
            async imprimirAbastecimentoManualExcel() {
                this.loading = true;
                
                try {
                    const data = this._coletarDadosFormulario();
                    
                    // Validar datas
                    if (!this._validarDatas(data)) {
                        return;
                    }
                    
                    console.log('Dados sendo enviados:', data);
                    
                    this._mostrarLoading();
                    
                    const response = await this._fazerRequisicao('/admin/abastecimentomanualrelatorio/imprimirexcel', data);
                    
                    // Verificar tipo de conteúdo
                    const contentType = response.headers.get('content-type');
                    console.log('Tipo de conteúdo:', contentType);
                    
                    if (contentType && contentType.includes('application/json')) {
                        const jsonData = await response.json();
                        if (jsonData.success === false) {
                            throw new Error(jsonData.message || 'Erro desconhecido');
                        }
                        throw new Error('Resposta inesperada do servidor');
                    }
                    
                    // Processar como blob para download
                    const blob = await response.blob();
                    this._fazerDownload(blob, `relatorio_abastecimento_${new Date().toISOString().split('T')[0]}.xls`);
                    
                    console.log('Download iniciado com sucesso');
                    
                } catch (error) {
                    console.error('Erro ao gerar relatório Excel:', error);
                    
                    if (error.name === 'AbortError') {
                        this._mostrarAlerta('Timeout: A requisição demorou mais de 5 minutos para responder. O relatório pode ter muitos dados. Tente filtrar por um período menor ou entre em contato com o suporte.');
                    } else {
                        this._mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this._removerLoading();
                    console.log('Processo finalizado');
                }
            }
        });
    });
</script>