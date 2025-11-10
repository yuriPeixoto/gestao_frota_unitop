/**
 * Dashboard de Multas - JavaScript API
 * Sistema de Gestão de Frota
 */

class DashboardMultas {
    constructor(config = {}) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api',
            refreshInterval: config.refreshInterval || 300000, // 5 minutos
            autoRefresh: config.autoRefresh !== false,
            ...config
        };
        
        this.data = null;
        this.charts = {};
        this.refreshTimer = null;
        
        this.init();
    }

    /**
     * Inicializa o dashboard
     */
    async init() {
        console.log('Inicializando Dashboard de Multas...');
        
        // Verificar se o DOM está pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.start());
        } else {
            this.start();
        }
    }

    /**
     * Inicia o dashboard
     */
    async start() {
        try {
            await this.loadData();
            this.renderAll();
            
            if (this.config.autoRefresh) {
                this.startAutoRefresh();
            }
            
            console.log('Dashboard iniciado com sucesso!');
        } catch (error) {
            console.error('Erro ao iniciar dashboard:', error);
            this.showError('Erro ao carregar o dashboard. Verifique sua conexão.');
        }
    }

    /**
     * Carrega dados da API ou usa dados simulados
     */
    async loadData() {
        this.showLoading();
        
        try {
            // Tentar carregar da API
            if (this.config.apiBaseUrl !== 'mock') {
                const response = await fetch(`${this.config.apiBaseUrl}/dashboard-multas`);
                if (response.ok) {
                    this.data = await response.json();
                    return;
                }
            }
            
            // Usar dados simulados se API não estiver disponível
            this.data = this.getMockData();
            
        } catch (error) {
            console.warn('API não disponível, usando dados simulados:', error);
            this.data = this.getMockData();
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Dados simulados para demonstração
     */
    getMockData() {
        return {
            indicadores: {
                veiculos: Math.floor(Math.random() * 500) + 1000,
                licenciados: Math.floor(Math.random() * 100) + 950,
                nao_licenciados: Math.floor(Math.random() * 100) + 50,
                restricoes: Math.floor(Math.random() * 50) + 10,
                ipva_total: (Math.random() * 500000) + 500000,
                licenciamento_valor: (Math.random() * 200000) + 100000,
                total_notificacoes: Math.floor(Math.random() * 200) + 200,
                valor_notificacoes: (Math.random() * 50000) + 50000,
                multas_total: Math.floor(Math.random() * 100) + 100,
                valor_multas: (Math.random() * 30000) + 30000,
                multa_antt: Math.floor(Math.random() * 20) + 5,
                vlr_antt: (Math.random() * 15000) + 5000,
                valor_vencidas: (Math.random() * 20000) + 10000,
                desconto_perdido: (Math.random() * 10000) + 5000,
                multa_avencer: (Math.random() * 25000) + 15000,
                multa_desconto_a_vencer: (Math.random() * 20000) + 10000
            },
            graficos: {
                multas_por_placa: this.generateRandomPlacaData(10),
                notificacoes_por_orgao: [
                    { orgao_autuador: 'DETRAN-SP', total: Math.floor(Math.random() * 100) + 50 },
                    { orgao_autuador: 'PRF', total: Math.floor(Math.random() * 80) + 40 },
                    { orgao_autuador: 'CET-SP', total: Math.floor(Math.random() * 60) + 30 },
                    { orgao_autuador: 'DETRAN-RJ', total: Math.floor(Math.random() * 40) + 20 }
                ],
                notificacoes_por_gravidade: [
                    { gravidade: 'Leve', total: Math.floor(Math.random() * 100) + 80 },
                    { gravidade: 'Média', total: Math.floor(Math.random() * 80) + 60 },
                    { gravidade: 'Grave', total: Math.floor(Math.random() * 60) + 40 },
                    { gravidade: 'Gravíssima', total: Math.floor(Math.random() * 30) + 10 }
                ],
                multas_por_veiculo: this.generateRandomPlacaData(10, 'count')
            }
        };
    }

    /**
     * Gera dados aleatórios de placas
     */
    generateRandomPlacaData(count, type = 'value') {
        const placas = [];
        for (let i = 0; i < count; i++) {
            const letters = String.fromCharCode(65 + Math.floor(Math.random() * 26)) +
                          String.fromCharCode(65 + Math.floor(Math.random() * 26)) +
                          String.fromCharCode(65 + Math.floor(Math.random() * 26));
            const numbers = String(Math.floor(Math.random() * 9000) + 1000);
            const placa = `${letters}-${numbers}`;
            
            placas.push({
                placa: placa,
                total: type === 'value' ? 
                       (Math.random() * 5000) + 1000 : 
                       Math.floor(Math.random() * 15) + 1
            });
        }
        return placas.sort((a, b) => b.total - a.total);
    }

    /**
     * Renderiza todos os componentes
     */
    renderAll() {
        this.renderVeiculosIndicators();
        this.renderMultasIndicators();
        this.renderCharts();
    }

    /**
     * Renderiza indicadores de veículos
     */
    renderVeiculosIndicators() {
        const container = document.getElementById('veiculos-indicators');
        if (!container) return;

        const data = this.data.indicadores;
        container.innerHTML = `
            ${this.createIndicator('Veículos Ativos', data.veiculos, 'fas fa-car', 'blue-gradient')}
            ${this.createIndicator('Licenciados', data.licenciados, 'fas fa-check', 'blue-gradient')}
            ${this.createIndicator('Não Licenciados', data.nao_licenciados, 'fas fa-exclamation', 'blue-gradient')}
            ${this.createIndicator('Restrições', data.restricoes, 'fas fa-exclamation-triangle', 'blue-gradient')}
            ${this.createIndicator('IPVA', data.ipva_total, 'fas fa-money-bill-wave', 'blue-gradient')}
            ${this.createIndicator('Licenciamentos', data.licenciamento_valor, 'fas fa-calculator', 'blue-gradient')}
        `;
    }

    /**
     * Renderiza indicadores de multas
     */
    renderMultasIndicators() {
        const container = document.getElementById('multas-indicators');
        if (!container) return;

        const data = this.data.indicadores;
        container.innerHTML = `
            ${this.createIndicator('Notificações', data.total_notificacoes, 'fas fa-address-card', 'cyan-gradient')}
            ${this.createIndicator('R$ Notificações', data.valor_notificacoes, 'fas fa-comment-dollar', 'cyan-gradient')}
            ${this.createIndicator('Multas', data.multas_total, 'fas fa-exclamation-circle', 'cyan-gradient')}
            ${this.createIndicator('R$ Multas', data.valor_multas, 'fas fa-comment-dollar', 'cyan-gradient')}
            ${this.createIndicator('ANTT', data.multa_antt, 'fas fa-ban', 'dark-blue-gradient')}
            ${this.createIndicator('R$ ANTT', data.vlr_antt, 'fas fa-comment-dollar', 'dark-blue-gradient')}
            ${this.createIndicator('Multas Vencidas', data.valor_vencidas, 'fas fa-comment-dollar', 'red-gradient')}
            ${this.createIndicator('Desconto Perdido', data.desconto_perdido, 'fas fa-file-invoice-dollar', 'red-gradient')}
            ${this.createIndicator('Multa a Vencer', data.multa_avencer, 'fas fa-calendar-check', 'green-gradient')}
            ${this.createIndicator('Multas com Desconto', data.multa_desconto_a_vencer, 'fas fa-comment-dollar', 'green-gradient')}
        `;
    }

    /**
     * Renderiza gráficos
     */
    renderCharts() {
        // Verificar se Chart.js está disponível
        if (typeof Chart === 'undefined') {
            console.error('Chart.js não está carregado');
            return;
        }

        const data = this.data.graficos;
        
        // Cores para os gráficos
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];

        // Configuração padrão dos gráficos
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        };

        // Destruir gráficos existentes se houver
        this.destroyExistingCharts();

        // Gráfico Multas por Placa (Barra Horizontal)
        const ctxMultasPlaca = document.getElementById('chartMultasPlaca');
        if (ctxMultasPlaca) {
            this.charts.multasPlaca = new Chart(ctxMultasPlaca.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.multas_por_placa.map(item => item.placa),
                    datasets: [{
                        label: 'Valor das Multas (R$)',
                        data: data.multas_por_placa.map(item => item.total),
                        backgroundColor: colors.slice(0, data.multas_por_placa.length),
                        borderColor: colors.slice(0, data.multas_por_placa.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartConfig,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        ...chartConfig.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.parsed.x.toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico Notificações por Órgão (Pizza)
        const ctxNotificacoesOrgao = document.getElementById('chartNotificacoesOrgao');
        if (ctxNotificacoesOrgao) {
            this.charts.notificacoesOrgao = new Chart(ctxNotificacoesOrgao.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: data.notificacoes_por_orgao.map(item => item.orgao_autuador),
                    datasets: [{
                        data: data.notificacoes_por_orgao.map(item => item.total),
                        backgroundColor: colors.slice(0, data.notificacoes_por_orgao.length),
                        borderWidth: 2
                    }]
                },
                options: chartConfig
            });
        }

        // Gráfico Notificações por Gravidade (Donut)
        const ctxNotificacoesGravidade = document.getElementById('chartNotificacoesGravidade');
        if (ctxNotificacoesGravidade) {
            this.charts.notificacoesGravidade = new Chart(ctxNotificacoesGravidade.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: data.notificacoes_por_gravidade.map(item => item.gravidade),
                    datasets: [{
                        data: data.notificacoes_por_gravidade.map(item => item.total),
                        backgroundColor: colors.slice(0, data.notificacoes_por_gravidade.length),
                        borderWidth: 2
                    }]
                },
                options: chartConfig
            });
        }

        // Gráfico Multas por Veículo (Barra Vertical)
        const ctxMultasVeiculo = document.getElementById('chartMultasVeiculo');
        if (ctxMultasVeiculo) {
            this.charts.multasVeiculo = new Chart(ctxMultasVeiculo.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.multas_por_veiculo.map(item => item.placa),
                    datasets: [{
                        label: 'Quantidade de Multas',
                        data: data.multas_por_veiculo.map(item => item.total),
                        backgroundColor: '#36A2EB',
                        borderColor: '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    ...chartConfig,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * Destrói gráficos existentes
     */
    destroyExistingCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }

    /**
     * Cria um indicador
     */
    createIndicator(title, value, icon, gradientClass, subtitle = '') {
        const formattedValue = this.formatValue(value, title.includes('R$'));
        
        return `
            <div class="indicator-card ${gradientClass}">
                <div class="indicator-content">
                    <div class="indicator-header">
                        <div class="indicator-title">${title}</div>
                        <div class="indicator-icon">
                            <i class="${icon}"></i>
                        </div>
                    </div>
                    <div class="indicator-value">${formattedValue}</div>
                    ${subtitle ? `<div class="indicator-subtitle">${subtitle}</div>` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Cria uma lista de dados
     */
    createDataList(data, labelKey, valueKey, isCurrency = false) {
        if (!data || data.length === 0) {
            return '<div class="no-data">Nenhum dado disponível</div>';
        }

        return data.map(item => {
            const value = isCurrency ? this.formatCurrency(item[valueKey]) : this.formatNumber(item[valueKey]);
            return `
                <div class="list-item">
                    <div class="list-item-label">${item[labelKey]}</div>
                    <div class="list-item-value">${value}</div>
                </div>
            `;
        }).join('');
    }

    /**
     * Formata valor baseado no tipo
     */
    formatValue(value, isCurrency = false) {
        if (typeof value === 'number' && isCurrency) {
            return this.formatCurrency(value);
        }
        return typeof value === 'number' ? this.formatNumber(value) : value;
    }

    /**
     * Formata moeda
     */
    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    /**
     * Formata números
     */
    formatNumber(value) {
        return new Intl.NumberFormat('pt-BR').format(value);
    }

    /**
     * Mostra loading
     */
    showLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.classList.add('show');
        }
        
        const sections = ['veiculos-section', 'multas-section', 'graficos-section'];
        sections.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.style.display = 'none';
        });
    }

    /**
     * Esconde loading
     */
    hideLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.classList.remove('show');
        }
        
        const sections = ['veiculos-section', 'multas-section', 'graficos-section'];
        sections.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.style.display = 'block';
        });
    }

    /**
     * Mostra erro
     */
    showError(message) {
        alert(message); // Em produção, use um sistema de notificações mais sofisticado
    }

    /**
     * Inicia auto-refresh
     */
    startAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        this.refreshTimer = setInterval(() => {
            console.log('Auto-refresh executado:', new Date().toLocaleString());
            this.refresh();
        }, this.config.refreshInterval);
    }

    /**
     * Para auto-refresh
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    /**
     * Atualiza dados
     */
    async refresh() {
        try {
            await this.loadData();
            this.renderAll();
            console.log('Dashboard atualizado:', new Date().toLocaleString());
        } catch (error) {
            console.error('Erro ao atualizar dashboard:', error);
        }
    }

    /**
     * Destrói a instância
     */
    destroy() {
        this.stopAutoRefresh();
        
        // Limpar charts se estiver usando Chart.js
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        
        this.charts = {};
        this.data = null;
    }
}

// Exportar para uso global
window.DashboardMultas = DashboardMultas;

// Auto-inicializar se não estiver em modo módulo
if (typeof module === 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        window.dashboardMultas = new DashboardMultas({
            apiBaseUrl: 'mock', // Usar dados simulados por padrão
            autoRefresh: true,
            refreshInterval: 300000 // 5 minutos
        });
        
        // Adicionar evento ao botão de refresh se existir
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                window.dashboardMultas.refresh();
            });
        }
    });
}
