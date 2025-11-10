<form method="GET" action="{{ route('admin.pneus.index') }}" class="space-y-4" hx-get="{{ route('admin.pneus.index') }}"
    hx-target="#results-table" hx-select="#results-table" hx-trigger="change delay:500ms, search">

    <div class="flex w-full gap-2">
        <div class="w-full">
            <x-forms.input name="id_pneu" 
                label="Número de Fogo" 
                value="{{ request('id_pneu') }}" 
            />
        </div>

        <div class="w-full">
            <x-forms.input name="cod_antigo" label="Número de Fogo Antigo" value="{{ request('cod_antigo') }}" />
        </div>
    </div>

    <div class="flex w-full gap-2">
        <div class="w-full">
            <x-forms.input type="date" name="data_inclusao_inicial" label="Data Inclusão Inicial"
                value="{{ request('data_inclusao_inicial') }}" />
        </div>
        <div class="w-full">
            <x-forms.input type="date" name="data_inclusao_final" label="Data Inclusão Final"
                value="{{ request('data_inclusao_final') }}" />
        </div>
    </div>

    <div class="flex w-full gap-2">
        <div class="w-full">
            <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
            <select name="id_filial"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach ($formOptions['filiais'] as $filial)
                    <option value="{{ $filial['value'] }}"
                        {{ old('id_filial', request('id_filial') ?? '') == $filial['value'] ? 'selected' : '' }}>
                        {{ $filial['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full">
            <label for="id_departamento"
                class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
            <select name="id_departamento"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach ($formOptions['departamentos'] as $departamento)
                    <option value="{{ $departamento['value'] }}"
                        {{ old('id_departamento', request('id_departamento') ?? '') == $departamento['value'] ? 'selected' : '' }}>
                        {{ $departamento['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="flex gap-2 w-full">
        <div class="w-6/12">
            <label for="status_pneu" class="block text-sm font-medium text-gray-700 mb-1">Status Pneu</label>
            <select name="status_pneu"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione...</option>
                @foreach ($formOptions['statuses'] as $item)
                    <option 
                        value="{{ $item['value'] }}"
                        {{ old('status_pneu', request('status_pneu') ?? '') == $item['value'] ? 'selected' : '' }}
                    >
                    {{ $item['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div>
            <div class="flex space-x-2">
                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                    Buscar
                </button>

                <a href="{{ route('admin.pneus.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
</form>
