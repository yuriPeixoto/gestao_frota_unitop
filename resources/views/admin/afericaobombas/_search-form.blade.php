<form method="GET" action="{{ route('admin.afericaobombas.index') }}" class="space-y-4"
    hx-get="{{ route('admin.afericaobombas.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_abastecimento_integracao" label="Cód. Abastecimento"
                value="{{ request('id_abastecimento_integracao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicio_inicial" label="Data Inicial"
                value="{{ request('data_inicio_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicio_final" label="Data Final"
                value="{{ request('data_inicio_final') }}" />
        </div>

        <div>
            <label for="descricao_bomba" class="block text-sm font-medium text-gray-700 mb-1">Descriçao da
                bomba:</label>
            <select name="descricao_bomba" id="descricao_bomba" error_message="descricao_bomba"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($descricao_bomba as $descricoes_bombas)
                <option value="{{ $descricoes_bombas['value'] }}" {{
                    request('descricao_bomba')==$descricoes_bombas['value'] ? 'selected' : '' }}>
                    {{ $descricoes_bombas['label'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="entrada_realizada" class="block text-sm font-medium text-gray-700 mb-1">Entrada:</label>
            <select name="entrada_realizada" id="entrada_realizada"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Todos</option>
                <option value="1" {{ request('entrada_realizada')=='1' ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ request('entrada_realizada')=='0' ? 'selected' : '' }}>Não</option>
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Botões de exportação --}}
            <x-ui.export-buttons route="admin.afericaobombas" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.afericaobombas.index') }}"
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