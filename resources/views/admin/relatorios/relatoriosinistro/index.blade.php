<style>
    .dropdown-active {
        overflow: visible !important;
        z-index: 30;
    }

    [x-data*="simpleSelect"] [role="listbox"] {
        z-index: 50;
    }

    .smart-select-container {
        position: relative;
        z-index: 40;
    }

    /* Estilos para o loading */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Relatório de Sinistro') }}
            </h2>

        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="space-y-4">

                    {{-- Exibir mensagens de erro/confirmação --}}
                    <x-ui.export-message />
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inicial"
                            value="{{ request('data_inclusao') }}" />

                        <x-forms.input type="date" name="data_inclusao_final" label="Data Final"
                            value="{{ request('data_inclusao_final') }}" />

                        <x-forms.smart-select name="id_sinistro" label="Código Sinistro" :options="$sinistros"
                            asyncSearch="false" :selected="request('id_sinistro') ?? ''" />

                        <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                            :options="$filiais" asyncSearch="false" />

                        <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a Placa..."
                            :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="true" />

                        <x-forms.smart-select name="status" label="status" :options="[
                            ['value' => 'Finalizada', 'label' => 'Finalizada'],
                            ['value' => 'Em Andamento', 'label' => 'Em Andamento'],
                        ]" asyncSearch="false"
                            :selected="request('status') ?? ''" />

                        <div class="col-span-2">
                            <x-forms.smart-select name="id_motorista" label="Nome do Motorista"
                                placeholder="Selecione o motorista..." :options="$motoristas" :searchUrl="route('admin.api.motoristas.search')"
                                asyncSearch="true" />
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <div class="flex space-x-2">
                            <button type="button" x-on:click="$store.utils.onGeneratePDF()"
                                :disabled="$store.utils.loading"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">

                                <!-- Ícone de loading (quando carregando) -->
                                <span x-show="$store.utils.loading" class="loading-spinner mr-2"></span>
                                <!-- Ícone normal (quando não carregando) -->
                                <x-icons.magnifying-glass x-show="!$store.utils.loading" class="h-4 w-4 mr-2" />

                                <!-- Texto do botão -->
                                <span x-text="$store.utils.loading ? 'Gerando...' : 'Gerar PDF'"></span>
                            </button>

                            <button type="button" x-on:click="$store.utils.imprimirSinistroExcel()"
                                :disabled="$store.utils.loading"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">

                                <!-- Ícone de loading (quando carregando) -->
                                <span x-show="$store.utils.loading" class="loading-spinner mr-2"></span>
                                <!-- Ícone normal (quando não carregando) -->
                                <x-icons.magnifying-glass x-show="!$store.utils.loading" class="h-4 w-4 mr-2" />

                                <!-- Texto do botão -->
                                <span x-text="$store.utils.loading ? 'Gerando...' : 'Gerar XLS'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('utils', {
                    loading: false,

                    // Método para coletar dados do formulário
                    _coletarDadosFormulario() {
                        return {
                            dataInicio: document.getElementById('data_inclusao_inicial').value || '',
                            dataFim: document.getElementById('data_inclusao_final').value || '',
                            idVeiculo: document.getElementById('id_veiculo').value || '',
                            status: document.getElementById('status').value || '',
                            idFilial: document.getElementById('id_filial').value || '',
                            idSinitro: document.getElementById('id_sinistro').value || '',
                            idMotorista: document.getElementById('id_motorista').value || ''
                        };
                    },

                    // Método para validar datas
                    _validarDatas(data) {
                        if (!data.dataInicio || !data.dataFim) {
                            this._mostrarAlerta(
                                'Por favor, informe a data inicial e final para emissão do relatório.');
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
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');
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
                                    throw new Error(errorData.message ||
                                        `Erro ${response.status}: ${response.statusText}`);
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
                    async onGeneratePDF() {
                        this.loading = true;

                        try {
                            const data = this._coletarDadosFormulario();

                            this._mostrarLoading();

                            const response = await this._fazerRequisicao(
                                '/admin/relatorios/relatoriosinistro/onGeneratePdf', data);
                            const blob = await response.blob();

                            this._fazerDownload(blob,
                                `relatorio_sinistro_${new Date().toISOString().split('T')[0]}.pdf`);

                        } catch (error) {
                            console.error('Erro ao gerar relatório PDF:', error);

                            if (error.name === 'AbortError') {
                                this._mostrarAlerta(
                                    'Timeout: A requisição demorou mais de 5 minutos para responder. Tente filtrar por um período menor.'
                                );
                            } else {
                                this._mostrarAlerta('Erro ao gerar relatório: ' + error.message);
                            }
                        } finally {
                            this.loading = false;
                            this._removerLoading();
                        }
                    },

                    // Método para imprimir Excel
                    async imprimirSinistroExcel() {
                        this.loading = true;

                        try {
                            const data = this._coletarDadosFormulario();

                            // Validar datas
                            if (!this._validarDatas(data)) {
                                return;
                            }

                            console.log('Dados sendo enviados:', data);

                            this._mostrarLoading();

                            const response = await this._fazerRequisicao(
                                '/admin/relatorios/relatoriosinistro/onGenerateXls', data);

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
                            this._fazerDownload(blob,
                                `tabela_sinistro_${new Date().toISOString().split('T')[0]}.xls`);

                            console.log('Download iniciado com sucesso');

                        } catch (error) {
                            console.error('Erro ao gerar relatório Excel:', error);

                            if (error.name === 'AbortError') {
                                this._mostrarAlerta(
                                    'Timeout: A requisição demorou mais de 5 minutos para responder. O relatório pode ter muitos dados. Tente filtrar por um período menor ou entre em contato com o suporte.'
                                );
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
    @endpush
</x-app-layout>
