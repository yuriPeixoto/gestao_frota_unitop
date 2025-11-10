<div class="grid grid-cols-1 gap-2 md:grid-cols-2">
    {{-- <div class="flex flex-col items-center space-y-2">
        <label class="font-medium text-gray-700">Lançado?</label>
        <div class="relative inline-flex w-40">

            <label for="sim"
                class="cursor-pointer w-1/2 text-center py-2 font-medium relative z-10 text-gray-700 transition-all duration-300">
                <input type="radio" name="processado" value="1" id="sim" {{ isset($ordemservicoauxiliares->processado)
                && $ordemservicoauxiliares->processado == 1 ? 'checked' : '' }}>
                <span class="label-text">Sim</span>
            </label>

            <label for="nao"
                class="cursor-pointer w-1/2 text-center py-2 font-medium relative z-10 text-gray-700 transition-all duration-300">
                <input type="radio" name="processado" value="0" id="nao" {{ isset($ordemservicoauxiliares->processado)
                && $ordemservicoauxiliares->processado == 0 ? 'checked' : '' }}>
                <span class="label-text">Não</span>
            </label>
        </div>
    </div>
    @error('processado')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror --}}
    <div>
        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione um fornecedor..."
            :options="$formOptions['fornecedor']"
            :selected="old('id_fornecedor', $ordemservicoauxiliares->id_fornecedor ?? '')" />
    </div>
    <div>
        <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione uma filial..."
            :options="$formOptions['filial']" :selected="old('id_filial', $ordemservicoauxiliares->id_filial ?? '')" />

    </div>
</div>

<div class="mt-5 grid grid-cols-4 gap-2 md:grid-cols-4">
    <div class="mb-1 block text-sm font-medium text-gray-700">
        <label for="data_abertura" class="block text-sm font-medium text-gray-700">Data/Hora Abertura:</label>
        @error('data_abertura')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <input type="datetime-local" name="data_abertura"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('data_abertura', $ordemservicoauxiliares->data_abertura ?? now()->format('Y-m-d\TH:i')) }}"
            readonly>
    </div>

    <div>
        <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Selecione um departamento..."
            :options="$formOptions['departamento']" :selected="old(
                'id_departamento',
                $ordemservicoauxiliares->id_departamento ?? auth()->user()->id_departamento,
            )" />
    </div>

    <div>
        <x-forms.smart-select name="id_recepcionista" label="Recepcionista" placeholder="Selecione um recepcionista..."
            :options="$formOptions['usuarios']"
            :selected="old('id_recepcionista', $ordemservicoauxiliares->id_recepcionista ?? auth()->user()->id)"
            disabled="true" />
        <input type="hidden" name="id_recepcionista"
            value="{{ old('id_recepcionista', $ordemservicoauxiliares->id_recepcionista ?? auth()->user()->id) }}">
    </div>
    <div class="mb-1 block text-sm font-medium text-gray-700">
        <x-forms.smart-select name="local_manutencao" label="Local da Manutenção" placeholder="Selecione um local..."
            :options="[['value' => 'INTERNO', 'label' => 'INTERNO'], ['value' => 'EXTERNO', 'label' => 'EXTERNO']]"
            :selected="old('local_manutencao', $ordemservicoauxiliares->local_manutencao ?? '')" />
    </div>
</div>