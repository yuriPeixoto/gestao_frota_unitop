<form method="GET" action="{{ route('admin.tipodimensaopneus.index') }}" class="space-y-4"
    hx-get="{{ route('admin.tipodimensaopneus.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <x-forms.input name="id_dimensao_pneu" label="Cód. Dimensão Pneu"
                value="{{ request('id_dimensao_pneu') }}" />
        </div>

        <div>
            {{-- descrição --}}
            <x-forms.smart-select name="descricao_pneu" label="Descrição" placeholder="Selecione a descrição..."
                :options="$descricao" asyncSearch="false"
                :selected="old('descricao_pneu', $manutencaoImobilizados->descricao_pneu ?? '')" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão"
                value="{{ request('data_inclusao') }}" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.tipodimensaopneus.index') }}"
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