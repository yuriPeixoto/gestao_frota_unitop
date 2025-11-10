<form method="GET" action="{{ route('admin.manutencaopreordemservicofinalizada.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaopreordemservicofinalizada.index') }}" hx-target="#results-table"
    hx-select="#results-table" hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-bladewind::input name="id_pre_os" label="Cód. Pre-OS" clearable="true" value="{{ request('os') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="" placeholder="Selecione a placa..."
                :options="$referenceDatas['veiculosFrequentes']" :searchUrl="route('admin.api.veiculos.search')"
                :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="idobtermotorista" label="" placeholder="Selecione o motorista..."
                :options="$referenceDatas['motoristas']" :searchUrl="route('admin.api.motoristas.search')"
                :selected="request('idobtermotorista')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipostatus_pre_os" label="" placeholder="Selecione o status..."
                :options="$referenceDatas['statusPreOs']" asyncSearch="false" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <x-forms.smart-select name="id" label="" placeholder="Selecione o recepcinista..."
                :options="$referenceDatas['recepcinista']" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="" placeholder="Selecione a filial..."
                :options="$referenceDatas['filiais']" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_departamento" label="" placeholder="Selecione o departamento..."
                :options="$referenceDatas['departamentos']" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_grupo_resolvedor" label="" placeholder="Selecione o grupo resolvedor..."
                :options="$referenceDatas['grupoResolvedor']" asyncSearch="false" />
        </div>

    </div>

    <div class="flex justify-between mt-4">

        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.manutencaopreordemservicofinalizada"
                :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaopreordemservicofinalizada.index') }}"
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