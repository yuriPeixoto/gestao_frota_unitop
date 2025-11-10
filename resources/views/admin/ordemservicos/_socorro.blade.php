<div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
    <x-forms.smart-select name="idVeiculo_socorro" label="Veículo de Socorro" placeholder="Selecione o veiculo..."
        :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')" asyncSearch="false" :selected="old('idVeiculo_socorro', $ordemServico->idVeiculo_socorro ?? '')" />

    <x-forms.smart-select name="id_socorrista" label="Socorrista" placeholder="Selecione o socorrista..." :options="$motoristasFrequentes"
        asyncSearch="false" :searchUrl="route('admin.api.pessoal.search')" :selected="old('id_socorrista', $ordemServico->id_socorrista ?? '')" />

    <x-forms.smart-select name="id_municipio" label="Local do Socorro" placeholder="Selecione o município..."
        :options="$formOptions['municipio']" :searchUrl="route('admin.api.municipio.search')" asyncSearch="true" :selected="old('id_municipio', $ordemServico->id_municipio ?? '')" />
</div>

<div class="flex justify-left mt-4">
    <button type="button" onclick="adicionarSocorro()"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Adicionar
    </button>
</div>

<!-- Tabela de socorro -->
<div class="p-6 bg-white border-gray-200">
    <!-- Campo hidden para armazenar os socorristas -->
    <input type="hidden" name="tabelaSocorro" id="tabelaSocorro_json"
        value="{{ isset($tabelaSocorro) ? json_encode($tabelaSocorro) : '[]' }}">

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaSocorroBody">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Inclusão
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Alteração
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Veículo do Socorro
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Socorrista
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Local do Socorro
                    </th>
                </tr>
            </thead>
            <tbody id="tabelaSocorroBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>
