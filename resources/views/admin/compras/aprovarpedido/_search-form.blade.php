<form method="GET" action="{{ route('admin.compras.aprovarpedido.index') }}" class="space-y-4"
    hx-get="{{ route('admin.compras.aprovarpedido.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div>
            {{-- Cod. Solicitação de Compras --}}
            <x-forms.smart-select name="id_solicitacoes_compras" label="Solicitação de Compras"
                placeholder="Selecione a solicitação de compras..." :options="$id_solicitacoes_compras" asyncSearch="false" />
        </div>

        <div>
            {{-- Data Inicio --}}
            <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data Início</label>
            <input type="date" name="data_inicio"
                class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Selecione a data de início..." value="{{ request('data_inicio') }}" />
        </div>

        <div>
            {{-- Data final --}}
            <label for="data_final" class="block text-sm font-medium text-gray-700">Data Final</label>
            <input type="date" name="data_final"
                class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Selecione a data de início..." value="{{ request('data_final') }}" />
        </div>

        <div>
            {{-- Cod. Departamento --}}
            <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Selecione o departamento..."
                :options="$departamentos" asyncSearch="false" />
        </div>

        <div>
            {{-- Cod. Filial --}}
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..." :options="$filiais"
                asyncSearch="false" />
        </div>

    </div>

    <div class="mt-4 flex justify-end">
        <a href="{{ route('admin.compras.aprovarpedido.index') }}"
            class="mr-2 inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Limpar
        </a>
        <button type="submit"
            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Filtrar
        </button>
    </div>

</form>
