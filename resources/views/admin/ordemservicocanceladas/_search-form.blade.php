<form method="GET" action="{{ route('admin.ordemservicocanceladas.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ordemservicocanceladas.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_ordem_servico" label="Cód. Ordem de Serviço"
                value="{{ request('id_ordem_servico') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_abertura" label="Data abertura"
                value="{{ request('data_abertura') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_tipo_ordem_servico" label="Tipo Ordem Serviço"
                placeholder="Selecione o tipo de ordem de serviço..." :options="$tipoOrdemServico" asyncSearch="false"
                selected="{{ request('id_tipo_ordem_servico') }}" />
        </div>

        <div>
            <x-forms.input name="id_lancamento_os_auxiliar" label="Cód. Lançamento"
                value="{{ request('id_lancamento_os_auxiliar') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..." :options="$veiculosFrequentes"
                :searchUrl="route('admin.api.veiculos.search')" selected="{{ request('id_veiculo') }}" asyncSearch="true" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.smart-select name="recepcionista" label="Recepcionista" placeholder="Selecione o recepcionista..."
                :options="$usuariosFrequentes" :selected="request('recepcionista')" asyncSearch="false" />
        </div>

        <div>
            @php
                $localManutencao = [
                    'INTERNO' => 'INTERNO',
                    'EXTERNO' => 'EXTERNO',
                ];
            @endphp
            <x-forms.select name="local_manutencao" label="Local Manutenção" :options="$localManutencao" :selected="request('local_manutencao')" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." :options="$filiais"
                selected="{{ request('id_filial') }}" asyncSearch="false" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.ordemservicocanceladas.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>
    </div>
</form>
