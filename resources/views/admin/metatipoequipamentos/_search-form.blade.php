<form method="GET" action="{{ route('admin.metatipoequipamentos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.metatipoequipamentos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="id_meta" label="Código Meta" value="{{ request('id_meta') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Inicial"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Final"
                value="{{ request('data_final') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input type="number" name="vlr_meta" label="Valor Meta" step="0.01"
                value="{{ request('vlr_meta') }}" />
        </div>

        <div>
            <x-forms.smart-select
                name="id_filial"
                label="Filial"
                placeholder="Selecione a filial..."
                :options="$filiais ?? []"
                :selected="request('id_filial')"
                asyncSearch="false"
            />
        </div>

        <div>
            <x-forms.smart-select
                name="id_equipamento"
                label="Tipo Equipamento"
                placeholder="Selecione o tipo de equipamento..."
                :options="$tiposEquipamento ?? []"
                :selected="request('id_equipamento')"
                asyncSearch="false"
            />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Botões de exportação (se necessário) --}}
            <x-ui.export-buttons 
                route="admin.metatipoequipamentos"
                :formats="['pdf', 'csv', 'xls', 'xml']"
            />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.metatipoequipamentos.index') }}"
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