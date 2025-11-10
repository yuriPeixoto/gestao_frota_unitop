<form method="GET" action="{{ route('admin.listagemantt.index') }}" class="space-y-4"
    hx-get="{{ route('admin.listagemantt.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            {{-- Renavam --}}
            <x-forms.smart-select name="placa" label="Placa"
                placeholder="Selecione a placa..." :options="$placa"
                asyncSearch="false" />
        </div>

        <div>
            {{-- Processo --}}
            <x-forms.smart-select name="processo" label="Renavam"
                placeholder="Selecione o processo..." :options="$processo"
                asyncSearch="false" />
        </div>

        <div>
            {{-- AIT --}}
            <x-forms.smart-select name="ait" label="AIT"
                placeholder="Selecione o ait..." :options="$ait"
                asyncSearch="false" />
        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <x-ui.export-buttons route="admin.listagemantt" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.listagemantt.index') }}"
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