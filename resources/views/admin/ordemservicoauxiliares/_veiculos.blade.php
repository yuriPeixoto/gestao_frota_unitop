<div>
    <h1 class="text-2xl">Veículos:</h1>
    <hr>
    <div class="grid grid-cols-3 md:grid-cols-3 gap-2 mt-10">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Veiculo" placeholder="Selecione o Veículo..."
                :options="$formOptions['veiculos']"
                :selected="old('id_veiculo', $ordemservicoauxiliares->id_veiculo ?? '')" />
        </div>
        <div>
            <label for="km_atual" class="block text-sm font-medium text-gray-700">
                KM Atual:
            </label>
            <input type="text" name="km_atual" id="km_atual" {{ !isset($ordemservicoauxiliares)
                ? 'onblur="validaKMAtual()"' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('km_atual', $ordemservicoauxiliares->km_atual ?? '') }}" />
        </div>
        <div class="flex items-center mt-6">
            <button type="button" name="historico_km" id="historico_km" onclick="onimprimirhistorico()"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <div class="flex items-center">
                    <x-icons.magnifying-glass />
                    Histórico de KM
                </div>
            </button>
        </div>
        <div id="loader-overlay" style="display:none;">
            <div class="loader"></div>
        </div>
    </div>
    <div class="flex justify-left mt-4">
        <button type="button" onclick="adicionarOsVeiculos()" class="inline-flex items-center px-4 py-2
            border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600
            hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <x-icons.plus />
            Adicionar
        </button>
    </div>

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="osVeiculos" id="osVeiculos_json"
        value="{{ isset($dadosOsVeiculos) ? json_encode($dadosOsVeiculos) : '[]' }}">

    <!-- Tabela de Itens -->
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Ação</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Inclusão</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Alteração</th>
                        <th scope="col" class="py-3 px-6">Veículo</th>
                        <th scope="col" class="py-3 px-6">KM</th>
                    </tr>
                </thead>
                <tbody id="tabelaOSVeiculosBody" class="bg-white divide-y divide-gray-200">
                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Iniciando SmartSelect listener para veículos...');

        const hiddenInput = document.querySelector('input[name="id_veiculo"]') || document.getElementById('id_veiculo');
        const inputKmAtual = document.getElementById('km_atual');

        if (!hiddenInput) {
            console.warn('Campo hidden do SmartSelect [name="id_veiculo"] não encontrado.');
            return;
        }
        if (!inputKmAtual) {
            console.warn('Campo #km_atual não encontrado.');
            return;
        }

        // Função que busca o último KM e preenche o campo
        async function carregarKmVeiculo(id) {
            if (!id) {
                inputKmAtual.value = '';
                return;
            }

            try {
                const res = await fetch(`/admin/ordemservicoauxiliares/veiculos/${id}/ultimo-km`);

                if (!res.ok) {
                    console.error('Erro na requisição (status):', res.status);
                    return;
                }

                const data = await res.json();
                if (data.success) {
                    inputKmAtual.value = data.km ?? '';
                    console.log('Último KM carregado:', data.km);
                } else {
                    console.warn('Resposta sem sucesso ao buscar KM:', data.message);
                }
            } catch (err) {
                console.error('Erro ao buscar último KM:', err);
            }
        }

        if (hiddenInput.value) {
            console.log('Valor inicial detectado:', hiddenInput.value);
            carregarKmVeiculo(hiddenInput.value);
        }

        const observer = new MutationObserver(() => {
            const novoValor = hiddenInput.value;
            if (novoValor) {
                console.log(' Veículo alterado via SmartSelect:', novoValor);
                carregarKmVeiculo(novoValor);
            }
        });
        observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] });

        const visibleControl = document.querySelector('[name="id_veiculo"]');
        if (visibleControl) {
            visibleControl.addEventListener('change', e => {
                const val = e.target.value;
                if (val) {
                    console.log(' Veículo alterado via evento change:', val);
                    carregarKmVeiculo(val);
                }
            });
        }

        window.addEventListener('beforeunload', () => observer.disconnect());
    });

</script>