<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            📊 Relatório de Estoque por Produto
        </h1>
        <span class="text-sm text-gray-500 mt-2 sm:mt-0">
            Atualizado em {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>

    {{-- Seleção do produto --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm mb-6 flex flex-col gap-4">
        <x-forms.smart-select name="id_produto" id="id_produto" label="Selecione o Produto"
            placeholder="Escolha um produto..." :options="$produtos" :searchUrl="route('admin.api.produtos.search')"
            asyncSearch="false" />

        <div class="flex justify-end">
            <button type="button" id="btnLimparFiltro"
                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">
                {{-- Ícone de "limpar" (X dentro de círculo) --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Limpar Filtro
            </button>
        </div>
    </div>

    {{-- Conteúdo dinâmico --}}
    <div id="dadosProduto" class="hidden">
        <!-- Indicadores principais -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
                <div class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:shadow transition">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <i data-lucide="boxes" class="w-6 h-6 text-green-600"></i>
                        <span class="font-semibold">Estoque Atual</span>
                    </div>
                    <h3 id="estoque" class="text-2xl font-bold text-green-600">--</h3>
                </div>

                <div class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:shadow transition">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-500"></i>
                        <span class="font-semibold">Quantiade Estoque Semanal</span>
                    </div>
                    <h3 id="estoque_minimo" class="text-2xl font-bold text-red-600">--</h3>
                </div>

                <div class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:shadow transition">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <i data-lucide="arrows-up-down" class="w-6 h-6 text-blue-600"></i>
                        <span class="font-semibold">Diferença para atingir a quantidade semanal</span>
                    </div>
                    <h3 id="diferenca" class="text-2xl font-bold">--</h3>
                </div>
                <!-- Indicadores complementares -->
                <div class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:shadow transition">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <i data-lucide="activity" class="w-6 h-6 text-indigo-600"></i>
                        <span class="font-semibold">Consumo Médio Diário</span>
                    </div>
                    <h3 id="consumo_medio_diario" class="text-2xl font-bold text-indigo-600">--</h3>
                </div>

                <div class="flex flex-col items-center p-4 border border-gray-100 rounded-xl hover:shadow transition">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <i data-lucide="hourglass" class="w-6 h-6 text-amber-600"></i>
                        <span class="font-semibold">Dias de Duração Estimada</span>
                    </div>
                    <h3 id="dias_duracao" class="text-2xl font-bold text-amber-600">--</h3>
                </div>

            </div>
        </div>

        <!-- Área de carregamento -->
        <div id="loading" class="text-center py-10 text-gray-500 hidden">
            <svg class="animate-spin h-6 w-6 inline mr-2 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Carregando dados...
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-teal-600"></i>
                        Consumo Mensal
                    </h6>
                </div>
                <canvas id="graficoMensal" height="160"></canvas>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-5 h-5 text-rose-600"></i>
                        Consumo Semanal
                    </h6>
                </div>
                <canvas id="graficoSemanal" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- Mensagem inicial --}}
    <div id="mensagemInicial" class="text-center py-12 text-gray-400">
        <i data-lucide="search" class="w-8 h-8 mx-auto mb-3"></i>
        <p>Selecione um produto para visualizar os dados de estoque.</p>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons(); // Inicializa ícones

        let chartMensal = null;
        let chartSemanal = null;

        const hiddenInput = document.querySelector('input[name="id_produto"]');
        const dadosContainer = document.getElementById('dadosProduto');
        const mensagemInicial = document.getElementById('mensagemInicial');
        const loading = document.getElementById('loading');
        const btnLimparFiltro = document.getElementById('btnLimparFiltro');

        if (!hiddenInput) return;

        const observer = new MutationObserver(() => {
            const id = hiddenInput.value;
            if (id) carregarDadosProduto(id);
        });

        
        observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] });

        async function carregarDadosProduto(id) {
            if (!id) {
                resetarTela();
                return;
            }

            dadosContainer.classList.remove('hidden');
            mensagemInicial.classList.add('hidden');
            loading.classList.remove('hidden');

            try {
                const res = await fetch(`/admin/consultaprodutografico/estoque-grafico/dados/${id}`);
                if (!res.ok) throw new Error(`Erro ao buscar dados (${res.status})`);
                
                const data = await res.json();
                loading.classList.add('hidden');

                document.getElementById('estoque').textContent = data.estoque ?? 0;
                document.getElementById('estoque_minimo').textContent = data.estoque_minimo ?? 0;
                document.getElementById('consumo_medio_diario').textContent = data.consumo_diario ?? 0;
                document.getElementById('dias_duracao').textContent = data.dias_duracao ?? 0;
                
                const diferenca = (data.estoque ?? 0) - (data.estoque_minimo ?? 0);
                const diferencaElem = document.getElementById('diferenca');
                
                diferencaElem.textContent = diferenca;
                diferencaElem.classList.toggle('text-green-600', diferenca >= 0);
                diferencaElem.classList.toggle('text-red-600', diferenca < 0);

                // Gráfico Mensal
                const ctxMensal = document.getElementById('graficoMensal').getContext('2d');
                if (chartMensal) chartMensal.destroy();
                chartMensal = new Chart(ctxMensal, {
                    type: 'bar',
                    data: {
                        labels: data.consumo_mensal.labels ?? [],
                        datasets: [{
                            label: 'Consumo Mensal',
                            data: data.consumo_mensal.valores ?? [],
                            backgroundColor: '#14b8a6'
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { display: false } },
                        animation: { duration: 800, easing: 'easeOutQuart' }
                    }
                });

                // Gráfico Semanal
                const ctxSemanal = document.getElementById('graficoSemanal').getContext('2d');
                if (chartSemanal) chartSemanal.destroy();
                chartSemanal = new Chart(ctxSemanal, {
                    type: 'bar',
                    data: {
                        labels: data.consumo_semanal.labels ?? [],
                        datasets: [{
                            label: 'Consumo Semanal',
                            data: data.consumo_semanal.valores ?? [],
                            backgroundColor: '#f43f5e'
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true } },
                        plugins: { legend: { display: false } },
                        animation: { duration: 800, easing: 'easeOutQuart' }
                    }
                });

            } catch (error) {
                console.error('Erro ao carregar dados:', error);
                loading.classList.add('hidden');
            }
            console.log('Produto Selecionado:', hiddenInput.value);
        }

        function resetarTela() {
            if (chartMensal) chartMensal.destroy();
            if (chartSemanal) chartSemanal.destroy();

            document.getElementById('estoque').textContent = '--';
            document.getElementById('estoque_minimo').textContent = '--';
            document.getElementById('diferenca').textContent = '--';
            document.getElementById('consumo_medio_diario').textContent = '--';
            document.getElementById('dias_duracao').textContent = '--';

            dadosContainer.classList.add('hidden');
            mensagemInicial.classList.remove('hidden');

            hiddenInput.value = '';
            const select = document.querySelector('#id_produto');
            if (select && select.selectize) {
                select.selectize.clear(); 
            }
        }

        btnLimparFiltro.addEventListener('click', resetarTela);
    });
</script>