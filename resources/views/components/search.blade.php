@props(['table', 'field', 'route', 'columns' => null])

<div x-data="searchComponent('{{ $table }}', '{{ $field }}', '{{ $route }}', {{ json_encode($columns ?? []) }})"
     class="w-full flex items-center space-x-2">
    <div class="flex-grow relative">
        <input
            type="search"
            x-model="query"
            placeholder="Buscar..."
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
        >
        <div x-show="error"
             class="absolute text-red-500 text-sm mt-1"
             x-text="error">
        </div>
    </div>

    <button
        @click="search()"
        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-300 flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span>Pesquisar</span>
    </button>

    <ul x-show="results.length > 0">
        <template x-for="result in results" :key="result.id">
            <li x-text="result.{{ $field }}"></li>
        </template>
    </ul>
</div>

<script>
    function searchComponent(table, field, route, columns = []) {
        return {
            query: '',
            results: [],
            error: null,
            table: table,
            field: field,
            route: route,
            columns: columns,

            async search() {
                this.error = null;
                this.results = [];

                try {
                    const params = new URLSearchParams({
                        table: this.table,
                        field: this.field,
                        query: this.query
                    });

                    const url = `${this.route}?${params.toString()}`;

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    // this.results = data;
                    Alpine.dispatch('search-results', {
                        results: data,
                        columns: this.columns
                    });

                    console.log('Dados recebidos:', data);
                } catch (error) {
                    console.error('Search error:', error);
                    this.error = 'Erro ao realizar a busca. Tente novamente.';
                }
            }
        }
    }
</script>
