<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center" id="formPneu">

    <x-forms.input name="id_pneu" type="number" label="Nº Fogo" max="10" readonly="true"
        value="{{ $isCreate ? $formOptions['id_pneu'] : old('id_pneu', $pneus->id_pneu ?? '') }}" />

    <div>
        <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>

        @php
            $usuarios = [1, 25, 318];
            $disabled = in_array(auth()->user()->id, $usuarios) ? false : true;
        @endphp
        <select id="id_filial" name="id_filial" @disabled($disabled)
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $disabled ? 'bg-gray-200' : '' }}">

            <option value="">Selecione...</option>
            @foreach ($formOptions['filiais'] as $filial)
                <option value="{{ $filial['value'] }}"
                    {{ old('id_filial', $pneus->id_filial ?? 1) == $filial['value'] ? 'selected' : 1 }}>
                    {{ $filial['label'] }}
                </option>
            @endforeach
        </select>

        <!-- Campo oculto para garantir que o valor seja enviado -->
        <input type="hidden" name="id_filial" value="{{ old('id_filial', $pneus->id_filial ?? 1) }}">
    </div>

    <div>
        <label for="id_departamento" class="block text-sm font-medium text-gray-700">Departamento</label>

        <select id="id_departamento" name="id_departamento" @disabled($disabled)
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $disabled ? 'bg-gray-200' : '' }}">

            <option value="">Selecione...</option>
            @foreach ($formOptions['departamentos'] as $departamento)
                <option value="{{ $departamento['value'] }}"
                    {{ old('id_departamento', $pneus->id_departamento ?? 1) == $departamento['value'] ? 'selected' : 1 }}>
                    {{ $departamento['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="status_pneu" class="block text-sm font-medium text-gray-700">Status Pneu</label>

        <select id="status_pneu" name="status_pneu" @disabled($disabled)
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $disabled ? 'bg-gray-200' : '' }}">
            <option value="">Selecione...</option>
            @foreach ($formOptions['status_pneu'] as $status)
                <option value="{{ $status['value'] }}"
                    {{ old('status_pneu', $pneus->status_pneu ?? 'ESTOQUE') == $status['value'] ? 'selected' : 'ESTOQUE' }}>
                    {{ $status['label'] }}
                </option>
            @endforeach
        </select>

        <!-- Campo oculto para garantir que o valor seja enviado -->
        <input type="hidden" name="status_pneu" value="{{ old('status_pneu', $pneus->status_pneu ?? 'ESTOQUE') }}">
    </div>
</div>

<div class="col-span-full mt-5">
    <h3 class="font-medium text-gray-800 uppercase">Cadastro do Pneu</h3>
    <hr>

    <div class="mt-3">
        <label for="id_modelo_pneu" class="block text-sm font-medium text-gray-700 mb-1">Cód.
            Modelo Pneu</label>
        <select name="id_modelo_pneu" id="id_modelo_pneu" onchange="getInfoPneu(this.value)"
            class="w-full outline-none border-gray-300 rounded-md">
            <option value="">Selecione...</option>
            @foreach ($formOptions['modeloPneu'] as $modelo)
                <option value="{{ $modelo['value'] }}"
                    {{ old('id_modelo_pneu', $pneus->id_modelo_pneu ?? '') == $modelo['value'] ? 'selected' : '' }}>
                    {{ $modelo['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-3 gap-2 mt-3">
        <x-forms.input name="id_desenho_pneu" type="text" label="Desenho do Pneu" disabled="true" />

        <x-forms.input name="id_dimensao_pneu" type="text" label="Dimensão do Pneu" disabled="true" />

        <x-forms.input name="id_fornecedor" type="text" label="Fornecedor" disabled="true" />
    </div>

</div>

<div class="col-span-full mt-5">
    <h3 class="font-medium text-gray-800 uppercase">Controle da Vida do Pneu</h3>
    <hr>

    <div class="grid grid-cols-5 gap-2 mt-3">
        <div>
            <label for="id_controle_vida_pneu" class="block text-sm font-medium text-gray-700 mb-1">Vida
                do Pneu</label>
            <select name="id_controle_vida_pneu" id="id_controle_vida_pneu"
                class="w-full outline-none border-gray-300 rounded-md">
                <option value="">Selecione...</option>
                @foreach ($formOptions['controleVidaPneu'] as $vida)
                    <option value="{{ $vida['value'] }}">{{ $vida['label'] }}</option>
                @endforeach
            </select>
        </div>

        <x-forms.input name="km_rodagem" type="number" label="Km Rodagem"
            selected_value="{{ old('km_rodagem', $pneus->controleVidaPneus->km_rodagem ?? '') }}" />

        <x-forms.input name="sulco_pneu_novo" type="text" label="Sulco Pneu Novo"
            selected_value="{{ old('sulco_pneu_novo', $pneus->controleVidaPneus->sulco_pneu_novo ?? '') }}" />

        <x-forms.input name="sulco_pneu_reformado" type="text" label="Sulco Pneu Reformado"
            selected_value="{{ old('sulco_pneu_reformado', $pneus->controleVidaPneus->sulco_pneu_reformado ?? '') }}" />

        <x-forms.input name="numero_lonas" type="number" label="Nº Lonas"
            selected_value="{{ old('numero_lonas', $pneus->controleVidaPneus->numero_lonas ?? '') }}" />
    </div>
</div>

<h3 class="font-medium text-gray-800 uppercase mt-10">Histórico do Pneu</h3>
<hr>
<!-- Campo hidden para armazenar os históricos -->
<input type="hidden" name="histPneus" id="histPneus_json"
    value="{{ isset($historicoPneu) ? json_encode($historicoPneu) : '[]' }}">

<div class="shadow overflow-hidden sm:rounded-md mt-4">
    <div id="tabela-historico">
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                <x-tables.head-cell>Data Alteracao</x-tables.head-cell>
                <x-tables.head-cell>Cód.<br>Vida Pneu</x-tables.head-cell>
                <x-tables.head-cell>Placa</x-tables.head-cell>
                <x-tables.head-cell>KM Inicial</x-tables.head-cell>
                <x-tables.head-cell>KM Final</x-tables.head-cell>
                <x-tables.head-cell>Hr inicial</x-tables.head-cell>
                <x-tables.head-cell>Hr Final</x-tables.head-cell>
                <x-tables.head-cell>Eixo aplicado</x-tables.head-cell>
                <x-tables.head-cell>Data retirado</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tabela-historico-body">
            </x-tables.body>
        </x-tables.table>
    </div>
</div>
