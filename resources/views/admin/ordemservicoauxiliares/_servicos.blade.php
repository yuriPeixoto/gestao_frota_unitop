<div>
    <h1 class="text-2xl">Serviços:</h1>
    <hr>
    <div class="grid grid-cols-1 md:grid-cols-1 gap-2 mt-10">
        <div>
            <x-forms.smart-select name="id_servico" label="Serviço" placeholder="Selecione o Serviço..." :options="$formOptions['servico']"
                :searchUrl="route('admin.api.servicos.search')" :selected="old('id_servico', $osAuxiliar->id_servico ?? '')" />
        </div>
        {{-- <div>
            <x-forms.smart-select name="id_mecanico" label="Mecânico" placeholder="Selecione o Mecânico..."
                :options="$formOptions['usuarios']" :selected="old('id_mecanico', $osAuxiliar->id_mecanico ?? '')" />
        </div> --}}
    </div>
    <div class="flex justify-left mt-4">
        <button type="button" onclick="adicionarOsServicos()"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <x-icons.plus />
            Adicionar
        </button>
    </div>

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="osServicos" id="osServicos_json"
        value="{{ isset($dadosOsServicos) ? json_encode($dadosOsServicos) : '[]' }}">

    <!-- Tabela de Itens -->
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Ação</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Inclusão</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Alteração</th>
                        <th scope="col" class="py-3 px-6">Serviço</th>
                    </tr>
                </thead>
                <tbody id="tabelaOSServicosBody" class="bg-white divide-y divide-gray-200">
                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
