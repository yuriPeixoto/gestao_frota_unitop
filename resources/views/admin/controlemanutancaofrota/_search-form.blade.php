<form method="GET" action="{{ route('admin.controlemanutancaofrota.index') }}" class="space-y-4"
    hx-get="{{ route('admin.controlemanutancaofrota.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="os" label="Os" clearable="true" value="{{ request('os') }}" />
        </div>

        <div>
            <x-forms.smart-select name="placa" label="Placa:" placeholder="Selecionar" :options="$placa"
                value="{{ request('placa') }}" :searchUrl="route('admin.api.veiculo.search')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="modeloveiculo" label="Modelo" value="{{ request('modeloveiculo') }}"
                :options="$modelo" :searchUrl="route('admin.api.modeloveiculo.search')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.input type="date" name="dataentrada" label="Data Entrada" value="{{ request('dataentrada') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="dataprevisaosaida" label="Previsão Saída"
                value="{{ request('dataprevisaosaida') }}" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div>
            <x-forms.input type="date" name="data_encerramento" label="Data Encerramento"
                value="{{ request('data_encerramento') }}" />
        </div>

        <div>
            <x-forms.smart-select name="tipoos" label="Tipo de O.S" value="{{ request('tipoos') }}" :options="$os" />
        </div>

        <div>
            <x-forms.smart-select name="statusordem" label="Status" value="{{ request('statusordem') }}"
                :options="$status" />
        </div>

        <div>
            <x-forms.input name="localmanutancao" label="Local" value="{{ request('localmanutancao') }}" />
        </div>

        <div>
            <x-forms.smart-select name="filial" label="Filial" value="{{ request('filial') }}" :options="$filial" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.controlemanutancaofrota.index') }}"
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
            <x-ui.export-buttons route="admin.controlemanutancaofrota" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>