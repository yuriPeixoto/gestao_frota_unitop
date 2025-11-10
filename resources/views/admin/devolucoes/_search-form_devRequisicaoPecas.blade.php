<form method="GET" action="{{ route('admin.devolucoes.index') }}" class="space-y-4"
    hx-get="{{ route('admin.devolucoes.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Campo hidden para manter a tab ativa --}}
    <input type="hidden" name="active_tab" id="active_tab_unificado" value="Aba2">

    {{-- Exibir mensagens de erro/confirmação --}}


    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">

        <div>
            <x-forms.input name="id_solicitacao_pecas" label="Código Solicitações"
                value="{{ request('id_solicitacao_pecas') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_departamento" label="Departamento" :options="$departamentos"
                value="{{ request('id_departamento') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial_solicitante" label="Filial Solicitante" :options="$filiais"
                value="{{ request('id_filial_solicitante') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao_final" label="Data Inclusão Final"
                value="{{ request('data_inclusao_final') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">

        <div class="flex space-x-2">
            <a href="{{ route('admin.devolucoes.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit" id="search-input"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>
