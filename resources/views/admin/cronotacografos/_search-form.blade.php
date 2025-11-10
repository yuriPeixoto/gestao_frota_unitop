<form method="GET" action="{{ route('admin.cronotacografos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.cronotacografos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <div><br></div>
            <x-forms.input name="id_certificado_veiculo" label="Código"
                value="{{ request('id_certificado_veiculo') }}" />
        </div>

        <div>
            <div><br></div>
            <x-forms.smart-select name="id_veiculo" label="Veículo" placeholder="Selecione o veículo..."
                :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')"
                asyncSearch="true" />
        </div>

        <div>

            <div class="flex gap-2">
                <x-forms.input type="date" name="data_vencimento_inicio" label="Data Inicial"
                    value="{{ request('data_vencimento_inicio') }}" />
                <x-forms.input type="date" name="data_vencimento_final" label="Data Final"
                    value="{{ request('data_vencimento_final') }}" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="numero_certificado" label="Número do Certificado"
                value="{{ request('numero_certificado') }}" />
        </div>

        <div>
            <x-forms.smart-select name="situacao" label="Situação:" :options="[
                ['value' => 'A Vencer', 'label' => 'A Vencer'],
                ['value' => 'Vencido', 'label' => 'Vencido'],
                ['value' => 'Cancelado', 'label' => 'Cancelado'],
            ]" :selected="request('situacao')" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Status" placeholder="Selecione..."
                :options="[['value' => '1', 'label' => 'Ativo'], ['value' => '0', 'label' => 'Inativo']]"
                :selected="request('status')" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.cronotacografos.index') }}"
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
            <x-ui.export-buttons route="admin.cronotacografos" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>