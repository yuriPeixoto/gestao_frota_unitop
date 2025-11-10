<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">

        <div class="grid grid-cols-1 gap-6">
            <!-- apenas 1 coluna -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                $cards = [
                ['title' => 'Distância RV', 'value' => number_format($distanciaRv, 0, ',', '.'), 'color' => 'emerald',
                'icon' => 'route', 'chartId' => 'chartMotoristas'],
                ['title' => 'Distância Mensal', 'value' => number_format($distanciaMensal, 0, ',', '.'), 'color' =>
                'cyan', 'icon' => 'route', 'chartId' => 'chartDistancia'],
                ['title' => 'Valor do Prêmio', 'value' => 'R$ '.number_format($valorPremio, 2, ',', '.'), 'color' =>
                'violet', 'icon' => 'wallet', 'chartId' => 'chartPremio'],
                ];

                $colorMap = [
                'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
                'cyan' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'border' => 'border-cyan-200'],
                'violet' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-600', 'border' => 'border-violet-200'],
                ];
                @endphp

                @foreach ($cards as $c)
                @php $cColor = $colorMap[$c['color']]; @endphp

                <div
                    class="bg-white border {{ $cColor['border'] }} rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] uppercase font-semibold {{ $cColor['text'] }}">{{ $c['title'] }}</p>
                            <h3 class="text-lg font-bold text-gray-800 mt-0.5">{{ $c['value'] }}</h3>
                        </div>

                        <div class="{{ $cColor['bg'] }} {{ $cColor['text'] }} rounded-full p-2">
                            @if ($c['icon'] === 'route')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 21h4a4 4 0 004-4V7a4 4 0 00-4-4H6a4 4 0 00-4 4v5h5m0 0l3 3-3 3m3-3H2" />
                            </svg>
                            @elseif ($c['icon'] === 'wallet')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7h18a2 2 0 012 2v6a2 2 0 01-2 2H3V7zm0 0V5a2 2 0 012-2h11" />
                            </svg>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 h-12 flex justify-end">
                        <canvas id="{{ $c['chartId'] }}" class="w-16"></canvas>
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <x-forms.input label="Cód. Prêmio:" name="cod_premio"
                    value="{{ old('cod_premio', $veiculo->cod_premio ?? '') }}" readonly />
                <x-forms.input label="Motorista:" name="nome_motorista"
                    value="{{ old('nome_motorista', $veiculo->nome_motorista ?? '') }}" readonly />
            </div>
            <!-- Cards principais -->




            <!-- Registros Calculados -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Registros Calculados</h2>
                <div class="results-table overflow-x-auto">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Placa</x-tables.head-cell>
                            <x-tables.head-cell>Distância</x-tables.head-cell>
                            <x-tables.head-cell>Média</x-tables.head-cell>
                            <x-tables.head-cell>Valor Prêmio</x-tables.head-cell>
                            <x-tables.head-cell>Excedente</x-tables.head-cell>
                            <x-tables.head-cell>Subcategoria</x-tables.head-cell>
                            <x-tables.head-cell>Tipo Operação</x-tables.head-cell>
                            <x-tables.head-cell>Step</x-tables.head-cell>
                        </x-tables.header>
                        <x-tables.body>
                            @forelse($premioPagamento as $registro)
                            <x-tables.row>

                                <x-tables.cell>{{ $registro->placa }}</x-tables.cell>
                                <x-tables.cell>{{ $registro->distancia }}</x-tables.cell>
                                <x-tables.cell>{{ $registro->media }}</x-tables.cell>
                                <x-tables.cell>R$ {{ number_format($registro->valor_premio, 2, ',', '.') }}
                                </x-tables.cell>
                                <x-tables.cell>R$ {{ number_format($registro->excedente, 2, ',', '.') }}</x-tables.cell>
                                <x-tables.cell>{{ $registro->subcategoria }}</x-tables.cell>
                                <x-tables.cell>{{ $registro->tipo_operacao }}</x-tables.cell>
                                <x-tables.cell>{{ $registro->step ?? '' }}</x-tables.cell>
                            </x-tables.row>
                            @empty
                            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
                            @endforelse
                        </x-tables.body>
                    </x-tables.table>
                </div>
            </div>

            <!-- Placas RV -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Placas RV</h2>
                <div class="results-table overflow-x-auto">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Placa</x-tables.head-cell>
                            <x-tables.head-cell>Subcategoria</x-tables.head-cell>
                            <x-tables.head-cell>Operação</x-tables.head-cell>
                            <x-tables.head-cell>KM Rodado</x-tables.head-cell>
                            <x-tables.head-cell>Média</x-tables.head-cell>
                            <x-tables.head-cell>Cód. RV</x-tables.head-cell>
                            <x-tables.head-cell>Tipo</x-tables.head-cell>
                            <x-tables.head-cell>Franquia</x-tables.head-cell>
                        </x-tables.header>
                        <x-tables.body>
                            @forelse($premioUniao as $uniao)
                            <x-tables.row>
                                <x-tables.cell>{{ $uniao->placa }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->subcategoria }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->id_tipo_operacao }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->km_rodado }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->media }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->n_rv }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->tipo_operacao }}</x-tables.cell>
                                <x-tables.cell>{{ $uniao->franquia_dados }}</x-tables.cell>
                            </x-tables.row>
                            @empty
                            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                            @endforelse
                        </x-tables.body>
                    </x-tables.table>
                </div>
            </div>

            <!-- Placas Mensal -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Placas Mensal</h2>
                <div class="results-table overflow-x-auto">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Placa</x-tables.head-cell>
                            <x-tables.head-cell>Subcategoria</x-tables.head-cell>
                            <x-tables.head-cell>Operação</x-tables.head-cell>
                            <x-tables.head-cell>KM Rodado</x-tables.head-cell>
                            <x-tables.head-cell>Média</x-tables.head-cell>
                            <x-tables.head-cell>Tipo</x-tables.head-cell>
                            <x-tables.head-cell>Franquia</x-tables.head-cell>
                        </x-tables.header>
                        <x-tables.body>
                            @forelse($premioUniaoMensal as $uniaomensal)
                            <x-tables.row>
                                <x-tables.cell>{{ $uniaomensal->placa }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->subcategoria }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->id_tipo_operacao }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->km_rodado }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->media }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->tipo_operacao }}</x-tables.cell>
                                <x-tables.cell>{{ $uniaomensal->franquia_dados }}</x-tables.cell>
                            </x-tables.row>
                            @empty
                            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                            @endforelse
                        </x-tables.body>
                    </x-tables.table>
                </div>
            </div>

            <!-- Deflatores -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Deflatores</h2>
                <div class="results-table overflow-x-auto">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Placa</x-tables.head-cell>
                            <x-tables.head-cell>D + 1</x-tables.head-cell>
                            <x-tables.head-cell>Bafômetro</x-tables.head-cell>
                            <x-tables.head-cell>Celular</x-tables.head-cell>
                            <x-tables.head-cell>Cinto</x-tables.head-cell>
                            <x-tables.head-cell>Ex Velocidade</x-tables.head-cell>
                            <x-tables.head-cell>Vlr Desconto</x-tables.head-cell>
                        </x-tables.header>
                        <x-tables.body>
                            @forelse($premioDeflatores as $deflatores)
                            <x-tables.row>
                                <x-tables.cell>{{ $deflatores->placa ?? '' }}</x-tables.cell>
                                <x-tables.cell>{{ $deflatores->dmais1 }}</x-tables.cell>
                                <x-tables.cell>{{ $deflatores->bafometro }}</x-tables.cell>
                                <x-tables.cell>{{ $deflatores->celular ?? '' }}</x-tables.cell>
                                <x-tables.cell>{{ $deflatores->cinto }}</x-tables.cell>
                                <x-tables.cell>{{ $deflatores->exvelocidade }}</x-tables.cell>
                                <x-tables.cell>R$ {{ number_format($deflatores->totaldesconto, 2, ',', '.') }}
                                </x-tables.cell>
                            </x-tables.row>
                            @empty
                            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                            @endforelse
                        </x-tables.body>
                    </x-tables.table>
                </div>
            </div>

        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <a href="{{ route('admin.manutencaopremio.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Cancelar
            </a>

        </div>

    </div>
</div>

<!-- Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = {
        chartMotoristas: @json($distanciaRvData ?? array_fill(0, 6, 0)),
        chartDistancia: @json($distanciaMensalData ?? array_fill(0, 6, 0)),
        chartPremio: @json($valorPremioData ?? array_fill(0, 6, 0)),
    };

    const charts = [
        { id: 'chartMotoristas', color: '#10B981' }, // emerald
        { id: 'chartDistancia', color: '#06B6D4' },  // cyan
        { id: 'chartPremio', color: '#8B5CF6' }      // violet
    ];

    charts.forEach(({ id, color }) => {
        const ctx = document.getElementById(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({ length: 6 }, (_, i) => i + 1),
                datasets: [{
                    data: chartData[id],
                    borderColor: color,
                    borderWidth: 2,
                    fill: true,
                    backgroundColor: color + '20',
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    });
</script>