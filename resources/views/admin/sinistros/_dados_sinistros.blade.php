<h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro
</h3>
<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

    <div class="col-span-1">
        <x-forms.smart-select name="id_veiculo" :options="$formOptions['placas']" label="Placa" :selected="old('id_veiculo', $sinistro->id_veiculo ?? '')" asyncSearch="true"
            disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-1">
        <label for="id_categoria_veiculo" class="block text-sm font-medium text-gray-700">Categoria</label>
        <select name="id_categoria_veiculo" id="categoria_select" disabled="{{ $bloquear }}"
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'mouse-not-allowed' : '' }}">
            <option value="">Selecione a Categoria</option>
            @foreach ($formOptions['categoria_veiculo'] as $categoria)
                <option value="{{ $categoria['value'] }}"
                    {{ old('id_veiculo', $sinistro->id_categoria_veiculo ?? '') == $categoria['value'] ? 'selected' : '' }}>
                    {{ $categoria['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    @php
        $statuslist = [
            ['label' => 'Finalizada', 'value' => 'Finalizada'],
            ['label' => 'Em Andamento', 'value' => 'Em Andamento'],
        ];
    @endphp

    <div class="col-span-1">
        <x-forms.smart-select name="status" :options="$statuslist" label="Status" :selected="old('status', $sinistro->status ?? '')" asyncSearch="false"
            disabled />
    </div>

    @php
        $processolist = [
            ['label' => 'CGB', 'value' => 'CGB'],
            ['label' => 'CGR', 'value' => 'CGR'],
            ['label' => 'FROTA', 'value' => 'FROTA'],
            ['label' => 'JVE', 'value' => 'JVE'],
            ['label' => 'RH', 'value' => 'RH'],
            ['label' => 'ROO', 'value' => 'ROO'],
            ['label' => 'SAO', 'value' => 'SAO'],
            ['label' => 'SEGURADORA', 'value' => 'SEGURADORA'],
            ['label' => 'TERCEIRO', 'value' => 'TERCEIRO'],
            ['label' => 'TRAFEGO', 'value' => 'TRAFEGO'],
            ['label' => 'COLETA/ENTREGA', 'value' => 'COLETA/ENTREGA'],
        ];
    @endphp

    <div class="col-span-1">
        <x-forms.smart-select name="setor" :options="$processolist" label="Processo Alocado" :selected="old('setor', $sinistro->setor ?? '')"
            asyncSearch="false" disabled="{{ $bloquear }}" />
    </div>

    @php
        $prazoindica = [
            ['label' => '30', 'value' => '30'],
            ['label' => '60', 'value' => '60'],
            ['label' => '90', 'value' => '90'],
        ];
    @endphp

    <div class="col-span-2">
        <x-forms.smart-select name="prazo_em_dias" :options="$prazoindica" label="Prazo" :selected="old('prazo_em_dias', $sinistro->prazo_em_dias ?? '')"
            asyncSearch="false" disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-2">
        <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
        <select name="id_filial" id="id_filial_select" @disabled($bloquear)
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'mouse-not-allowed' : '' }}">
            <option value="">Selecione...</option>
            @foreach ($formOptions['filiais'] as $filial)
                <option value="{{ $filial['value'] }}"
                    {{ old('id_filial', $sinistro->id_filial ?? '') == $filial['value'] ? 'selected' : '' }}>
                    {{ $filial['label'] }}
                </option>
            @endforeach
        </select>
        @error('id_filial')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="col-span-full">
        <x-forms.smart-select name="id_motorista" :options="$formOptions['pessoas']" label="Motorista" :selected="old('id_motorista', $sinistro->pessoal->nome ?? '')" />
    </div>

</div>

<hr class="mt-10 mb-10">

<h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados da Ocorrência</h3>

<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ">

    <div class="col-span-2">
        <x-forms.smart-select name="id_tipo_ocorrencia" :options="$formOptions['tipoocorrencias']" label="Tipo Ocorrência" :selected="old('id_tipo_ocorrencia', $sinistro->id_tipo_ocorrencia ?? '')"
            disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-2">
        <x-forms.smart-select name="id_motivo" :options="$formOptions['tipomotivos']" label="Motivo Ocorrência" :selected="old('id_motivo', $sinistro->id_motivo ?? '')"
            disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-1">
        <label for="data_sinistro" class="block text-sm font-medium text-gray-700">Data do Sinistro</label>
        <input
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="data_sinistro" type="date"
            value="{{ old('data_sinistro', isset($sinistro) && $sinistro->data_sinistro ? $sinistro->data_sinistro->format('Y-m-d') : '') }}"
            {{ $bloquear ? 'disabled' : '' }}>
        @error('data_sinistro')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

    <div class="col-span-3">
        <label for="responsabilidade_sinistro" class="block text-sm font-medium text-gray-700">Responsável</label>
        <textarea
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="responsabilidade_sinistro" rows="3" {{ $bloquear ? 'disabled' : '' }}>{{ old('responsabilidade_sinistro', $sinistro->responsabilidade_sinistro ?? '') }}</textarea>
    </div>

</div>


<hr class="mt-10 mb-10">

<h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados do Local</h3>

<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

    @php
        $situacaopista = [
            ['label' => 'seca', 'value' => 'Seca'],
            ['label' => 'molhada', 'value' => 'Molhada'],
            ['label' => 'neblina', 'value' => 'Com Neblina'],
        ];
    @endphp

    <div class="col-span-1">
        <x-forms.smart-select name="situacao_pista" :options="$situacaopista" label="Situação Pista" :selected="old('situacao_pista', $sinistro->situacao_pista ?? '')"
            disabled="{{ $bloquear }}" />
    </div>

    @php
        $estadopista = [
            ['label' => 'bom', 'value' => 'Bom'],
            ['label' => 'regular', 'value' => 'Regular'],
            ['label' => 'ruim', 'value' => 'Ruim'],
            ['label' => 'obras', 'value' => 'Em Obras'],
        ];
    @endphp

    <div class="col-span-1">
        <x-forms.smart-select name="estado_pista" :options="$estadopista" label="Estado Pista" :selected="old('estado_pista', $sinistro->estado_pista ?? '')"
            disabled="{{ $bloquear }}" />
    </div>

    @php
        $topografia = [
            ['label' => 'Reta', 'value' => 'Reta'],
            ['label' => 'Curva Direita', 'value' => 'Curva Direita'],
            ['label' => 'Curva Esquerda', 'value' => 'Curva Esquerda'],
            ['label' => 'Cruzamento', 'value' => 'Cruzamento'],
            ['label' => 'Ponte', 'value' => 'Ponte'],
            ['label' => 'Trevo', 'value' => 'Trevo'],
        ];
    @endphp

    <div class="col-span-1">
        <x-forms.smart-select name="topografica" :options="$topografia" label="Topografia" :selected="old('topografica', $sinistro->topografica ?? '')"
            disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-1 flex flex-col items-start">
        <label for="sinalizacao" class="mb-1 text-sm font-medium">Há
            Sinalização?</label>
        <div class="flex items-center space-x-4">
            <input type="radio" name="sinalizacao" value="1"
                {{ old('sinalizacao', $sinistro->sinalizacao ?? '') == '1' ? 'checked' : '' }} id="sinalizacao_sim"
                @disabled($bloquear)>
            <label for="sinalizacao_sim">Sim</label>

            <input type="radio" name="sinalizacao" value="0"
                {{ old('sinalizacao', $sinistro->sinalizacao ?? '') == '0' ? 'checked' : '' }} id="sinalizacao_nao"
                @disabled($bloquear)>
            <label for="sinalizacao_nao">Não</label>
        </div>
    </div>

    <div class="col-span-full">
        <label for="local_ocorrencia" class="block text-sm font-medium text-gray-700">Observação do Local da
            Ocorrência</label>
        <textarea
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="local_ocorrencia" rows="3" {{ $bloquear ? 'disabled' : '' }}>{{ old('local_ocorrencia', $sinistro->local_ocorrencia ?? '') }}</textarea>
    </div>

</div>

<hr class="mt-10 mb-10">

<h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações da
    Ocorrência
</h3>

<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

    <div class="col-span-full">
        <label for="descricao_ocorrencia" class="block text-sm font-medium text-gray-700">Descrição da
            Ocorrência</label>
        <textarea
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="descricao_ocorrencia" rows="3" {{ $bloquear ? 'disabled' : '' }}>{{ old('descricao_ocorrencia', $sinistro->descricao_ocorrencia ?? '') }}</textarea>
    </div>

    <div class="col-span-full">
        <label for="observacao_ocorrencia" class="block text-sm font-medium text-gray-700">Descrição da Ocorrência
            pelo Motorista</label>
        <textarea
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="observacao_ocorrencia" rows="3" {{ $bloquear ? 'disabled' : '' }}>{{ old('observacao_ocorrencia', $sinistro->observacao_ocorrencia ?? '') }}</textarea>
    </div>

</div>

<hr class="mt-10 mb-10">

<h3 class="font-medium text-gray-800 mb-10 uppercase ">Histórico do Registro
</h3>

<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center align-items-center">

    <div class="col-span-2">
        <label for="temp_data_evento" class="block text-sm font-medium text-gray-700">Data do Evento</label>
        <input
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            type="date" name="temp_data_evento" {{ $bloquear ? 'disabled' : '' }}>
    </div>

    @php
        $situacao = [
            [
                'label' => 'Abertura processo sinistro',
                'value' => 'Abertura processo sinistro',
            ],
            ['label' => 'Vistoria realizada', 'value' => 'Vistoria realizada'],
            ['label' => 'Fase de orçamento', 'value' => 'Fase de orçamento'],
            ['label' => 'Orçamento aprovado', 'value' => 'Orçamento aprovado'],
            [
                'label' => 'Serviço em andamento',
                'value' => 'Serviço em andamento',
            ],
            ['label' => 'Finalizado', 'value' => 'Finalizado'],
            [
                'label' => 'Processo de ressarcimento',
                'value' => 'Processo de ressarcimento',
            ],
            ['label' => 'Em atraso', 'value' => 'Em atraso'],
            [
                'label' => 'Envio departamento responsável',
                'value' => 'Envio departamento responsável',
            ],
        ];
    @endphp
    <div class="col-span-2">
        <x-forms.smart-select name="temp_descricao_situacao" :options="$situacao" label="Situação" :selected="old('temp_descricao_situacao', $sinistro->temp_descricao_situacao ?? '')"
            asyncSearch="false" disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-3">
        <label for="temp_observacao" class="block text-sm font-medium text-gray-700">Observação</label>
        <textarea
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            name="temp_observacao" rows="3" {{ $bloquear ? 'disabled' : '' }}>{{ old('temp_observacao', $sinistro->temp_observacao ?? '') }}</textarea>
    </div>

    <div class="flex justify-center items-center">
        <button type="button" onclick="adicionarHistorico()" {{ $bloquear ? 'disabled' : '' }}
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Adicionar Histórico
        </button>
    </div>

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="historicos" id="historicos_json"
        value="{{ isset($historicosinistro) ? json_encode($historicosinistro) : '[]' }}">

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data do Evento
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Situação
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Observação
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="tabelaHistoricoBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>
