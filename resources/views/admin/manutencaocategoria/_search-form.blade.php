<form method="GET" action="{{ route('admin.manutencaocategoria.index') }}" class="space-y-4"
    hx-get="{{ route('admin.manutencaocategoria.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    <div class="flex gap-4">

        <div class="w-full">
            <x-forms.smart-select name="id_categoria" label="" placeholder="Selecione a categoria..."
                :options="$referenceDatas['categoria']" :selected="request('id_categoria')" asyncSearch="true" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="id_manutencao" label="" placeholder="Selecione a manutencao..."
                :options="$referenceDatas['manutencao']" :selected="request('id_manutencao')" asyncSearch="true" />
        </div>

    </div>

    {{-- {{ dd($referenceDatas['categoria']) }} --}}

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.manutencaocategoria.index') }}"
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
