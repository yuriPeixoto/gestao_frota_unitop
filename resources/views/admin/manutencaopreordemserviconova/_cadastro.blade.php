@php
    $isCreate = Route::currentRouteName() === 'admin.manutencaopreordemserviconova.create';
@endphp
<!-- Cabeçalho -->
<div class="mb-6 rounded-lg bg-gray-50 p-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

        <!-- Campos para Pré-O.S -->
        <div>
            <label for="id_motorista" class="block text-sm font-medium text-gray-700">Motorista</label>
            <select id="id_motorista" name="id_motorista"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                onchange="getTelefoneMotorista()">
                <option value="">Selecione...</option>
                @foreach ($selectOptions['motoristas'] as $motora)
                    <option value="{{ $motora['value'] }}"
                        {{ old('id_motorista', $preOrdemFinalizada->id_motorista ?? '') == $motora['value'] ? 'selected' : '' }}>
                        {{ $motora['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="telefone_motorista" class="block text-sm font-medium text-gray-700">Telefone/Celular do
                Motorista</label>
            <input type="tel" id="telefone_motorista" name="telefone_motorista" maxlength="15"
                oninput="mascaraDeTelefone('telefone_motorista')" placeholder="(11) 99999-9999"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('telefone_motorista', $preOrdemFinalizada->telefone_motorista ?? '') }}">
        </div>

        <div>
            <label for="id_status" class="block text-sm font-medium text-gray-700">Status</label>

            <select id="id_status" name="id_status" disabled
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                <option value="">Selecione...</option>

                @foreach ($selectOptions['statusPreOs'] as $status)
                    <option value="{{ $status['value'] }}"
                        @if (isset($preOrdemFinalizada->id_status)) {{ old('id_status', $preOrdemFinalizada->id_status) == $status['value'] ? 'selected' : '' }}
                    @else
                    {{ $status['value'] == 1 ? 'selected' : '' }} @endif>
                        {{ $status['label'] }}
                    </option>
                @endforeach
            </select>

            <!-- Campo oculto para garantir que o valor seja enviado -->
            <input type="hidden" name="id_status"
                value="{{ isset($preOrdemFinalizada->id_status) ? old('id_status', $preOrdemFinalizada->id_status) : 1 }}">
        </div>

        <div>
            <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
            <select id="id_filial" name="id_filial"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($selectOptions['filiais'] as $filial)
                    <option value="{{ $filial->value }}"
                        {{ old('id_filial', $preOrdemFinalizada->id_filial ?? '') == $filial->value ? 'selected' : '' }}>
                        {{ $filial->label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="id_veiculo" class="block text-sm font-medium text-gray-700">Placa</label>
            <select id="id_veiculo" name="id_veiculo"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                onchange="getInfoVeiculos()">
                <option value="">Selecione...</option>
                @foreach ($selectOptions['placa'] as $veiculo)
                    <option value="{{ $veiculo->value }}"
                        {{ old('id_veiculo', $preOrdemFinalizada->id_veiculo ?? '') == $veiculo->value ? 'selected' : '' }}>
                        {{ $veiculo->label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
            <input type="text" id="categoria" name="categoria" disabled
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label for="modelo" class="block text-sm font-medium text-gray-700">Modelo</label>
            <input type="text" id="modelo" name="modelo" disabled
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label for="tipoEquipamento" class="block text-sm font-medium text-gray-700">Tipo
                Equipamento</label>
            <input type="text" id="tipoEquipamento" name="tipoEquipamento" disabled
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-6">
        <div>
            <label for="id_departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
            <select id="id_departamento" name="id_departamento"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($selectOptions['departamentos'] as $departamento)
                    <option value="{{ $departamento->value }}"
                        {{ old('id_departamento', $preOrdemFinalizada->id_departamento ?? '') == $departamento->value ? 'selected' : '' }}>
                        {{ $departamento->label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <!-- Mostra o nome do recepcionista -->
            <x-forms.input name="usuario" type="text" label="Recepcionista" :value="old('id_recepcionista', $preOrdemFinalizada->recepcionista->name ?? auth()->user()->name)" readonly />

            <!-- Envia o ID real do recepcionista -->
            <input type="hidden" name="id_recepcionista"
                value="{{ old('id_recepcionista', $preOrdemFinalizada->id_recepcionista ?? auth()->user()->id) }}" />
        </div>

        <div>
            @php
                $situacaoPreOs = [
                    'ALTA' => 'ALTA',
                    'MÉDIA' => 'MÉDIA',
                    'BAIXA' => 'BAIXA',
                ];
            @endphp
            <label for="situacao_pre_os" class="block text-sm font-medium text-gray-700">Prioridade Pré
                O.S</label>
            <select id="situacao_pre_os" name="situacao_pre_os"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                readonly>
                <option value="">Selecione...</option>
                @foreach ($situacaoPreOs as $situacao)
                    <option value="{{ $situacao }}"
                        {{ old('situacao_pre_os', $preOrdemFinalizada->situacao_pre_os ?? '') == $situacao ? 'selected' : '' }}>
                        {{ $situacao }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="id_grupo_resolvedor" class="block text-sm font-medium text-gray-700">Grupo Resolvedor</label>
            <select id="id_grupo_resolvedor" name="id_grupo_resolvedor"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Selecione...</option>
                @foreach ($selectOptions['grupoResolvedor'] as $resolvedor)
                    <option value="{{ $resolvedor['value'] }}"
                        {{ old('id_grupo_resolvedor', $preOrdemFinalizada->id_grupo_resolvedor ?? '') == $resolvedor['value']
                            ? 'selected'
                            : '' }}>
                        {{ $resolvedor['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <!-- Mostra o nome -->
            <x-forms.input name="recepcionista" type="text" label="Usuário" :value="old('id_usuario', $preOrdemFinalizada->usuario->name ?? auth()->user()->name)" readonly />

            <!-- Envia o ID real -->
            <input type="hidden" name="id_usuario"
                value="{{ old('id_usuario', $preOrdemFinalizada->id_usuario ?? auth()->user()->id) }}" />
        </div>

        <div>
            <label for="local_execucao" class="block text-sm font-medium text-gray-700">Local
                Execução</label>
            <select id="local_execucao" name="local_execucao"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                readonly>
                <option value="">Selecione...</option>
                <option value="INTERNO"
                    {{ old('local_execucao', $preOrdemFinalizada->local_execucao ?? '') == 'INTERNO' ? 'selected' : '' }}>
                    INTERNO</option>
                <option value="EXTERNO"
                    {{ old('local_execucao', $preOrdemFinalizada->local_execucao ?? '') == 'EXTERNO' ? 'selected' : '' }}>
                    EXTERNO</option>
            </select>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="km_realizacao" class="block text-sm font-medium text-gray-700">Km
                Realização</label>
            <input type="number" id="km_realizacao" name="km_realizacao" step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('km_realizacao', $preOrdemFinalizada->km_realizacao ?? '') }}">
        </div>

        <div>
            <label for="horimetro_tk" class="block text-sm font-medium text-gray-700">Horímetro</label>
            <input type="number" id="horimetro_tk" name="horimetro_tk" step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('horimetro_tk', $preOrdemFinalizada->horimetro_tk ?? '') }}">
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4">
        <div>
            <label for="descricao_reclamacao" class="block text-sm font-medium text-gray-700">Descrição
                Reclamação</label>
            <textarea id="descricao_reclamacao" name="descricao_reclamacao" {{ $isCreate ? '' : 'readonly' }}
                class="mt-1 block w-full rounded-md border-gray-300 {{ $isCreate ? '' : 'bg-gray-200' }} shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('descricao_reclamacao', $preOrdemFinalizada->descricao_reclamacao ?? '') }}</textarea>
        </div>

        <div>
            <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
            <textarea id="observacoes" name="observacoes"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacoes', $preOrdemFinalizada->observacoes ?? '') }}</textarea>
        </div>

        @if (!$isCreate)
            <h1 class="text-lg font-medium mt-6">HISTÓRICO</h1>
            <hr class="border-t border-gray-300">
        @endif

        <!-- Campo hidden para armazenar os históricos -->
        <input type="hidden" name="vManutencaoVencidas" id="vManutencaoVencidas_json"
            value="{{ json_encode($vManutencaoVencidas ?? []) }}">

        @if (isset($vManutencaoVencidas) && count($vManutencaoVencidas) > 0)
            <div class="col-span-full mt-6 overflow-auto">
                <table class="tabelaVManutencaoVencidasBody min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="font-small px-6 py-3 text-left text-xs uppercase tracking-wider text-gray-500">
                                Placa
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm/6 font-light uppercase tracking-wider text-gray-500">
                                Código Manutenção
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm/6 font-light uppercase tracking-wider text-gray-500">
                                Descrição Manutenção
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm/6 font-light uppercase tracking-wider text-gray-500">
                                Tipo Manutenção
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm/6 font-light uppercase tracking-wider text-gray-500">
                                Km Ultima Manutenção
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                Data Ultima Manutenção
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                KM Frequência
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                Km Atual
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                Km Vencer
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                Data Vencer
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider text-gray-500">
                                Dias Vencidos
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tabelaVManutencaoVencidasBody" class="divide-y divide-gray-200 bg-white">
                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
