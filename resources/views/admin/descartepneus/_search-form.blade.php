<form method="GET" action="{{ route('admin.descartepneus.index') }}" class="space-y-4">

    {{-- TÍTULO DA SEÇÃO --}}
    {{--<div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Filtros de Busca
        </h3>
    </div>--}}

    {{-- IDENTIFICAÇÃO --}}
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Identificação
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-forms.smart-select name="id_descarte_pneu" label="Código da Baixa"
                                      placeholder="Selecione o código..." :options="$destartePneus"
                                      :selected="request('id_descarte_pneu')" asyncSearch="true" />
            </div>
            <div>
                <x-forms.smart-select name="id_pneu" label="Número de Fogo"
                                      placeholder="Selecione o pneu..." :options="$pneu"
                                      :searchUrl="route('admin.api.pneu.search')" :selected="request('id_pneu')"
                                      asyncSearch="true" />
            </div>
        </div>
    </div>

    {{-- CLASSIFICAÇÃO --}}
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Classificação
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-forms.smart-select name="id_tipo_descarte" label="Tipo de Descarte"
                                      placeholder="Selecione o tipo..." :options="$tipodescarte"
                                      :selected="request('id_tipo_descarte')" :searchUrl="route('admin.api.tipodescarte.search')"
                                      asyncSearch="true" />
            </div>
            <div>
                <label for="origem" class="block text-sm font-medium text-gray-700 mb-2">Origem</label>
                <select name="origem" id="origem"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todas as origens</option>
                    <option value="manual" {{ request('origem') == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="manutencao" {{ request('origem') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                </select>
            </div>
        </div>
    </div>

    {{-- STATUS E PERÍODO --}}
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Status e Período
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="status_processo" class="block text-sm font-medium text-gray-700 mb-2">Status do Processo</label>
                <select name="status_processo" id="status_processo"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Todos os status</option>
                    <option value="aguardando_inicio" {{ request('status_processo') == 'aguardando_inicio' ? 'selected' : '' }}>
                        Aguardando Início
                    </option>
                    <option value="em_andamento" {{ request('status_processo') == 'em_andamento' ? 'selected' : '' }}>
                        Em Andamento
                    </option>
                    <option value="finalizado" {{ request('status_processo') == 'finalizado' ? 'selected' : '' }}>
                        Finalizado
                    </option>
                </select>
            </div>
            <div>
                <x-forms.input type="date" name="data_inclusao" label="Data de Inclusão"
                               value="{{ request('data_inclusao') }}" />
            </div>
        </div>
    </div>

    {{-- AÇÕES --}}
    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
        <a href="{{ route('admin.descartepneus.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Buscar
        </button>
    </div>
</form>
