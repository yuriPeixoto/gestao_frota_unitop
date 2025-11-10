<form method="GET" action="{{ route('admin.requisicaoMaterial.index') }}" class="space-y-4"
    hx-get="{{ route('admin.requisicaoMaterial.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
        <x-forms.input name="id_solicitacao_pecas" label="Código Requisição"
            value="{{ request('id_solicitacao_pecas') }}" />

        <x-forms.input name="data_inicial" type="date" label="Data inicial" value="{{ request('data_inicial') }}" />

        <x-forms.input name="data_final" type="date" label="Data Final" value="{{ request('data_final') }}" />

        <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Selecione o departamento..."
            :options="$dados['departamento']" :selected="request('id_departamento')" asyncSearch="true" />

        <x-forms.smart-select name="id_usuario" label="Usuario solicitante"
            placeholder="Selecione o usuario solicitante..." :options="$dados['usuarios']" :selected="request('id_usuario')" asyncSearch="true" />

        <x-forms.smart-select name="id_situacao" label="Situação" placeholder="Selecione o situação..."
            :options="$dados['situacao']" :selected="request('id_situacao')" asyncSearch="true" />
    </div>


    <div class="flex justify-between mt-4">
        <div>
            <a href="{{ route('admin.requisicaoMaterial.index') }}"
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
