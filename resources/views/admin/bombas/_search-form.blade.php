<form method="GET" action="{{ route('admin.bombas.index') }}" class="space-y-4"
    hx-get="{{ route('admin.bombas.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_bomba" label="Código Bomba" value="{{ request('id_bomba') }}" />
        </div>

        <div>
            <div>
                <label for="descricao_bomba" class="block text-sm font-medium text-gray-700 mb-1">Descrição:</label>
                <select name="descricao_bomba" id="descricao_bomba" error_message="descricao_bomba"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Selecione...</option>
                    @foreach ($descricao_bomba as $descricoes_bombas)
                    <option value="{{ $descricoes_bombas['label'] }}" {{
                        request('descricao_bomba')==$descricoes_bombas['label'] ? 'selected' : '' }}>
                        {{ $descricoes_bombas['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final_inclusao" label="Data Inclusão Final"
                value="{{ request('data_final_inclusao') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial:</label>
            <select name="id_filial" id="id_filial" error_message="id_filial"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($filial as $filiais)
                <option value="{{ $filiais['value'] }}" {{ request('id_filial')==$filiais['value'] ? 'selected' : '' }}>
                    {{ $filiais['label'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="id_tanque" class="block text-sm font-medium text-gray-700 mb-1">Tanque:</label>
            <select name="id_tanque" id="id_tanque" error_message="id_tanque"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($tanque as $tanques)
                <option value="{{ $tanques['value'] }}" {{ request('id_tanque')==$tanques['value'] ? 'selected' : '' }}>
                    {{ $tanques['label'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status:</label>
            <select name="status" id="status" error_message="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Todos</option>
                @foreach ($status_options as $option)
                <option value="{{ $option['value'] }}" {{ request('status')==$option['value'] ? 'selected' : '' }}>
                    {{ $option['label'] }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            {{-- Usar o componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.bombas" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.bombas.index') }}"
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