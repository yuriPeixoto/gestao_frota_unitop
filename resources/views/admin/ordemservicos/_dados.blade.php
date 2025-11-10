@php
    $departamento = strtolower(auth()->user()->departamento->descricao_departamento ?? '');
    $permitidos = ['administrativo', 'processos e qualidade', 'gerencia'];
    $isUpdate = request()->routeIs('admin.ordemservicos.edit');

    // habilitado se:
    // - estamos na rota de criação → sempre true
    // - estamos na rota de update → somente se o departamento estiver na lista
    // - durante a atualização da OS o campo situacao_tipo_os_corretiva não é enviado para o backend caso o departamento do
    // usuário não esteja na lista
    $habilitado =
        request()->routeIs('admin.ordemservicos.create') || ($isUpdate && in_array($departamento, $permitidos));
@endphp
@if ($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="grid grid-cols-6 gap-3 mb-6">

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="investimento" value="1" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 1 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="investimento"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 rounded-lg cursor-pointer border-gray-200 hover:bg-green-50 peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-blue-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                </svg>
                <span>Investimento</span>
            </div>
        </label>
    </div>

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="sinistro" value="2" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 2 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="sinistro"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-red-50 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
                <span>Sinistro</span>
            </div>
        </label>
    </div>

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="socorro" value="3" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 3 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="socorro"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <x-icons.help-truck class="w-5 h-5 mb-1" />
                <span>Socorro</span>
            </div>
        </label>
    </div>

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="retorno" value="4" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 4 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="retorno"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-yellow-50 peer-checked:border-yellow-600 peer-checked:bg-yellow-50 peer-checked:text-yellow-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <x-icons.arrow-path-rounded-square class="w-5 h-5 mb-1" />
                <span>Retorno</span>
            </div>
        </label>
    </div>

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="programada" value="5" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 5 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="programada"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-purple-50 peer-checked:border-purple-600 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <x-icons.calendar class="w-5 h-5 mb-1" />
                <span>Programada</span>
            </div>
        </label>
    </div>

    <div class="relative">
        <input type="radio" name="situacao_tipo_os_corretiva" id="borracharia" value="6" class="sr-only peer"
            {{ old(
                'situacao_tipo_os_corretiva',
                isset($ordemServico) && $ordemServico->situacao_tipo_os_corretiva == 6 ? 'checked' : '',
            ) }}
            @if (!$habilitado) disabled @endif>
        <label for="borracharia"
            class="flex items-center justify-center p-3 text-sm font-medium bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-rose-50 peer-checked:border-rose-600 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition-all duration-200">
            <div class="flex flex-col items-center">
                <x-icons.tire class="w-5 h-5 mb-1" />
                <span>Borracharia</span>
            </div>
        </label>
    </div>
</div>

<div class="grid grid-cols-4 md:grid-cols-4 gap-2">
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

    {{-- prioridade_os --}}
    <div>
        @php
            $situacaoOS = [
                'Alta' => 'Alta',
                'Média' => 'Média',
                'Baixa' => 'Baixa',
            ];
        @endphp
        <label for="prioridade_os" class="block text-sm font-medium text-gray-700 mb-1">Prioridade:</label>
        <select name="prioridade_os" id="prioridade_os"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">Selecione...</option>
            @foreach ($situacaoOS as $key => $value)
                <option value="{{ $key }}"
                    {{ old('prioridade_os', $ordemServico->prioridade_os ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- id_tipo_ordem_servico --}}
    <div>
        <x-forms.smart-select name="id_tipo_ordem_servico" id="id_tipo_ordem_servico" label="Tipo Ordem Serviço"
            :options="$formOptions['tipoOrdemServico']" value="{{ old('id_tipo_ordem_servico', $ordemServico->id_tipo_ordem_servico ?? '') }}"
            disabled />
        <input type="hidden" name="id_tipo_ordem_servico" id="id_tipo_ordem_servico_hidden"
            value="{{ old('id_tipo_ordem_servico', $ordemServico->id_tipo_ordem_servico ?? '') }}">
    </div>

    {{-- id_status_ordem_servico --}}
    <div>
        <x-forms.smart-select name="id_status_ordem_servico" label="Situação Ordem de Serviço" :options="$formOptions['statusOrdemServico']"
            value="{{ old('id_status_ordem_servico', $ordemServico->id_status_ordem_servico ?? 1) }}" disabled />
        <input type="hidden" name="id_status_ordem_servico"
            value="{{ old('id_status_ordem_servico', $ordemServico->id_status_ordem_servico ?? 1) }}">
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
                    {{ old('local_manutencao', $ordemServico->local_manutencao ?? !$isUpdate ? 'INTERNO' : '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- id_filial --}}
    <div>
        <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial da Manutenção:</label>
        <select name="id_filial_manutencao" id="id_filial_manutencao"
            class="relative w-full flex items-center bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-[10px] text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Selecione...</option>
            @foreach ($formOptions['filial'] as $filialManutencao)
                <option value="{{ $filialManutencao['value'] }}"
                    {{ old('id_filial_manutencao', $ordemServico->id_filial_manutencao ?? GetterFilial()) ==
                    $filialManutencao['value']
                        ? 'selected'
                        : '' }}>
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
        @php
            $desativar = false;
            if (
                isset($ordemServico) &&
                isset($ordemServico->id_veiculo) &&
                (in_array(auth()->user()->id, [2, 269]) || auth()->user()->is_superuser)
            ) {
                $desativar = true;
            }
        @endphp
        <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..." :options="$veiculosFrequentes"
            :searchUrl="route('admin.api.veiculos.search')" :selected="old('id_veiculo', $ordemServico->veiculo->placa ?? '')" asyncSearch="true" :disabled="$desativar" />
    </div>

    {{-- chassis --}}
    <div>
        <label for="chassis" class="block text-sm font-medium text-gray-700">
            Chassis:
        </label>
        <input type="text" name="chassi" id="chassi" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-black shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
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
        <x-forms.button onclick="imprimirKm()">
            <x-icons.magnifying-glass />
            Histórico de KM
        </x-forms.button>
    </div>
</div>

{{-- horas_manutencao_tk --}}
<div class="grid grid-cols-3 md:grid-cols-3 gap-2 mt-2">
    <div>
        <label for="horas_manutencao_tk" class="block text-sm font-medium text-gray-700">
            Horas TK:
        </label>
        <input type="text" name="horas_manutencao_tk" id="horas_manutencao_tk"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            value="{{ old('horas_manutencao_tk', $ordemServico->horas_manutencao_tk ?? '') }}" />
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
            class="mt-1 block w-full text-black rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-2 gap-2 mt-2">
    {{-- observacao --}}
    <div>
        <label for="observacao" class="block text-sm font-medium text-gray-700">
            Observação Ordem de Serviço:
        </label>
        <textarea name="observacao" id="observacao" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $ordemServico->observacao ?? '' }}</textarea>
    </div>

    {{-- Relato Inicial --}}
    <div>
        <label for="relato_problema" class="block text-sm font-medium text-gray-700">
            Relato Problema Resolvido:
        </label>
        <textarea name="relato_problema" id="relato_problema" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $ordemServico->relato_problema ?? '' }}</textarea>
    </div>
</div>
