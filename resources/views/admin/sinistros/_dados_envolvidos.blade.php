<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

    <div class="col-span-2">
        <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
        <input name="nome" {{ $bloquear ? 'disabled' : '' }}
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('nome', $sinistro->nome ?? '') }}">
    </div>

    <div class="col-span-2">
        <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
        <input name="telefone" {{ $bloquear ? 'disabled' : '' }}
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('telefone', $sinistro->telefone ?? '') }}">
    </div>

    <div class="col-span-2">
        <label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label>
        <input name="cpf" {{ $bloquear ? 'disabled' : '' }}
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('cpf', $sinistro->cpf ?? '') }}">
    </div>

    <div class="flex justify-center items-center">
        <button type="button" onclick="adicionarEnvolvido()" {{ $bloquear ? 'disabled' : '' }}
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 {{ $bloquear ? 'cursor-not-allowed' : '' }}">
            Adicionar Envolvido
        </button>
    </div>

    @php
        $actionIcons = [
            'icon:pencil | tip:Editar | click:editSinistroEnvolvido({id})',
            "icon:trash | tip:Excluir | color:red | click:destroySinistroEnvolvido({id}, '{id}')",
        ];

        $column_historico = [
            'data_inclusao' => 'Data Inclusão',
            'nome' => 'Nome',
            'telefone' => 'Telefone',
            'cpf' => 'CPF',
        ];
    @endphp

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="envolvidos" id="envolvidos_json"
        value="{{ isset($dadosEnvolvidos) ? json_encode($dadosEnvolvidos) : '[]' }}">

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Inclusão
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telefone
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        CPF
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="tabelaEnvolvidoBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>
