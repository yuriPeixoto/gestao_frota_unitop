<form method="GET" action="{{ route('admin.testefrios.index') }}" class="space-y-4"
    hx-get="{{ route('admin.testefrios.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input name="searchCodigo" label="Buscar codigo:" value=" {{ request('search') }}" />
        </div>

        <div>
            <x-forms.input name="searchCertificado" label="Buscar por nº certificado:"
                value="{{ request('search') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa:" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
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

        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Certificação (Inicial)"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Certificação (Final)"
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
    </div>

    <div class="flex justify-between space-x-2">
        <div>
            <a href="{{ route('admin.testefrios.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Buscar
            </button>
        </div>
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.testefrios" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
