<form method="GET" action="{{ route('admin.devolucaosaidaestoque.index') }}" class="space-y-4"
    hx-get="{{ route('admin.devolucaosaidaestoque.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Campo hidden para manter a tab ativa --}}
    <input type="hidden" name="active_tab" id="active_tab_unificado" value="Aba3">

    {{-- Exibir mensagens de erro/confirmação --}}


    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            <x-forms.input name="id_devolucao_materiais" label="Código Devolução"
                value="{{ request('id_devolucao_materiais') }}" />
        </div>

        <div>
            <x-forms.input name="id_relacaosolicitacoespecas" label="Código Solicitação de Materiais"
                value="{{ request('id_relacaosolicitacoespecas') }}" />
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
            <a href="{{ route('admin.devolucaosaidaestoque.index') }}"
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

        <div>
            <x-forms.button href="{{ route('admin.devolucaosaidaestoque.create_devMateriais') }}">
                <x-icons.plus class="h-4 w-4" />
                Cadastrar Devolução de Materiais
            </x-forms.button>
        </div>
    </div>
</form>
