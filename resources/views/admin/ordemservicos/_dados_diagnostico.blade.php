<div class="grid grid-cols-5 md:grid-cols-5 gap-2">
    {{-- data_abertura --}}
    <div>
        <label for="data_abertura" class="block text-sm font-medium text-gray-700">
            Data Abertura:
        </label>
        <input type="datetime-local" name="data_abertura" id="data_abertura"
            class="mt-1 block w-full rounded-md border-gray-300 bg-white-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('data_abertura', $ordemServico->data_abertura ?? '') }}" />
    </div>

    {{-- data_previsao_saida --}}
    <div>
        <label for="data_previsao_saida" class="block text-sm font-medium text-gray-700">
            Previsão de Saída:
        </label>
        <input type="datetime-local" name="data_previsao_saida" id="data_previsao_saida"
            class="mt-1 block w-full rounded-md border-gray-300 bg-white-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('data_previsao_saida', $ordemServico->data_previsao_saida ?? '') }}" />
    </div>

    {{-- data_inclusao --}}
    <div>
        <label for="data_inclusao" class="block text-sm font-medium text-gray-700">
            Data Inclusão:
        </label>
        <input type="datetime-local" name="data_inclusao" id="data_inclusao" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('data_inclusao', $ordemServico->data_inclusao ?? '') }}" />
    </div>

    {{-- id_tipo_ordem_servico --}}
    <div>
        <label for="id_tipo_ordem_servico" class="block text-sm font-medium text-gray-700 mb-1">Tipo Ordem
            Serviço:</label>

        <select name="id_tipo_ordem_servico" id="id_tipo_ordem_servico" readonly
            class="relative w-full flex items-center bg-gray-200 border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

            <option value="">Selecione...</option>
            @foreach ($formOptions['tipoOrdemServico'] as $tipoOrdemServico)
                @php
                    // Verifica se $ordemServico existe e se o valor atual é 3 - diagnostico
                    $isSelected =
                        !isset($ordemServico) && $tipoOrdemServico['value'] == 3
                            ? 3
                            : (isset($ordemServico)
                                ? $ordemServico->id_tipo_ordem_servico
                                : null);
                @endphp
                <option value="{{ $tipoOrdemServico['value'] }}"
                    {{ old('id_tipo_ordem_servico', $isSelected == $tipoOrdemServico['value'] ? 'selected' : '') }}>
                    {{ $tipoOrdemServico['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- id_status_ordem_servico --}}
    <div>
        <label for="id_status_ordem_servico" class="block text-sm font-medium text-gray-700 mb-1">Situação Ordem de
            Serviço:</label>

        <select name="id_status_ordem_servico" id="id_status_ordem_servico"
            class="relative w-full flex items-center bg-gray-200 border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Selecione...</option>
            @foreach ($formOptions['statusOrdemServico'] as $statusOrdemServico)
                @php
                    // Prioridade: old() > valor do banco > valor padrão (1)
                    $selectedValue =
                        old('id_status_ordem_servico') ??
                        (isset($ordemServico) ? $ordemServico->id_status_ordem_servico : 1);
                @endphp
                <option value="{{ $statusOrdemServico['value'] }}"
                    {{ $selectedValue == $statusOrdemServico['value'] ? 'selected' : '' }}>
                    {{ $statusOrdemServico['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- local_manutencao --}}
    <div>
        @php
            $localManutencao = [
                'INTERNO' => 'INTERNO',
                'EXTERNO' => 'EXTERNO',
            ];
        @endphp
        <label for="local_manutencao" class="block text-sm font-medium text-gray-700 mb-1">Local Manutenção:</label>
        <select name="local_manutencao" id="local_manutencao"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">Selecione...</option>
            @foreach ($localManutencao as $key => $value)
                <option value="{{ $key }}"
                    {{ old('local_manutencao', isset($ordemServico) && $ordemServico->local_manutencao == $key ? 'selected' : '') }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- id_filial --}}
    <div>
        <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial da Manutenção:</label>

        <select name="id_filial_manutencao" id="id_filial"
            class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Selecione...</option>
            @foreach ($formOptions['filial'] as $filialManutencao)
                @php
                    $isSelected = auth()->user()->filial_id ?? (isset($ordemServico) ? $ordemServico->id_filial : 1);
                @endphp
                <option value="{{ $filialManutencao['value'] }}"
                    {{ old('id_filial_manutencao', $isSelected == $filialManutencao['value'] ? 'selected' : '') }}>
                    {{ $filialManutencao['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- id_motorista --}}
    <div class="col-span-2">
        <x-forms.smart-select name="id_motorista" label="Motorista" placeholder="Selecione o motorista..."
            :options="$motoristasFrequentes" :searchUrl="route('admin.api.pessoal.search')" :selected="old('id_motorista', $ordemServico->id_motorista ?? '')" asyncSearch="false" />
    </div>

    {{-- telefone_motorista --}}
    <div>
        <label for="telefone_motorista" class="block text-sm font-medium text-gray-700">
            Telefone/Celular Motorista:
        </label>
        <input type="text" name="telefone_motorista" id="telefone_motorista"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('telefone_motorista', $ordemServico->telefone_motorista ?? '') }}" />
    </div>


    {{-- id_veiculo --}}
    <div>
        <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..." :options="$veiculosFrequentes"
            :searchUrl="route('admin.api.veiculos.search')" :selected="old('id_veiculo', $ordemServico->id_veiculo ?? '')" asyncSearch="true" />
    </div>

    {{-- chassis --}}
    <div>
        <label for="chassis" class="block text-sm font-medium text-gray-700">
            Chassis:
        </label>
        <input type="text" name="chassi" id="chassi" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
    </div>

    {{-- km_atual --}}
    <div>
        <label for="km_atual" class="block text-sm font-medium text-gray-700">
            KM Atual:
        </label>
        <input type="text" name="km_atual" id="km_atual"
            {{ !isset($ordemServico) ? 'onblur="validaKMAtual()"' : '' }}
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('km_atual', $ordemServico->km_atual ?? '') }}" />
    </div>

    <div class="flex items-center mt-6">
        <button type="button" name="historico_km" id="historico_km"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <div class="flex items-center">
                <x-icons.magnifying-glass />
                Histórico de KM
            </div>
        </button>
    </div>
</div>

{{-- horas_manutencao_tk --}}
<div class="grid grid-cols-3 md:grid-cols-3 gap-2">
    <div>
        <label for="horas_manutencao_tk" class="block text-sm font-medium text-gray-700 mb-1">
            Horas TK:
        </label>
        <input type="text" name="horas_manutencao_tk" id="horas_manutencao_tk"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('km_atual', $ordemServico->horas_manutencao_tk ?? '') }}" />
    </div>

    {{-- id_departamento --}}
    <div>
        <x-forms.smart-select name="id_departamento" label="Departamento" placeholder="Selecione o departamento..."
            :options="$formOptions['departamento']" asyncSearch="false" :selected="old('id_departamento', $ordemServico->id_departamento ?? '')" :disabled="true" />
    </div>

    {{-- filial veiculo --}}
    <div>
        <label for="id_filial_veiculo" class="block text-sm font-medium text-gray-700 mb-1">Filial do Veiculo:</label>

        <input type="text" name="id_filial_veiculo" id="id_filial_veiculo" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />

    </div>

    {{-- observacao --}}
    <div class="col-span-3">
        <label for="observacao" class="block text-sm font-medium text-gray-700">
            Observação Ordem de Serviço:
        </label>
        <textarea name="observacao" id="observacao" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $ordemServico->observacao ?? '') }}</textarea>
    </div>
</div>
