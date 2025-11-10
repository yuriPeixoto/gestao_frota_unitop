<form method="GET" action="{{ route('admin.abastecimentosatstruckpagmanual.index') }}" class="space-y-4"
    hx-get="{{ route('admin.abastecimentosatstruckpagmanual.index') }}" hx-target="#results-table"
    hx-select="#results-table" hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id" label="Cód. de Abastecimento" value="{{ request('id') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicio" label="Data Inicial"
                value="{{ request('data_inicio') ?? now()->format('Y-m-d') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Final"
                value="{{ request('data_final') ?? now()->format('Y-m-d') }}" />
        </div>

        <div>
            <x-forms.smart-select name="placa" label="Veículo" placeholder="Selecione o veículo..." :options="$veiculos"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('placa')" asyncSearch="true" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.smart-select name="id_tipo_combustivel" label="Combustível"
                placeholder="Selecione o combustível..." :options="$tiposCombustivel"
                :selected="request('id_tipo_combustivel')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_categoria" label="Categoria" placeholder="Selecione a categoria..."
                :options="$categorias" :selected="request('id_categoria')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_equipamento" label="Tipo Equipamento" placeholder="Selecione o tipo..."
                :options="$tiposEquipamento" :selected="request('id_tipo_equipamento')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                :options="$filiais" :selected="request('id_filial')" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.abastecimentosatstruckpagmanual"
                :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.abastecimentosatstruckpagmanual.index') }}"
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