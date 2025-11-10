<form method="GET" action="{{ route('admin.transfimobilizadoveiculo.index') }}" class="space-y-4"
    hx-get="{{ route('admin.transfimobilizadoveiculo.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            {{-- Cod. Descarte Imobilizados --}}
            <x-forms.input name="id_transferencia_imobilizado_veiculo" label="Código Transferência Imobilizado"
                value="{{ request('id_transferencia_imobilizado_veiculo') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_equipamento" label="Tipo Veículo" placeholder="Selecione o tipo..."
                :options="$id_tipo_equipamento" :selected="request('id_tipo_equipamento')" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Situação" placeholder="Selecione a situação..."
                :options="$situacao" :selected="request('status')" />
        </div>

        <div>
            <x-forms.input name="data_inclusao" type="date" label="Data Inclusão" value="{{ request('data_inclusao') }}" />
        </div>
        
        <div>
            <x-forms.input name="data_inicio" type="date" label="Data Inclusão" value="{{ request('data_inicio') }}" />
        </div>

        <div>
            <x-forms.input name="data_fim" type="date" label="Data Inclusão" value="{{ request('data_fim') }}" />
        </div>
    </div>


    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.transfimobilizadoveiculo.index') }}"
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