<form method="GET" action="{{ route('admin.solicitacaoimobilizado.index') }}" class="space-y-4"
    hx-get="{{ route('admin.solicitacaoimobilizado.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            {{-- Cod. Descarte Imobilizados --}}
            <x-forms.input name="id_relacao_imobilizados" label="Cód. Solicitacao Imobilizados"
                value="{{ request('id_relacao_imobilizados') }}" />
        </div>

        <div>
            {{-- Data Final --}}
            <x-forms.input type="date" name="data_inclusao" label="Data Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            {{-- Data Final --}}
            <x-forms.input type="date" name="data_alteracao" label="Data Final"
                value="{{ request('data_alteracao') }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            {{-- Departamento --}}
            <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Selecione o departamento..."
                :options="$id_departamento_relacao_imobilizados" asyncSearch="false" />
        </div>

        <div>
            {{-- Usuario --}}
            <x-forms.smart-select name="id_usuario" label="Usuário" placeholder="Selecione o usuario..."
                :options="$id_usuario_relacao_imobilizados" asyncSearch="false" />
        </div>

        <div>
            {{-- Situacao --}}
            <x-forms.smart-select name="status" label="Situação" placeholder="Selecione a Situação..."
                :options="$status_relacao_imobilizados" asyncSearch="false" />
        </div>
    </div>


    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.solicitacaoimobilizado.index') }}"
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