<h2 class="text-lg font-semibold text-gray-700 mb-4">Distância sem Login</h2>

{{-- Filtros --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 relative z-20">
    <div class="mb-4">
        <label for="buscaPlaca" class="block text-sm font-medium text-gray-700">Placa:</label>
        <input type="text" id="buscaPlaca" name="buscaPlaca"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="Digite a placa...">
    </div>

    <div>
        <x-forms.input type="date" name="data_inicial" id="dataInicial" label="Data Inicial:"
            value="{{ request('data_inicial') }}" />
    </div>

    <div>
        <x-forms.input type="date" name="data_final" id="dataFinal" label="Data Final:"
            value="{{ request('data_final') }}" />
    </div>
</div>

{{-- Tabela de resultados --}}
<div id="tabelaDistancias">
    @include('admin.manutencaopremio._table_distancias', ['listagem' => $listagem])
</div>

<div id="ModalKm" class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-md flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
        <h2 class="text-xl font-semibold mb-4">Vincular Distância sem Login</h2>
        <div id="conteudoModalKm" class="overflow-y-auto max-h-[70vh]">
            <p class="text-gray-500">Carregando...</p>
        </div>
        <button onclick="fecharModalKm()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
            Fechar
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar event listener a todos os botões do modal
        document.querySelectorAll('.modal-km-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                abrirModalKm(id);
            });
        });

        // Configurar pesquisa dinâmica
        const inputPlaca = document.getElementById('buscaPlaca');
        const dataInicial = document.getElementById('dataInicial');
        const dataFinal = document.getElementById('dataFinal');

        function realizarPesquisa() {
            const placa = inputPlaca ? inputPlaca.value.trim() : '';
            const dataInicialVal = dataInicial ? dataInicial.value : '';
            const dataFinalVal = dataFinal ? dataFinal.value : '';

            const params = new URLSearchParams();
            
            if (placa) params.append('placa', placa);
            if (dataInicialVal) params.append('data_inicial', dataInicialVal);
            if (dataFinalVal) params.append('data_final', dataFinalVal);

            fetch(`/admin/manutencaopremio/modalDistancia?${params.toString()}`, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro na requisição');
                return response.text();
            })
            .then(html => {
                document.getElementById('tabelaDistancias').innerHTML = html;
                // Reatachar event listeners aos novos botões do modal
                reatacharEventListeners();
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('tabelaDistancias').innerHTML = 
                    '<p class="text-red-500 p-4">Erro ao carregar os dados.</p>';
            });
        }

        function reatacharEventListeners() {
            document.querySelectorAll('.modal-km-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    abrirModalKm(id);
                });
            });
        }

        // Event listeners para os filtros
        let timeout;
        if (inputPlaca) {
            inputPlaca.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(realizarPesquisa, 400);
            });
        }

        if (dataInicial) {
            dataInicial.addEventListener('change', realizarPesquisa);
        }

        if (dataFinal) {
            dataFinal.addEventListener('change', realizarPesquisa);
        }
    });

    // Funções do modal
    window.abrirModalKm = function(id) {
        const modal = document.getElementById('ModalKm');
        const conteudo = document.getElementById('conteudoModalKm');

        modal.classList.remove('hidden');
        conteudo.innerHTML = '<p class="text-gray-500 p-4">Carregando...</p>';

        fetch(`/admin/manutencaopremio/modalKm/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erro ao carregar dados');
            return response.text();
        })
        .then(html => {
            conteudo.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro:', error);
            conteudo.innerHTML = '<p class="text-red-500 p-4">Erro ao carregar os dados.</p>';
        });
    }

    window.fecharModalKm = function() {
        const modal = document.getElementById('ModalKm');
        modal.classList.add('hidden');
    };
</script>