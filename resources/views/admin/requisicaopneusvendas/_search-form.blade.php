<form method="GET" action="{{ route('admin.requisicaopneusvendas.index') }}" class="space-y-4"
    hx-get="{{ route('admin.relacaodespesasveiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 gap-2">

        <div>
            <x-forms.input name="id_requisicao_pneu" label="Código Requisição"
                value="{{ request('id_requisicao_pneu') }}" />
        </div>

        <div>
            <x-forms.input name="data_inicial" type="date" label="Data inicial"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input name="data_final" type="date" label="Data Final" value="{{ request('data_final') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_usuario" label="Usuario solicitante"
                placeholder="Selecione o usuario solicitante..." :options="$form['pessoa']" :selected="request('id_usuario')"
                asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_situacao" label="Situação" placeholder="Selecione o situação..."
                :options="$form['situacao']" :selected="request('id_situacao')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione o filial..." :options="$form['filial']"
                :selected="request('id_filial')" asyncSearch="true" />
        </div>
    </div>

    <div class="m-2">

        <x-forms.button href="{{ route('admin.requisicaopneusvendas.index') }}" type="secondary" variant="outlined">
            <x-icons.trash class="h-4 w-4 mr-2" />
            Limpar
        </x-forms.button>

        <x-forms.button button-type="submit" type="primary">
            <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
            Buscar
        </x-forms.button>
    </div>
</form>
