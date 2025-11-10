<x-app-layout>

    @if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-4">
        <ul class="list-inside list-disc text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manutenção Prêmio') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{route('admin.manutencaopremio.show')}}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Abrir registro sem Login
                </a>

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Manutenção Prêmio
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe o dashboard do Prêmio. Use os filtros abaixo para
                                    refinar sua busca.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->


                <div class="w-full px-44 space-y-10 mt-2">
                    <!-- Cabeçalho -->
                    <div class="flex justify-between items-center">
                        <p class="text-gray-500 text-sm">Atualizado em {{ now()->format('d/m/Y H:i') }}</p>
                    </div>

                    <!-- Cards principais -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @php
                        $cards = [
                        ['title' => 'Motoristas', 'value' => $totalMotoristas, 'color' => 'emerald', 'icon' => 'user',
                        'chartId' => 'motoristasSpark'],
                        ['title' => 'Distância', 'value' => number_format($totalDistancia, 0, ',', '.'), 'color' =>
                        'emerald', 'icon' => 'route', 'chartId' => 'distanciaSpark'],
                        ['title' => 'Valor do Prêmio', 'value' => 'R$ '.number_format($totalValorPremio, 2, ',', '.'),
                        'color' => 'emerald', 'icon' => 'wallet', 'chartId' => 'premioSpark'],
                        ['title' => 'Placas sem Login', 'value' => $veiculosSemLogin, 'color' => 'red', 'icon' =>
                        'truck', 'chartId' => 'placasSpark'],
                        ];
                        @endphp

                        @foreach ($cards as $c)
                        <div
                            class="bg-{{ $c['color'] }}-600 text-white rounded-xl p-6 shadow-md hover:shadow-lg transition transform hover:-translate-y-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm uppercase opacity-80">{{ $c['title'] }}</p>
                                    <h3 class="text-3xl font-bold mt-1">{{ $c['value'] }}</h3>
                                </div>
                                <div class="bg-white/20 rounded-full p-3">
                                    @if ($c['icon'] === 'user')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                        class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm4-3a4 4 0 1 1-8 0 4 4 0 0 1 8 0z" />
                                        <path fill-rule="evenodd" d="M14 14s-1-1.5-6-1.5S2 14 2 14s1-4 6-4 6 4 6 4z" />
                                    </svg>
                                    @elseif ($c['icon'] === 'route')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                        class="bi bi-sign-turn-slight-right" viewBox="0 0 16 16">
                                        <path
                                            d="m8.335 6.982.8 1.386a.25.25 0 0 0 .451-.039l1.06-2.882a.25.25 0 0 0-.192-.333l-3.026-.523a.25.25 0 0 0-.26.371l.667 1.154-.621.373A2.5 2.5 0 0 0 6 8.632V11h1V8.632a1.5 1.5 0 0 1 .728-1.286z" />
                                        <path fill-rule="evenodd"
                                            d="M6.95.435c.58-.58 1.52-.58 2.1 0l6.515 6.516c.58.58.58 1.519 0 2.098L9.05 15.565c-.58.58-1.519.58-2.098 0L.435 9.05a1.48 1.48 0 0 1 0-2.098z" />
                                    </svg>
                                    @elseif ($c['icon'] === 'wallet')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                        class="bi bi-wallet2" viewBox="0 0 16 16">
                                        <path d="M12 6V5a1 1 0 0 0-1-1H2V3h9a2 2 0 0 1 2 2v1z" />
                                        <path
                                            d="M15 8a2 2 0 0 0-2-2H2a1 1 0 0 0-1 1v5a2 2 0 0 0 2 2h11a1 1 0 0 0 1-1V8zM2 7h11a1 1 0 0 1 1 1v1H2V7z" />
                                    </svg>
                                    @elseif ($c['icon'] === 'truck')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor"
                                        class="bi bi-truck" viewBox="0 0 16 16">
                                        <path
                                            d="M0 3a1 1 0 0 1 1-1h9v2h1.293a1 1 0 0 1 .707.293L15 8v5h-1a2 2 0 1 1-4 0H6a2 2 0 1 1-4 0H1a1 1 0 0 1-1-1V3z" />
                                    </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Sparkline -->
                            <canvas id="{{ $c['chartId'] }}" height="60" class="mt-4"></canvas>
                        </div>
                        @endforeach
                    </div>

                    <!-- Gráficos principais -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-700">Distância percorrida por mês</h2>
                                <span class="text-sm text-gray-600">Total: <strong>{{ number_format($totalDistancia, 2,
                                        ',', '.') }} km</strong></span>
                            </div>

                            <canvas id="distanciaChart" height="120"></canvas>
                        </div>
                        @php
                        // Valores originais vindos do backend
                        $semLogin = $distanciaSemLogin ?? 0;
                        $inconsistencia = $distanciaInconsistencia ?? 0;
                        $inconsistenciaMensal = $distanciaInconsistenciaMensal ?? 0;

                        // Soma total
                        $total = $semLogin + $inconsistencia + $inconsistenciaMensal;

                        // Cálculo percentual
                        $semLoginPercent = $total > 0 ? round(($semLogin / $total) * 100, 2) : 0;
                        $inconsistenciaPercent = $total > 0 ? round(($inconsistencia / $total) * 100, 2) : 0;
                        $inconsistenciaMensalPercent = $total > 0 ? round(($inconsistenciaMensal / $total) * 100, 2) :
                        0;
                        @endphp
                        <div class="bg-white rounded-xl shadow-md p-4">
                            <h2 class="text-lg font-semibold mb-4 text-gray-700">Inconsistências de Rotas</h2>

                            <canvas id="inconsistenciaChart" height="100" style="max-height: 300px;"></canvas>

                            <div class="mt-4 border-t pt-3">
                                <h3 class="text-sm font-medium text-gray-600 mb-2">Totais de Distância</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div
                                        class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-lg p-2">
                                        <span class="inline-block w-3 h-3 rounded-full bg-[#F97316]"></span>
                                        <span class="text-gray-700 font-medium">
                                            Sem Login:
                                            <span class="font-semibold text-gray-800">
                                                {{ number_format($distanciaSemLogin ?? 0, 0, ',', '.') }}
                                            </span>
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-lg p-2">
                                        <span class="inline-block w-3 h-3 rounded-full bg-[#EA580C]"></span>
                                        <span class="text-gray-700 font-medium">
                                            Inconsistência RV:
                                            <span class="font-semibold text-gray-800">
                                                {{ number_format($distanciaInconsistencia ?? 0, 0, ',', '.') }}
                                            </span>
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 bg-orange-50 border border-orange-200 rounded-lg p-2">
                                        <span class="inline-block w-3 h-3 rounded-full bg-[#C2410C]"></span>
                                        <span class="text-gray-700 font-medium">
                                            Inconsistência Mensal:
                                            <span class="font-semibold text-gray-800">
                                                {{ number_format($distanciaInconsistenciaMensal ?? 0, 0, ',', '.') }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <br />
                <!-- Results Table -->
                @include('admin.manutencaopremio._search-form')
                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        @include('admin.manutencaopremio._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
    // Sparkline charts for each card
            const sparkConfigs = [
                { id: 'motoristasSpark', color: '#059669', data: [10, 12, 14, 15, 13, 16, 18] },
                { id: 'distanciaSpark', color: '#0d9488', data: [1000, 1200, 1500, 1300, 1800, 2100, 2500] },
                { id: 'premioSpark', color: '#047857', data: [500, 600, 550, 700, 850, 900, 1000] },
                { id: 'placasSpark', color: '#ef4444', data: [5, 6, 8, 7, 10, 9, 12] },
            ];

            sparkConfigs.forEach(cfg => {
                const el = document.getElementById(cfg.id);
                if (!el) return; // evita erro se id não existir
                new Chart(el, {
                    type: 'line',
                    data: {
                        labels: ['', '', '', '', '', '', ''],
                        datasets: [{
                            data: cfg.data,
                            borderColor: cfg.color,
                            backgroundColor: cfg.color + '20',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        responsive: true,
                        scales: { x: { display: false }, y: { display: false } },
                        elements: { line: { borderJoinStyle: 'round' } }
                    }
                });
            });

            // Main chart: Distance per month
             const ctx = document.getElementById('distanciaChart')?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($labels),
                        datasets: [{
                            label: 'Distância (Km)',
                            data: @json($valores),
                            backgroundColor: '#10b98180',
                            borderColor: '#059669',
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#4B5563' } },
                            x: { ticks: { color: '#4B5563' } }
                        }
                    }
                });
            }

            // Doughnut chart: inconsistências
             const inconsistenciaCtx = document.getElementById('inconsistenciaChart')?.getContext('2d');
            if (inconsistenciaCtx) {
                new Chart(inconsistenciaCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sem Login', 'Inconsistência RV', 'Inconsistência Mensal'],
                        datasets: [{
                            data: [
                                {{ $semLoginPercent }},
                                {{ $inconsistenciaPercent }},
                                {{ $inconsistenciaMensalPercent }}
                            ],
                            backgroundColor: ['#F97316', '#EA580C', '#C2410C'],
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#374151' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.raw;
                                        return `${label}: ${value}%`;
                                    }
                                }
                            },
                            title: {
                                display: false
                            }
                        },
                        cutout: '60%', // Tamanho do furo central
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>

</x-app-layout>