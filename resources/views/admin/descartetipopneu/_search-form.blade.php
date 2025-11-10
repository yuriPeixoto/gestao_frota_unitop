<form method="GET" action="{{ route('admin.descartetipopneu.index') }}" class="space-y-4">

    {{-- Linha 1 --}}
    <div class="flex w-full gap-2">
        <div class="w-full">
            <x-forms.input name="id_tipo_descarte" label="Código Descarte:" />
        </div>


        <div class="w-full">
            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão:"
                value="{{ request('data_inclusao') }}" />
        </div>

        <div class="w-full">
            <x-forms.input type="date" name="data_alteracao" label="Data Alteração:"
                value="{{ request('data_alteracao') }}" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="descricao_tipo_descarte" label="Descrição Tipo Descarte:"
                :options="$tipoDescarte" />
        </div>

    </div>



    {{-- Botões --}}
    <div class="flex justify-between mt-4">
        <div>
            <div class="flex space-x-2">
                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Buscar
                </button>

                <a href="{{ route('admin.descartetipopneu.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
</form>