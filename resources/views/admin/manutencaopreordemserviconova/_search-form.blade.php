<form method="GET" action="{{ route('admin.manutencaopreordemserviconova.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaopreordemserviconova.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
            <x-bladewind::input name="id_pre_os" label="Cód. Pre-OS" clearable="true" value="{{ request('os') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="" placeholder="Selecione a placa..." :options="$referenceDatas['veiculosFrequentes']"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="idobtermotorista" label="" placeholder="Selecione o motorista..."
                :options="$referenceDatas['motoristas']" :searchUrl="route('admin.api.motoristas.search')" :selected="request('idobtermotorista')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipostatus_pre_os" label="" placeholder="Selecione o status..."
                :options="$referenceDatas['statusPreOs']" asyncSearch="false" />
        </div>

    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

        <div>
            <x-forms.smart-select name="id" label="" placeholder="Selecione o recepcinista..."
                :options="$referenceDatas['recepcinista']" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="" placeholder="Selecione a filial..." :options="$referenceDatas['filiais']"
                asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_departamento" label="" placeholder="Selecione o departamento..."
                :options="$referenceDatas['departamentos']" asyncSearch="false" />
        </div>

        <div>
            <x-forms.smart-select name="id_grupo_resolvedor" label=""
                placeholder="Selecione o grupo resolvedor..." :options="$referenceDatas['grupoResolvedor']" asyncSearch="false" />
        </div>

    </div>

    <div class="mt-4 flex justify-between">

        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaopreordemserviconova.index') }}"
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
