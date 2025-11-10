<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Painel de Controle de Multas') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Painel de Controle Multas"
                    content="Este painel apresenta um resumo completo dos dados de veículos, notificações e multas da frota." />
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Seção Veículos -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-indigo-600 mb-4 border-b border-indigo-200 pb-2">
                    <i class="fas fa-car mr-2"></i>Veículos
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Veículos Ativos -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Veículos Ativos</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['veiculos']) }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-car text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Licenciados -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Licenciados</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['licenciados']) }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Não Licenciados -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Não Licenciado</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['nao_licenciados']) }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-exclamation text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Restrições -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Restrições</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['restricoes']) }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-exclamation-triangle text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- IPVA -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">IPVA</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['ipva_total'], 2, ',',
                                    '.') }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-money-bill-wave text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Licenciamentos -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Licenciamentos</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['licenciamento_valor'], 2,
                                    ',', '.') }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-calculator text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção Notificações/Multas -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-indigo-600 mb-4 border-b border-indigo-200 pb-2">
                    <i class="fas fa-exclamation-circle mr-2"></i>Notificações/Multas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <!-- Notificações -->
                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm">Notificações</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['total_notificacoes']) }}
                                </p>
                            </div>
                            <div class="text-cyan-200">
                                <i class="fas fa-address-card text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- R$ Notificações -->
                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm">R$ Notificações</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['valor_notificacoes'], 2,
                                    ',', '.') }}</p>
                            </div>
                            <div class="text-cyan-200">
                                <i class="fas fa-comment-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Multas -->
                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm">Multas</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['multas_total']) }}</p>
                            </div>
                            <div class="text-cyan-200">
                                <i class="fas fa-exclamation-circle text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- R$ Multas -->
                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm">R$ Multas</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['valor_multas'], 2, ',',
                                    '.') }}</p>
                            </div>
                            <div class="text-cyan-200">
                                <i class="fas fa-comment-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- ANTT -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">ANTT</p>
                                <p class="text-2xl font-bold">{{ number_format($indicadores['multa_antt']) }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-ban text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- R$ ANTT -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">R$ ANTT</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['vlr_antt'], 2, ',', '.')
                                    }}</p>
                            </div>
                            <div class="text-blue-200">
                                <i class="fas fa-comment-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Segunda linha de indicadores -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Multas Vencidas -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm">Multas Vencidas</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['valor_vencidas'], 2, ',',
                                    '.') }}</p>
                            </div>
                            <div class="text-red-200">
                                <i class="fas fa-comment-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Desconto Perdido -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm">Desconto Perdido</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['desconto_perdido'], 2,
                                    ',', '.') }}</p>
                            </div>
                            <div class="text-red-200">
                                <i class="fas fa-file-invoice-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Multa a Vencer -->
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Multa a Vencer</p>
                                <p class="text-lg font-bold">R$ {{ number_format($indicadores['multa_avencer'], 2, ',',
                                    '.') }}</p>
                            </div>
                            <div class="text-green-200">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Multas com Desconto -->
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Multas com Desconto</p>
                                <p class="text-lg font-bold">R$ {{
                                    number_format($indicadores['multa_desconto_a_vencer'], 2, ',', '.') }}</p>
                            </div>
                            <div class="text-green-200">
                                <i class="fas fa-comment-dollar text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfico Multas por Placa -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Multas por Placa (Top 10)</h4>
                    <div class="relative h-80">
                        @if(count($graficos['multas_por_placa']) > 0)
                        <canvas id="chartMultasPlaca"></canvas>
                        @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Nenhuma multa registrada</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Gráfico Notificações por Órgão -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Notificações por Órgão</h4>
                    <div class="relative h-80">
                        @if(count($graficos['notificacoes_por_orgao']) > 0)
                        <canvas id="chartNotificacoesOrgao"></canvas>
                        @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Nenhuma notificação registrada</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Gráfico Notificações por Gravidade -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Notificações por Gravidade</h4>
                    <div class="relative h-80">
                        @if(count($graficos['notificacoes_por_gravidade']) > 0)
                        <canvas id="chartNotificacoesGravidade"></canvas>
                        @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Nenhuma notificação registrada</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Gráfico Multas por Veículo -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Multas por Veículo (Top 10)</h4>
                    <div class="relative h-80">
                        @if(count($graficos['multas_por_veiculo']) > 0)
                        <canvas id="chartMultasVeiculo"></canvas>
                        @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Nenhuma multa registrada</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuração dos gráficos
            const chartConfig = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            };

            // Cores para os gráficos
            const colors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
            ];

            // Dados dos gráficos

            // Verifique se os elementos do gráfico existem
            function initCharts() {
                try {
                    // Seu código de inicialização dos gráficos aqui
                } catch (error) {
                    console.error('Erro ao carregar gráficos:', error);
                    // Exibe mensagens de erro nos containers
                    const errorElements = document.querySelectorAll('.chart-container');
                    errorElements.forEach(el => {
                        if (!el.querySelector('p')) {
                            el.innerHTML = '<p class="text-red-500">Erro ao carregar gráfico</p>';
                        }
                    });
                }
            }

            // Execute quando o DOM estiver pronto
            document.addEventListener('DOMContentLoaded', initCharts);
            const chartData = {
                multasPlaca: {
                    labels: [
                        @foreach($graficos['multas_por_placa'] as $item)
                            @if(trim($item->placa) !== '')
                            '{{ addslashes(trim($item->placa)) }}',
                            @endif
                        @endforeach
                    ],
                    data: [
                        @foreach($graficos['multas_por_placa'] as $item)
                            @if(trim($item->placa) !== '')
                            {{ $item->total }},
                            @endif
                        @endforeach
                    ]
                },
                notificacoesOrgao: {
                    labels: [
                        @foreach($graficos['notificacoes_por_orgao'] as $item)
                            @if(trim($item->orgao_autuador) !== '')
                            '{{ addslashes(trim($item->orgao_autuador)) }}',
                            @endif
                        @endforeach
                    ],
                    data: [
                        @foreach($graficos['notificacoes_por_orgao'] as $item)
                            @if(trim($item->orgao_autuador) !== '')
                            {{ $item->total }},
                            @endif
                        @endforeach
                    ]
                },
                notificacoesGravidade: {
                    labels: [
                        @foreach($graficos['notificacoes_por_gravidade'] as $item)
                            @if(trim($item->gravidade ?? '') !== '')
                            '{{ addslashes(trim($item->gravidade)) }}',
                            @else
                            'Não informado',
                            @endif
                        @endforeach
                    ],
                    data: [
                        @foreach($graficos['notificacoes_por_gravidade'] as $item)
                            {{ $item->total }},
                        @endforeach
                    ]
                },
                multasVeiculo: {
                    labels: [
                        @foreach($graficos['multas_por_veiculo'] as $item)
                            @if(trim($item->placa) !== '')
                            '{{ addslashes(trim($item->placa)) }}',
                            @endif
                        @endforeach
                    ],
                    data: [
                        @foreach($graficos['multas_por_veiculo'] as $item)
                            @if(trim($item->placa) !== '')
                            {{ $item->total }},
                            @endif
                        @endforeach
                    ]
                }
            };

            // Gráfico Multas por Placa (Barra Horizontal)
            

            // Gráfico Notificações por Órgão (Pizza)
            

            // Gráfico Notificações por Gravidade (Donut)
            

            // Gráfico Multas por Veículo (Barra Vertical)
            const ctxMultasVeiculo = document.getElementById('chartMultasVeiculo').getContext('2d');
            new Chart(ctxMultasVeiculo, {
                type: 'bar',
                data: {
                    labels: chartData.multasVeiculo.labels,
                    datasets: [{
                        label: 'Quantidade de Multas',
                        data: chartData.multasVeiculo.data,
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

            // Atualização automática dos dados a cada 5 minutos
            setInterval(function() {
                location.reload();
            }, 300000); // 5 minutos

            // Gráfico de Multas por Placa (Valor)
            if (chartData.multasPlaca.labels.length > 0 && chartData.multasPlaca.data.length > 0) {
                const ctxMultasPlaca = document.getElementById('chartMultasPlaca').getContext('2d');
                new Chart(ctxMultasPlaca, {
                    type: 'bar',
                    data: {
                        labels: chartData.multasPlaca.labels,
                        datasets: [{
                            label: 'Valor das Multas (R$)',
                            data: chartData.multasPlaca.data,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'R$ ' + context.parsed.x.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartMultasPlaca').innerHTML = '<p class="text-center text-gray-500">Nenhum dado disponível</p>';
            }

            // Gráfico de Multas por Veículo (Quantidade)
            if (chartData.multasVeiculo.labels.length > 0 && chartData.multasVeiculo.data.length > 0) {
                const ctxMultasVeiculo = document.getElementById('chartMultasVeiculo').getContext('2d');
                new Chart(ctxMultasVeiculo, {
                    type: 'bar',
                    data: {
                        labels: chartData.multasVeiculo.labels,
                        datasets: [{
                            label: 'Quantidade de Multas',
                            data: chartData.multasVeiculo.data,
                            backgroundColor: 'rgba(255, 99, 132, 0.6)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('chartMultasVeiculo').innerHTML = '<p class="text-center text-gray-500">Nenhum dado disponível</p>';
            }
    </script>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Verifica se existem dados e se o elemento canvas existe
        function initChartMultasPlaca() {
                const canvas = document.getElementById('chartMultasPlaca');
                if (!canvas) return; // Sai da função se o canvas não existir
                
                const ctx = canvas.getContext('2d');
                const chartData = @json($graficos['multas_por_placa']);
                
                if (chartData.length === 0) return; // Sai se não houver dados
                
                // Prepara labels e dados
                const labels = chartData.map(item => item.placa || 'Sem placa');
                const data = chartData.map(item => parseFloat(item.total));
                
                // Cores dinâmicas
                const colors = [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#EC4899', '#14B8A6', '#F97316', '#64748B', '#06B6D4'
                ].slice(0, data.length);
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Valor das Multas (R$)',
                            data: data,
                            backgroundColor: colors,
                            borderColor: colors.map(color => shadeColor(color, -20)),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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
                            legend: {
                                display: false
                            },
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

            // Função para escurecer cores (para as bordas)
            function shadeColor(color, percent) {
                let R = parseInt(color.substring(1,3), 16);
                let G = parseInt(color.substring(3,5), 16);
                let B = parseInt(color.substring(5,7), 16);

                R = parseInt(R * (100 + percent) / 100);
                G = parseInt(G * (100 + percent) / 100);
                B = parseInt(B * (100 + percent) / 100);

                R = (R<255)?R:255;  
                G = (G<255)?G:255;  
                B = (B<255)?B:255;  

                return `#${((1 << 24) + (R << 16) + (G << 8) + B).toString(16).slice(1)}`;
            }

            // Inicializa o gráfico
            initChartMultasPlaca();
        });
    </script>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Verifica se existem dados
    const notificacoesOrgao = @json($graficos['notificacoes_por_orgao']);
    
    // Só tenta criar o gráfico se houver dados e se o canvas existir
    if (notificacoesOrgao.length > 0) {
        const canvas = document.getElementById('chartNotificacoesOrgao');
        
        if (canvas) {
            const ctx = canvas.getContext('2d');
            
            // Prepara os dados
            const labels = notificacoesOrgao.map(item => item.orgao_autuador || 'Sem órgão');
            const data = notificacoesOrgao.map(item => parseInt(item.total));
            
            // Cores dinâmicas
            const colors = [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#EC4899', '#14B8A6', '#F97316', '#64748B', '#06B6D4'
            ].slice(0, data.length);
            
            // Cria o gráfico
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});
    </script>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Verifica se existem dados
    const notificacoesGravidade = @json($graficos['notificacoes_por_gravidade']);
    
    // Só tenta criar o gráfico se houver dados e se o canvas existir
    if (notificacoesGravidade.length > 0) {
        const canvas = document.getElementById('chartNotificacoesGravidade');
        
        if (canvas) {
            const ctx = canvas.getContext('2d');
            
            // Prepara os dados
            const labels = notificacoesGravidade.map(item => {
                // Padroniza os rótulos de gravidade
                const gravidade = item.gravidade || 'Sem classificação';
                return gravidade.charAt(0).toUpperCase() + gravidade.slice(1).toLowerCase();
            });
            
            const data = notificacoesGravidade.map(item => parseInt(item.total));
            
            // Cores por gravidade (vermelho para gravíssimas, laranja para graves, etc)
            const severityColors = {
                'Gravíssima': '#EF4444',
                'Grave': '#F59E0B',
                'Média': '#3B82F6',
                'Leve': '#10B981',
                'Sem classificação': '#64748B'
            };
            
            // Mapeia as cores conforme a gravidade
            const backgroundColors = labels.map(label => {
                return severityColors[label] || '#8B5CF6'; // Cor padrão para gravidades não mapeadas
            });
            
            // Cria o gráfico de rosca (doughnut)
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%', // Controla o tamanho do buraco central
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        },
                        // Adiciona título no centro do doughnut
                        doughnutCenterText: {
                            text: "Total\n" + data.reduce((a, b) => a + b, 0),
                            color: "#4B5563",
                            fontStyle: "Arial",
                            sidePadding: 20
                        }
                    }
                },
                plugins: [{
                    id: 'doughnutCenterText',
                    beforeDraw: function(chart) {
                        if (chart.config.options.plugins.doughnutCenterText) {
                            const width = chart.width,
                                height = chart.height,
                                ctx = chart.ctx;
                            
                            ctx.restore();
                            const fontSize = (height / 114).toFixed(2);
                            ctx.font = `bold ${fontSize}em ${chart.config.options.plugins.doughnutCenterText.fontStyle || "Arial"}`;
                            ctx.textBaseline = "middle";
                            
                            const text = chart.config.options.plugins.doughnutCenterText.text,
                                textX = Math.round((width - ctx.measureText(text).width) / 2),
                                textY = height / 2;
                            
                            ctx.fillStyle = chart.config.options.plugins.doughnutCenterText.color || "#000";
                            ctx.fillText(text, textX, textY);
                            ctx.save();
                        }
                    }
                }]
            });
        }
    }
});
    </script>
    @endpush
</x-app-layout>