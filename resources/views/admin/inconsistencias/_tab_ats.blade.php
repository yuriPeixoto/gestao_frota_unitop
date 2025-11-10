<div class="space-y-6">
    <!-- Formulário de busca -->
    <form id="ats-search-form" action="{{ route('admin.inconsistencias.ats.search') }}" method="POST"
        class="bg-white p-4 rounded-md shadow-sm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-input-label for="data_inicio" value="Data Início" />
                <x-text-input id="data_inicio" type="date" name="data_inicio"
                    :value="request('data_inicio', date('Y-m-d', strtotime('-7 days')))" class="mt-1 block w-full"
                    required />
            </div>

            <div>
                <x-input-label for="data_fim" value="Data Fim" />
                <x-text-input id="data_fim" type="date" name="data_fim" :value="request('data_fim', date('Y-m-d'))"
                    class="mt-1 block w-full" required />
            </div>

            <div>
                <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                    :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')"
                    :selected="request('id_veiculo')" asyncSearch="true" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <x-input-label for="descricao_bomba" value="Bomba" />
                <select id="descricao_bomba" name="descricao_bomba"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($bombas as $bomba)
                    <option value="{{ $bomba['value'] }}">{{ $bomba['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="id_filial" value="Filial" />
                <select id="id_filial" name="id_filial"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($filiais as $filial)
                    <option value="{{ $filial['value'] }}">{{ $filial['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="id_departamento" value="Departamento" />
                <select id="id_departamento" name="id_departamento"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento['value'] }}">{{ $departamento['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <x-input-label for="tipo_combustivel" value="Tipo de Combustível" />
                <select id="tipo_combustivel" name="tipo_combustivel"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($tiposCombustivel as $tipo)
                    <option value="{{ $tipo['value'] }}">{{ $tipo['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-4 space-x-2">
            <a href="{{ route('admin.inconsistencias.index') }}?tab=ats"
                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                        clip-rule="evenodd" />
                </svg>
                Limpar Filtros
            </a>

            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                        clip-rule="evenodd" />
                </svg>
                Buscar
            </button>
        </div>
    </form>

    <!-- Resultados -->
    <!-- Loading + resultados -->
    <div id="ats-results" class="mt-6 bg-white rounded-md shadow-sm p-4">
        <div id="ats-loading" class="text-center py-8 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-gray-500">Aguarde enquanto buscamos as inconsistências...</p>
        </div>

        <div id="ats-table-container">
            <!-- tabela AJAX será carregada aqui -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ats-search-form');
        const resultsContainer = document.getElementById('ats-table-container');
        const loadingIndicator = document.getElementById('ats-loading');

        // Inicializa com busca padrão
        setTimeout(() => form.dispatchEvent(new Event('submit')), 500);

        // === Função AJAX reutilizável ===
        function fetchResults(url = null) {
            loadingIndicator.classList.remove('hidden');
            resultsContainer.classList.add('hidden');

            // Monta os dados do formulário
            const formData = new URLSearchParams(new FormData(form));

            let fetchUrl = url || form.action;
            let method = 'POST';

            // Se o link contiver "?page=", usa GET para paginação
            if (fetchUrl.includes('?page=')) {
                method = 'GET';
            }

            fetch(fetchUrl, {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': method === 'POST'
                        ? 'application/x-www-form-urlencoded'
                        : undefined,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: method === 'POST' ? formData : undefined
            })
            .then(response => response.text())
            .then(html => {
                loadingIndicator.classList.add('hidden');
                resultsContainer.innerHTML = html;
                resultsContainer.classList.remove('hidden');
                attachPaginationHandlers();
            })
            .catch(error => {
                console.error('Erro ao buscar inconsistências:', error);
                loadingIndicator.classList.add('hidden');
                resultsContainer.innerHTML =
                    '<div class="text-center py-4 text-red-500">Erro ao buscar inconsistências. Tente novamente.</div>';
                resultsContainer.classList.remove('hidden');
            });
        }

        // === Intercepta o submit ===
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchResults();
        });

        // === Função que intercepta os cliques nas paginações ===
        function attachPaginationHandlers() {
            const paginationLinks = resultsContainer.querySelectorAll('.pagination a');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Captura a URL da próxima página (page=2, page=3, etc)
                    const url = this.getAttribute('href');

                    // Atualiza via AJAX
                    fetchResults(url);
                });
            });
        }

        // Inicializa a primeira vez (caso já venha conteúdo com paginação)
        attachPaginationHandlers();
    });
</script>
@endpush