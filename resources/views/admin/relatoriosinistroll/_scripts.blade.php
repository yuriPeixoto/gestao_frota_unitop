<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('relatoriosinistroll', {
            loading: false,

            //Aqui colcamos os parâmetros de acordo com o html
            coletarDadosFormulario() {
                return {
                    data_inclusao: document.querySelector('select[name="data_inclusao"], #data_inclusao')?.value || '',
                    data_final: document.querySelector('select[name="data_final"], #data_final')?.value || '',
                    id_filial: document.querySelector('select[name="id_filial"], #id_filial')?.value || '',
                    id_veiculo: document.querySelector('select[name="id_veiculo"], #id_veiculo')?.value || '',
                    situacao_sinistro_processo: document.querySelector('select[name="situacao_sinistro_processo"], #situacao_sinistro_processo')?.value || '',
                    id_motorista: document.querySelector('select[name="id_motorista"], #id_motorista')?.value || '',

                };
            },

            validarDatas(data) {
                if (!data.data_inclusao || !data.data_final) {
                    this.mostrarAlerta('Por favor, informe a data inicial e final para emissão do relatório.');
                    return false;
                }
                return true;
            },
            mostrarAlerta(mensagem) {
                alert(mensagem);
            },

            mostrarLoading() {
                this.removerLoading();

                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'loading-message';
                loadingDiv.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0,0,0,0.75);
                    color: white;
                    padding: 20px 30px;
                    border-radius: 10px;
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    text-align: center;
                    z-index: 9999;
                `;
                loadingDiv.innerHTML = `
                    <div style="margin-bottom: 10px;">
                        <div style="
                            border: 4px solid #f3f3f3;
                            border-top: 4px solid #3498db;
                            border-radius: 50%;
                            width: 40px;
                            height: 40px;
                            animation: spin 1s linear infinite;
                            margin: 0 auto;">
                        </div>
                    </div>
                    Gerando relatório...<br><small>Isso pode levar alguns minutos</small>
                `;

                if (!document.getElementById('loading-style')) {
                    const style = document.createElement('style');
                    style.id = 'loading-style';
                    style.textContent = `
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    `;
                    document.head.appendChild(style);
                }

                document.body.appendChild(loadingDiv);
            },

            removerLoading() {
                const elem = document.getElementById('loading-message');
                if (elem) elem.remove();
            },

            obterCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!token) throw new Error('Token CSRF não encontrado');
                return token;
            },

            async fazerRequisicao(url, data, timeout = 300000) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.obterCSRFToken(),
                            'Accept': 'application/pdf, application/vnd.ms-excel, application/json'
                        },
                        body: JSON.stringify(data),
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        try {
                            const errJson = await response.json();
                            throw new Error(errJson.message || `Erro ${response.status}`);
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

            fazerDownload(blob, nomeArquivo) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = nomeArquivo;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);
            },

            async gerarPdf() {
                this.loading = true;

                try {
                    const dados = this.coletarDadosFormulario();

                   if (!this.validarDatas(dados)) return;

                    this.mostrarLoading();

                    const response = await this.fazerRequisicao('/admin/relatoriosinistroll/gerarpdf', dados);
                    const blob = await response.blob();

                    this.fazerDownload(blob, `relatorio_sinistro${new Date().toISOString().slice(0,10)}.pdf`);

                } catch (error) {
                    console.error('Erro ao gerar PDF:', error);
                    if (error.name === 'AbortError') {
                        this.mostrarAlerta('Timeout: A requisição demorou muito tempo. Tente filtrar por um período menor.');
                    } else {
                        this.mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this.removerLoading();
                }
            },
            async gerarPdfTotalizado() {
                this.loading = true;

                try {
                    const dados = this.coletarDadosFormulario();

                   if (!this.validarDatas(dados)) return;

                    this.mostrarLoading();

                    const response = await this.fazerRequisicao('/admin/relatoriosinistroll/gerarpdftotalizado', dados);
                    const blob = await response.blob();

                    this.fazerDownload(blob, `relatorio_sinistro${new Date().toISOString().slice(0,10)}.pdf`);

                } catch (error) {
                    console.error('Erro ao gerar PDF:', error);
                    if (error.name === 'AbortError') {
                        this.mostrarAlerta('Timeout: A requisição demorou muito tempo. Tente filtrar por um período menor.');
                    } else {
                        this.mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this.removerLoading();
                }
            },
            async gerarExcel() {
                this.loading = true;

                try {
                    const dados = this.coletarDadosFormulario();

                   if (!this.validarDatas(dados)) return;

                    this.mostrarLoading();

                    const response = await this.fazerRequisicao('/admin/relatoriosinistroll/gerarexcel', dados);
                    const blob = await response.blob();

                    this.fazerDownload(blob, `relatorio_sinistro${new Date().toISOString().slice(0,10)}.xls`);

                } catch (error) {
                    console.error('Erro ao gerar Xls:', error);
                    if (error.name === 'AbortError') {
                        this.mostrarAlerta('Timeout: A requisição demorou muito tempo. Tente filtrar por um período menor.');
                    } else {
                        this.mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                    }
                } finally {
                    this.loading = false;
                    this.removerLoading();
                }
            }
        });
    });
</script>