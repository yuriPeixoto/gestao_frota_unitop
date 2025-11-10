<form method="GET" action="{{ route('admin.recebimentocombustiveis.index') }}" class="space-y-4"
    hx-get="{{ route('admin.recebimentocombustiveis.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <x-forms.input name="id_recebimento_combustivel" label="Cód. Recebimento"
                value="{{ request('id_recebimento_combustivel') }}" />
        </div>

        <div>
            <x-forms.input name="numeronotafiscal" label="N° NF" value="{{ request('numeronotafiscal') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nf2" label="N° NF2" value="{{ request('numero_nf2') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nf3" label="N° NF3" value="{{ request('numero_nf3') }}" />
        </div>

        <div>
            <x-forms.input name="numero_nf4" label="N° NF4" value="{{ request('numero_nf4') }}" />
        </div>
        <div>
            <x-forms.smart-select name="id_tipo_combustivel" label="Tipo Combustível" placeholder="Selecione o tipo..."
                :options="$tiposCombustivel" :selected="request('id_tipo_combustivel')" asyncSearch="false" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_alteracao" label="Data Alteração"
                value="{{ request('data_alteracao') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_tanque" label="Tanque" placeholder="Selecione o tanque..." :options="$tanques"
                :selected="request('id_tanque')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
                :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')" asyncSearch="true" />
        </div>
        <div>
            <x-forms.input type="date" name="data_entrada" label="Data de Entrada"
                value="{{ request('data_entrada') }}" />
        </div>

        <div>
            <x-forms.input name="quantidade" label="Quantidade" value="{{ request('quantidade') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." :options="$filiais"
                :selected="request('id_filial')" asyncSearch="false" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Situação</label>
            <select name="situacao_nf"
                class="w-full border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="" {{ request('situacao_nf') == '' ? 'selected' : '' }}>Todas as situações</option>
                <option value="FINALIZADO" {{ request('situacao_nf') == 'FINALIZADO' ? 'selected' : '' }}>FINALIZADO
                </option>
                <option value="AGUARDANDO LANÇAMENTO DE NF"
                    {{ request('situacao_nf') == 'AGUARDANDO LANÇAMENTO DE NF' ? 'selected' : '' }}>
                    AGUARDANDO LANÇAMENTO DE NF</option>
                <option value="null" {{ request('situacao_nf') == 'null' ? 'selected' : '' }}>Sem situação definida
                </option>
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.recebimentocombustiveis.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>
