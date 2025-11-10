<form method="GET" action="{{ route('admin.veiculos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.veiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
            <x-forms.smart-select name="placa" label="Placa" placeholder="Selecione o veículo..."
                :options="$formOptions['placa']" :searchUrl="route('admin.veiculos.search')"
                :selected="request('placa')" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione o filial..."
                :options="$formOptions['filiais']" :selected="request('id_filial')" />
        </div>

        <div>
            <x-forms.smart-select name="base_veiculo" label="Base Veículo" placeholder="Selecione o base veiculo..."
                :options="$formOptions['bases']" :selected="request('base_veiculo')" />
        </div>

        <div>
            <x-forms.smart-select name="id_categoria" label="Cód. Categoria" placeholder="Selecione o categoria..."
                :options="$formOptions['categorias']" :selected="request('id_categoria')" />
        </div>

        <div>
            <x-forms.smart-select name="renavam" label="Renavam" placeholder="Selecione o renavam..."
                :options="$formOptions['renavams']" :selected="request('renavam')" />
        </div>

        <div>
            <x-forms.smart-select name="marca_veiculo" label="Marca Veículo" placeholder="Selecione marca veículo..."
                :options="$formOptions['modelos']" :selected="request('marca_veiculo')" />
        </div>

        <div>
            <x-forms.input name="data_compra" type="date" label="Data Compra" value="{{ request('data_compra') }}" />
        </div>

        <div>
            <x-forms.smart-select name="is_terceiro" label="Veículo de Terceiro" placeholder="Selecione ..."
                :options="$formOptions['veiculos_terceiro']" :selected="request('is_terceiro')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="situacao_veiculo" label="Situação"
                :options="[['value' => 'true', 'label' => 'Ativo'], ['value' => 'false', 'label' => 'Inativo']]"
                :selected="request('situacao_veiculo') ?? ''" />
        </div>
    </div>

    <div class="mt-4 flex justify-between">
        <div>
            <x-ui.export-buttons route="admin.veiculos" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.veiculos.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.trash class="mr-2 h-4 w-4" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icons.magnifying-glass class="mr-2 h-4 w-4" />
                Buscar
            </button>
        </div>
    </div>
</form>