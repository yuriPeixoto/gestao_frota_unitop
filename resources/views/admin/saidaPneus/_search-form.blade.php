<form method="GET" action="{{ route('admin.saidaPneus.index') }}" class="space-y-4"
    hx-get="{{ route('admin.saidaPneus.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div>
            <x-forms.input type="datetime-local" name="data_inicial" label="Data Inicial"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="datetime-local" name="data_final" label="Data Final"
                value="{{ request('data_final') }}" />
        </div>

        <div>
            @php
                $situacaoOptions = $situacoes ?? [];
            @endphp
            <x-forms.select name="situacao" label="Situação" :options="$situacaoOptions" :selected="request('situacao')" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" :options="$filiais" :selected="request('id_filial')" />
        </div>

        <div>
            @php
                $usuarioOptions = ($usuarios ?? collect())->pluck('name', 'id')->toArray();
            @endphp
            <x-forms.select name="id_usuario_solicitante" label="Usuário Solicitante" :options="$usuarioOptions"
                :selected="request('id_usuario_solicitante')" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit" id="search-input"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.saidaPneus.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>

        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.saidaPneus" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>
