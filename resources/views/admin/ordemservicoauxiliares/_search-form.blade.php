<form method="GET" action="{{ route('admin.ordemservicoauxiliares.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ordemservicoauxiliares.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_os_auxiliar" label="Cód. Lançamento" value="{{ request('id_os_auxiliar') }}" />
        </div>
        <div class="flex flex-col items-center space-y-2">
            <label class="font-medium text-gray-700">Processado:</label>
            <div>
                <label for="sim">
                    <input type="radio" name="processado" value="1" id="sim"
                        {{ request('processado') == 1 ? 'checked' : '' }}>
                    <span class="label-text">Sim</span>
                </label>

                <label for="nao">
                    <input type="radio" name="processado" value="0" id="nao"
                        {{ request('processado') == 0 ? 'checked' : '' }}>
                    <span class="label-text">Não</span>
                </label>
            </div>
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inicial"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Final"
                value="{{ request('data_inclusao') }}" />
        </div>
    </div>

    <div>
        <x-forms.smart-select name="id_recepcionista" label="Recepcionista" placeholder="Selecione o recepcionista..."
            :options="$usuariosFrequentes" :selected="request('id_recepcionista')" asyncSearch="false" />
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>

            <a href="{{ route('admin.ordemservicoauxiliares.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>
        </div>
    </div>
</form>
@push('scripts')
    @include('admin.ordemservicoauxiliares._scripts')
@endpush
