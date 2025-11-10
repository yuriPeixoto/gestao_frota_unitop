<form method="GET" action="{{ route('admin.tanques.index') }}" class="space-y-4"
    hx-get="{{ route('admin.tanques.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="ms-0.5 grid grid-cols-3 md:grid-cols-3 gap-4">
        <div>
            <x-forms.input name="id_tanque" placeholder="Cód. Tanque" label="Cód. Tanque:"
                value="{{ request('id_tanque') }}" />
        </div>

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
            <label for="combustivel" class="block text-sm font-medium text-gray-700 mb-1">Combustível:</label>
            <select name="combustivel" id="combustivel" error_message="combustivel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($combustivel as $combustiveis)
                <option value="{{ $combustiveis['value'] }}" {{ request('combustivel')==$combustiveis['value']
                    ? 'selected' : '' }}>
                    {{ $combustiveis['label'] }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="ma-4 grid grid-cols-4 md:grid-cols-4 gap-4">
        <div>
            <label for="descricao_ats" class="block text-sm font-medium text-gray-700 mb-1">Descrição ATS:</label>
            <select name="descricao_ats" id="descricao_ats" error_message="descricao_ats"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($descricao_ats as $descricoes_ats)
                <option value="{{ $descricoes_ats['label'] }}" {{ request('descricao_ats')==$descricoes_ats['label']
                    ? 'selected' : '' }}>
                    {{ $descricoes_ats['label'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tanque" class="block text-sm font-medium text-gray-700 mb-1">Descrição Tanque:</label>
            <select name="tanque" id="tanque" error_message="tanque"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($descricao_tanque as $descricoes_tanques)
                <option value="{{ $descricoes_tanques['label'] }}" {{ request('tanque')==$descricoes_tanques['label']
                    ? 'selected' : '' }}>
                    {{ $descricoes_tanques['label'] }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Filtros de data -->
        <div>
            <label for="data_inclusao" class="block text-sm font-medium text-gray-700 mb-1">Data de Inclusão:</label>
            <input type="date" name="data_inclusao" id="data_inclusao" value="{{ request('data_inclusao') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label for="data_alteracao" class="block text-sm font-medium text-gray-700 mb-1">Data de Alteração:</label>
            <input type="date" name="data_alteracao" id="data_alteracao" value="{{ request('data_alteracao') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="flex justify-between mt-4 me-4">
        <div></div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.tanques.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-400 hover:red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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