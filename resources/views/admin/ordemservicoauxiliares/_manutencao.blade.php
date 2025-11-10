<div>
    <h1 class="text-2xl">Manutenções:</h1>
    <hr>
    <div class="grid grid-cols-1 md:grid-cols-1 gap-2 mt-10">
        <div>
            <label for="id_manutencao" class="block text-sm font-medium text-gray-700">Manutenção:</label>
            <select name='id_manutencao'
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($formOptions['manutencao'] as $manutencao)
                    <option value="{{ $manutencao['value'] }}"
                        {{ old('id_veiculo', $osAuxiliar->id_veiculo ?? '') == $manutencao['value'] ? 'selected' : '' }}>
                        {{ $manutencao['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="flex justify-left mt-4">
        <button type="button" onclick="adicionarOsManutencao()"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <x-icons.plus />
            Adicionar
        </button>
    </div>

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="osManutencao" id="osManutencao_json"
        value="{{ isset($dadosOsManutencao) ? json_encode($dadosOsManutencao) : '[]' }}">

    <!-- Tabela de Itens -->
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Ação</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Inclusão</th>
                        <th scope="col" class="py-3 px-6">Data/Hora Alteração</th>
                        <th scope="col" class="py-3 px-6">Manutenção</th>
                    </tr>
                </thead>
                <tbody id="tabelaOsManutencaoBody" class="bg-white divide-y divide-gray-200">
                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
