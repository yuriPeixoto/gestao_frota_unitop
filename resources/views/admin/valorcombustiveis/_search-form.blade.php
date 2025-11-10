<!-- OPÇÃO 1: Botões maiores, na mesma linha (ajuste apenas o tamanho) -->
<form method="GET" action="{{ route('admin.valorcombustiveis.index') }}" class="space-y-3 text-sm"
    hx-get="{{ route('admin.valorcombustiveis.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            {{-- Data Inicio --}}
            <x-forms.input type="date" name="data_inicio" label="Data Início" value="{{ request('data_inicio') }}" />
        </div>

        <div>
            {{-- Data Fim --}}
            <x-forms.input type="date" name="data_fim" label="Data Fim" value="{{ request('data_fim') }}" />
        </div>

        <div>
            {{-- Bomba --}}
            <x-forms.smart-select name="boma_combustivel" label="Bomba" placeholder="Selecione..."
                :options="$bombas ?? []" :selected="request('boma_combustivel')" asyncSearch="false" class="text-xs"
                labelClass="text-xs" />
        </div>

        <div>
            {{-- Tipo de Combustível --}}
            <x-forms.smart-select name="id_tipo_combustivel" label="Tipo de Combustível" placeholder="Selecione..."
                :options="$tiposCombustivel ?? []" :selected="request('id_tipo_combustivel')" asyncSearch="false"
                class="text-xs" labelClass="text-xs" />
        </div>

        <div class="flex items-end">
            {{-- Ações --}}
            <div class="flex space-x-2 h-10 w-full">
                <a href="{{ route('admin.valorcombustiveis.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-1" />
                    Limpar
                </a>

                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-1" />
                    Buscar
                </button>
            </div>
        </div>
    </div>
</form>