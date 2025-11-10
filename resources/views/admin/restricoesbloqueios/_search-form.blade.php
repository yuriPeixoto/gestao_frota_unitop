<form method="GET" action="#" class="space-y-4" hx-get="#" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div>
            {{-- Renavam --}}
            <x-forms.smart-select name="" label="Placa" placeholder="Selecione a placa..." :options="$veiculos"
                asyncSearch="false" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inclusao" label="Data Inicial" />

        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Inicial" />

        </div>

    </div>

    <div class="flex justify-between mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        </div>

        <div class="flex space-x-2">
            <a href="#"
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