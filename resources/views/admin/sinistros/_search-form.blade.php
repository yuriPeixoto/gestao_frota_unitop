<form method="GET" action="{{ route('admin.sinistros.index') }}" class="space-y-4"
    hx-get="{{ route('admin.sinistros.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_sinistro" label="Cód. Sinistros" value="{{ request('id_sinistro') }}" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Status" placeholder="Selecione o status..." :options="[
                ['value' => 'Em Andamento', 'label' => 'Em Andamento'],
                ['value' => 'Finalizada', 'label' => 'Finalizada'],
            ]"
                :selected="request('status')" asyncSearch="false" />
        </div>

        <div>
            <x-forms.input name="placa" label="Placa" value="{{ request('placa') }}" />
        </div>

        <div>
            <x-forms.input name="filial" label="Filial" value="{{ request('filial') }}" />
        </div>

        <div>
            <x-forms.input name="motorista" label="Motorista" value="{{ request('motorista') }}" />
        </div>

        <div>
            <x-forms.input name="responsabilidade" label="Responsabilidade" value="{{ request('responsabilidade') }}" />
        </div>

        <div>
            <x-forms.input name="orgao_sinistro" label="Orgão Registro" value="{{ request('orgao_sinistro') }}" />
        </div>

        <div class="col-span-3 grid grid-cols-4 gap-2 w-full">
            <div class="col-span-1">
                <x-forms.input class="w-full" type="date" name="data_sinistro" label="Data Ocorrência Início"
                    value="{{ request('data_sinistro') }}" />
            </div>

            <div class="col-span-1">
                <x-forms.input class="w-full" type="date" name="data_sinistro_fim" label="Data Ocorrência Fim"
                    value="{{ request('data_sinistro_fim') }}" />
            </div>
            <div class="col-span-1">
                <x-forms.input class="w-full" type="date" name="data_inclusao" label="Data Inclusão Início"
                    value="{{ request('data_inclusao') }}" />
            </div>
            <div class="col-span-1">
                <x-forms.input class="w-full" type="date" name="data_inclusao_fim" label="Data Inclusão Fim"
                    value="{{ request('data_inclusao_fim') }}" />
            </div>
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.sinistros.index') }}"
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
