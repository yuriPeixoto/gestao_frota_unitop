<form method="GET" action="{{ route('admin.seguroobrigatorio.index') }}" class="space-y-4"
    hx-get="{{ route('admin.seguroobrigatorio.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_seguro_obrigatorio_veiculo" label="Cód. Seguro"
                value="{{ request('id_seguro_obrigatorio_veiculo') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <label for="filial_veiculo" class="block text-sm font-medium text-gray-700 mb-1">Filial do Veiculo</label>
            <select name="filial_veiculo" id="filial_veiculo"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione a filial...</option>
                @foreach ($filiais as $filial)
                    <option value="{{ $filial['value'] }}">{{ $filial['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-forms.input name="numero_bilhete" label="Número do Bilhete" value="{{ request('numero_bilhete') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="ano_validade" label="Ano de Validade" type="number" min="2000" max="2099"
                value="{{ request('ano_validade') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_vencimento_inicio" label="Vencimento (Início)"
                value="{{ request('data_vencimento_inicio') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_vencimento_fim" label="Vencimento (Fim)"
                value="{{ request('data_vencimento_fim') }}" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Ativo/Inativo" placeholder="Selecione..." :options="[['value' => 'ativo', 'label' => 'Ativo'], ['value' => 'inativo', 'label' => 'Inativo']]"
                :selected="request('status')" />
        </div>
    </div>

    <div class="flex justify-between space-x-2 mt-4">
        <div>
            <a href="{{ route('admin.seguroobrigatorio.index') }}"
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

        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.seguroobrigatorio" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
