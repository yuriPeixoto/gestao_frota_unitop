<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Gráfico de Descontos por Fornecedor (LADO ESQ                         ">
                        <div class="flex items-center justify-between">RDO) -->
    <div class="rounded-lg bg-white p-6 shadow-lg">
        <h2 class="mb-4 text-lg font-medium leading-6 text-gray-900">
            <i class="fas fa-chart-line"></i> Cotações
        </h2>
        <div class="relative h-80">
            @if (isset($dadosGraficoDescontos) && $dadosGraficoDescontos->count() > 0)
                <canvas id="chartDescontosFornecedor"></canvas>
            @else
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-12 w-12" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p>Nenhum desconto encontrado para esta solicitação</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Resumo da Cotação (LADO DIREITO) -->
    <div class="rounded-lg bg-white p-6 shadow-lg">
        <h2 class="mb-4 text-lg font-medium leading-6 text-gray-900">
            <i class="fas fa-file-invoice-dollar"></i> Resumo da Cotação
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <i class="fas fa-truck"></i> Fornecedor (Cotação)
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <i class="fas fa-tag"></i> Valor Total Sem Desconto
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            <i class="fas fa-percentage"></i> Valor Total Com Desconto
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @if (isset($dadosGraficoDescontos) && $dadosGraficoDescontos->count() > 0)
                        @foreach ($dadosGraficoDescontos as $cotacao)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                    {{ $cotacao['nome_fornecedor'] }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    R$ {{ number_format($cotacao['valor_total_sem_desconto'], 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    R$ {{ number_format($cotacao['valor_desconto'], 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-info-circle mb-2"></i><br>
                                Nenhuma cotação encontrada para esta solicitação
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Mapa de Cotações --}}
@if (isset($dadosGraficoDescontos) && count($dadosGraficoDescontos) > 0)
    <div class="mt-6 rounded-lg bg-white p-6 shadow-lg">
        <div class="rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 p-4">
                <h3 class="text-lg font-medium text-gray-900">Mapa de Cotações</h3>
                <p class="text-sm text-gray-500">Total de {{ count($dadosGraficoDescontos) }} cotações encontradas</p>
            </div>

            {{-- Container com scroll --}}
            <div class="max-h-96 overflow-y-auto">
                @foreach ($dadosGraficoDescontos as $index => $cotacao)
                    <div class="border-b border-gray-200 last:border-b-0">
                        {{-- Cabeçalho da Cotação --}}
                        <div class="cursor-pointer p-4 transition-colors hover:bg-gray-50"
                            onclick="
                                var content = document.getElementById('cotacao-{{ $index }}');
                                var icon = document.getElementById('icon-{{ $index }}');
                                if (content) {
                                    if (content.classList.contains('hidden')) {
                                        content.classList.remove('hidden');
                                        if (icon) icon.style.transform = 'rotate(180deg)';
                                    } else {
                                        content.classList.add('hidden');
                                        if (icon) icon.style.transform = 'rotate(0deg)';
                                    }
                                } else {
                                    console.error('Elemento cotacao-{{ $index }} não encontrado');
                                }
                             ">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">
                                        {{ $cotacao['nome_fornecedor'] }}
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                        <span>ID: {{ $cotacao['id_cotacoes'] }}</span>
                                        @if (isset($cotacao['data_entrega']))
                                            <span>Data Entrega:
                                                {{ date('d/m/Y', strtotime($cotacao['data_entrega'])) }}</span>
                                        @endif
                                        <span class="font-medium">{{ count($cotacao['itens']) }} item(ns)</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-green-600">
                                        R$ {{ number_format($cotacao['valor_total_sem_desconto'] ?? 0, 2, ',', '.') }}
                                    </span>
                                    <svg id="icon-{{ $index }}"
                                        class="h-5 w-5 transform text-gray-400 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Conteúdo dos Itens (inicialmente oculto) --}}
                        <div id="cotacao-{{ $index }}"
                            class="hidden bg-gray-50 transition-all duration-300 ease-in-out">
                            @if (count($cotacao['itens']) > 0)
                                <div class="px-4 pb-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full table-auto">
                                            <thead>
                                                <tr class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                                    <th class="px-2 py-2 text-left">Produto</th>
                                                    <th class="px-2 py-2 text-left">Unidade</th>
                                                    <th class="px-2 py-2 text-right">Qtd</th>
                                                    <th class="px-2 py-2 text-right">Valor Unit.</th>
                                                    <th class="px-2 py-2 text-right">Total</th>
                                                    <th class="px-2 py-2 text-right">Desconto</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach ($cotacao['itens'] as $item)
                                                    <tr class="text-sm">
                                                        <td class="px-2 py-2 text-gray-900">
                                                            {{ $item['descricao_produto'] }}
                                                        </td>
                                                        <td class="px-2 py-2 text-gray-500">
                                                            {{ $item['unidade'] }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right text-gray-900">
                                                            {{ number_format($item['quantidade'], 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right text-gray-900">
                                                            R$
                                                            {{ number_format($item['valor_unitario'], 2, ',', '.') }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right font-medium text-gray-900">
                                                            R$
                                                            {{ number_format($item['quantidade'] * $item['valor_unitario'], 2, ',', '.') }}
                                                        </td>
                                                        <td class="px-2 py-2 text-right text-red-600">
                                                            R$
                                                            {{ number_format($item['valor_desconto'], 2, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="px-4 pb-4 text-center text-sm text-gray-500">
                                    Nenhum item encontrado para esta cotação
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="mt-6 rounded-lg bg-white p-6 shadow-lg">
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma cotação encontrada</h3>
                <p class="mt-1 text-sm text-gray-500">Não há cotações disponíveis para exibir no mapa.</p>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        // Função para controlar o accordion das cotações
        function toggleCotacao(index) {
            const content = document.getElementById(`cotacao-${index}`);
            const icon = document.getElementById(`icon-${index}`);

            if (content) {
                const isHidden = content.classList.contains('hidden');

                if (isHidden) {
                    // Expandir
                    content.classList.remove('hidden');
                    if (icon) {
                        icon.style.transform = 'rotate(180deg)';
                        icon.style.transition = 'transform 0.3s ease';
                    }
                } else {
                    // Contrair
                    content.classList.add('hidden');
                    if (icon) {
                        icon.style.transform = 'rotate(0deg)';
                        icon.style.transition = 'transform 0.3s ease';
                    }
                }

                // Adicionar um pequeno delay para garantir que a transição aconteça
                setTimeout(() => {
                    console.log('Estado após toggle - hidden:', content.classList.contains('hidden'));
                }, 100);
            } else {
                console.error('Elemento cotacao-' + index + ' não encontrado');
                // Listar todos os elementos com ID similar para debug
                const allElements = document.querySelectorAll('[id^="cotacao-"]');
            }
        }

        // Disponibilizar a função globalmente
        window.toggleCotacao = toggleCotacao;

        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Descontos por Fornecedor (estilo linha com informações para a direita)
            const canvas = document.getElementById('chartDescontosFornecedor');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                const dadosDescontos = @json($dadosGraficoDescontos ?? []);

                if (dadosDescontos && dadosDescontos.length > 0) {
                    // Prepara labels e dados
                    const labels = dadosDescontos.map((item, index) => {
                        // Trunca o nome se for muito longo
                        const nomeCompleto = item.nome_fornecedor;
                        const nomeFormatado = nomeCompleto.length > 25 ?
                            nomeCompleto.substring(0, 25) + '...' :
                            nomeCompleto;
                        return `${nomeFormatado}`;
                    });
                    const data = dadosDescontos.map(item => parseFloat(item.valor_desconto) || 0);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Valor',
                                data: data,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: {
                                    right: 20,
                                    left: 10,
                                    top: 10,
                                    bottom: 10
                                }
                            },
                            scales: {
                                x: {
                                    position: 'bottom',
                                    grid: {
                                        display: true,
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        maxRotation: data.length > 5 ? 45 :
                                        0, // Rotaciona labels se houver muitos dados
                                        minRotation: 0,
                                        align: 'start', // Alinha para a direita
                                        font: {
                                            size: data.length > 8 ? 8 :
                                                10 // Fonte menor se houver muitos dados
                                        },
                                        padding: data.length > 8 ? 5 : 10,
                                        maxTicksLimit: data.length <= 15 ? undefined :
                                            15 // Limita ticks apenas se houver muitos dados
                                    }
                                },
                                y: {
                                    position: 'right', // Eixo Y à direita
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        },
                                        font: {
                                            size: 10
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)',
                                        drawOnChartArea: true
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: 'white',
                                    bodyColor: 'white',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    position: 'nearest',
                                    callbacks: {
                                        title: function(context) {
                                            return dadosDescontos[context[0].dataIndex].nome_fornecedor;
                                        },
                                        label: function(context) {
                                            const item = dadosDescontos[context.dataIndex];
                                            return [
                                                'Valor Desconto: R$ ' + context.parsed.y
                                                .toLocaleString(
                                                    'pt-BR', {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    }),
                                                'Cotação ID: ' + item.id_cotacoes
                                            ];
                                        }
                                    }
                                }
                            },
                            animation: {
                                duration: 1500,
                                easing: 'easeInOutQuart'
                            },
                            elements: {
                                point: {
                                    hoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                                    hoverBorderColor: '#fff'
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endpush
