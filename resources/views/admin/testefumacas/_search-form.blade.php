<form method="GET" action="{{ route('admin.testefumacas.index') }}" class="space-y-4"
    hx-get="{{ route('admin.testefumacas.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecione o veículo..." :options="$veiculosFrequentes"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <label for="filial" class="block text-sm font-medium text-gray-700 mb-1">Filial do Veiculo:</label>
            <select name="filial" id="filial"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas as Filiais</option>
                @foreach ($filiais as $filial)
                    <option value="{{ $filial['value'] }}"
                        {{ request('filial') == $filial['value'] ? 'selected' : '' }}>
                        {{ $filial['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <x-forms.input name="resultado" label="Resultado" value="{{ request('resultado') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Realização (Inicial)"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Realização (Final)"
                value="{{ request('data_final') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="vencimento_inicial" label="Data Vencimento (Inicial)"
                value="{{ request('vencimento_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="vencimento_final" label="Data Vencimento (Final)"
                value="{{ request('vencimento_final') }}" />
        </div>

        <div>
            <x-forms.smart-select name="situacao" label="Situação:" :options="[
                ['value' => 'A Vencer', 'label' => 'A Vencer'],
                ['value' => 'Vencido', 'label' => 'Vencido'],
                ['value' => 'Cancelado', 'label' => 'Cancelado'],
            ]" :selected="request('situacao')" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Status" placeholder="Selecione..." :options="[['value' => 'ativo', 'label' => 'Ativo'], ['value' => 'inativo', 'label' => 'Inativo']]"
                :selected="request('status')" />
        </div>
    </div>

    <div class="flex justify-between space-x-2">
        <div>
            <a href="{{ route('admin.testefumacas.index') }}"
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
            <x-ui.export-buttons route="admin.testefumacas" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
