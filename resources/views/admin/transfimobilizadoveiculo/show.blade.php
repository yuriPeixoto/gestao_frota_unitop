<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Transferência de Imobilizado #{{ $transferencia->id_transferencia_imobilizado_veiculo }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.transfimobilizadoveiculo.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>

                {{-- @if ($transferencia->status == 2)
                    @can('transferencia-imobilizado-juridico')
                        <a href="{{ route('admin.transfimobilizadoveiculo.edit', $transferencia->id_transferencia_imobilizado_veiculo) }}"
                            class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            Editar Transferencia
                        </a>
                    @endcan
                @endif

                @if ($transferencia->status == 2 && $transferencia->tipo == 'COMODATO')
                    @can('transferencia-imobilizado-juridico')
                        <button onclick="enviarParaJuridico()"
                            class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            Enviar para Jurídico
                        </button>
                    @endcan
                @endif

                @if ($transferencia->status == 4)
                    @can('transferencia-imobilizado-patrimonio')
                        <button onclick="enviarParaPatrimonio()"
                            class="inline-flex items-center rounded-md bg-purple-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700">
                            Enviar para Patrimônio
                        </button>
                    @endcan
                @endif --}}

                {{-- @if ($transferencia->status == 5)
                    @can('transferencia-imobilizado-filial')
                        <button onclick="enviarParaFilial()"
                            class="inline-flex items-center rounded-md bg-orange-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-700">
                            Finalizar
                        </button>
                    @endcan
                @endif

                @if ($transferencia->status == 6)
                    @can('transferencia-imobilizado-concluir')
                        <button onclick="concluirTransferencia()"
                            class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            Finalizar Transferência
                        </button>
                    @endcan
                @endif

                @if (!in_array($transferencia->status, [7, 10]))
                    @can('transferencia-imobilizado-reprovar')
                        <button onclick="abrirModalReprovacao()"
                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700">
                            Cancelar
                        </button>
                    @endcan
                @endif --}}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensagens de Sucesso/Erro -->
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Status da Transferência - Fluxo Completo -->
            <div class="mb-6 overflow-hidden bg-white shadow sm:rounded-lg">
                <div class="bg-gray-50 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Status da Transferência - Fluxo Completo</h3>
                </div>
                <div class="flex flex-col items-center justify-center p-8">
                    @php
                        $log = $transferencia->log;
                        $statusAtual = (string) $transferencia->status;
                        $etapas = [
                            '2' => [
                                'label' => 'Tráfego',
                                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                'data' => $log && $log->situacao_trafego ? $log->data_trafego : null,
                                'concluido' => $log && $log->situacao_trafego,
                            ],
                            // Jurídico só aparece se tipo for COMODATO
                            '3' =>
                                $transferencia->tipo === 'COMODATO'
                                    ? [
                                        'label' => 'Jurídico',
                                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'data' => $log && $log->situacao_juridico ? $log->data_juridico : null,
                                        'concluido' => $log && $log->situacao_juridico,
                                    ]
                                    : null,
                            '4' => [
                                'label' => 'Frota',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'data' => $log && $log->situacao_frota ? $log->data_frota : null,
                                'concluido' => $log && $log->situacao_frota,
                            ],
                            '5' => [
                                'label' => 'Patrimônio',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'data' => $log && $log->situacao_patrimonio ? $log->data_patrimonio : null,
                                'concluido' => $log && $log->situacao_patrimonio,
                            ],
                            // Reprovado só aparece se houver reprovação
                            '8' =>
                                $log && $log->situacao_reprova
                                    ? [
                                        'label' => 'Reprovado',
                                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'data' => $log ? $log->data_reprova : null,
                                        'erro' => true,
                                        'concluido' => true,
                                    ]
                                    : null,
                            '9' => [
                                'label' => 'Filial',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'data' => $log && $log->situacao_pendente ? $log->data_pendente : null,
                                'concluido' => $log && $log->situacao_pendente,
                            ],
                            '10' => [
                                'label' => 'Concluido',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                'data' => $log && $log->situacao_pendente ? $log->data_pendente : null,
                                'concluido' => $log && $log->situacao_pendente,
                            ],
                        ];
                        // Remove os nulos do array (jurídico e reprovado se não aplicável)
                        $etapas = array_filter($etapas);
                        $fluxoCompleto = [];
                        foreach ($etapas as $key => $etapa) {
                            // Marcar como ativo todas as etapas até o status atual (inclusive)
                            $etapa['ativo'] = (int) $key <= (int) $statusAtual;
                            $fluxoCompleto[$key] = $etapa;
                        }
                    @endphp

                    <div class="flex items-center space-x-2 overflow-x-auto pb-4">
                        @foreach ($fluxoCompleto as $key => $status)
                            @if (!$loop->first)
                                <!-- Linha conectora -->
                                <div
                                    class="{{ $status['ativo'] ? 'bg-green-500' : 'bg-gray-200' }} h-1 w-12 flex-shrink-0">
                                </div>
                            @endif
                            <!-- Step -->
                            <div class="flex flex-shrink-0 flex-col items-center">
                                <div
                                    class="{{ $status['ativo']
                                        ? (isset($status['erro'])
                                            ? 'bg-red-500 text-white'
                                            : 'bg-green-500 text-white')
                                        : 'bg-gray-200 text-gray-400' }} flex h-10 w-10 items-center justify-center rounded-full">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $status['icon'] }}"></path>
                                    </svg>
                                </div>
                                <span class="mt-2 max-w-20 text-center text-xs">{{ $status['label'] }}</span>
                                @if (isset($status['concluido']) && $status['concluido'] && $status['data'])
                                    <span class="mt-1 text-center text-xs text-gray-500">
                                        {{ $status['data'] instanceof \Carbon\Carbon ? $status['data']->format('d/m H:i') : $status['data'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Status atual em destaque -->
                    <div class="mt-6 flex items-center justify-center">
                        <span
                            class="{{ $transferencia->getStatusClassAttribute() }} inline-flex rounded-full px-4 py-2 text-sm font-semibold leading-5">
                            {{ $transferencia->departamentoTransferencia->departamento }}
                        </span>
                    </div>

                    @if ($transferencia->log && $transferencia->log->data_reprova)
                        <div class="mt-4 text-center">
                            <div class="inline-flex items-center rounded-full bg-red-100 px-4 py-2">
                                <span class="text-sm font-medium text-red-800">
                                    Reprovada por {{ $transferencia->user->name ?? 'Sistema' }} em
                                    {{ $transferencia->log && $transferencia->log->data_reprova ? $transferencia->log->data_reprova->format('d/m/Y H:i') : 'Data não disponível' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">
                    <div class="space-y-6">
                        <!-- Informações da Transferência -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Coluna 1 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações da Transferência
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                N° da Transferência:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->id_transferencia_imobilizado_veiculo }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Solicitante:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->user->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Departamento:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->departamento->descricao_departamento ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Data da Transferência:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->data_inclusao ? $transferencia->data_inclusao->format('d/m/Y H:i') : 'N/A' }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Coluna 2 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações Adicionais
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Origem:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->filialOrigem->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial de Destino:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->filialDestino->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        @if ($transferencia->tipo == 'DOMOTADO')
                                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                                <dt class="text-sm font-medium text-gray-500">
                                                    Fornecedor Preferencial:
                                                </dt>
                                                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                    {{ $transferencia->fornecedor->nome_fornecedor ?? 'Não especificado' }}
                                                </dd>
                                            </div>
                                        @endif
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Tipo de Transferência:
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $transferencia->tipo ?? 'Não especificado' }}
                                            </dd>
                                        </div>
                                        @if ($transferencia->checklist_id)
                                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                                <dt class="text-sm font-medium text-gray-500">
                                                    Checklist:
                                                </dt>
                                                <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $transferencia->checklist_id }}"
                                                    class="{{ $transferencia->checklist->status == 'completed' ? ' text-green-800' : 'text-yellow-800' }} leading-2 inline-flex rounded-full px-2 py-1 text-sm font-semibold">
                                                    {{ $transferencia->checklist_id }}
                                                </a>
                                            </div>
                                        @endif
                                        @if ($transferencia->checklist_devo)
                                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                                <dt class="text-sm font-medium text-gray-500">
                                                    Checklist Devolutivo:
                                                </dt>
                                                <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $transferencia->checklist_devo_id }}"
                                                    class="{{ $transferencia->checklistDevo->status == 'completed' ? 'text-green-800' : 'text-yellow-800' }} leading-2 inline-flex rounded-full px-2 py-1 text-sm font-semibold">
                                                    {{ $transferencia->checklist_devo }}
                                                </a>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        @if ($transferencia->observacao)
                            <div class="mb-6 rounded-lg bg-white shadow">
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-medium text-gray-900">Observações</h3>
                                </div>
                                <div class="p-6">
                                    <div class="rounded-lg bg-gray-50 p-4">
                                        <h4 class="mb-2 text-sm font-medium text-gray-900">Texto</h4>
                                        <p class="text-sm text-gray-700">{{ $transferencia->observacao }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($transferencia->justificativa)
                            <div class="mb-6 rounded-lg bg-white shadow">
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-medium text-gray-900">Observação do Aprovador</h3>
                                </div>
                                <div class="p-6">
                                    <div class="rounded-lg bg-red-50 p-4">
                                        <p class="text-sm text-red-700">{{ $transferencia->justificativa }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Informações do Veículo/Equipamento -->
                        @if ($transferencia->veiculo || $transferencia->tipoEquipamento)
                            <div class="mb-6 rounded-lg bg-white shadow">
                                <div class="border-b border-gray-200 px-6 py-4">
                                    <h3 class="text-lg font-medium text-gray-900">Informações do Veículo/Equipamento
                                    </h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    TIPO EQUIPAMENTO</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    PLACA</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                    {{ $transferencia->tipoEquipamento->descricao_tipo . ' Eixo: ' . ($transferencia->tipoEquipamento->numero_eixos ?? '-') }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                    {{ $transferencia->veiculo->placa ?? '-' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Histórico -->
                        <div class="rounded-lg bg-white shadow">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-medium text-gray-900">Histórico</h3>
                            </div>
                            <div class="p-6">
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        @php
                                            $historico = [];

                                            // Transferência criada
                                            $historico[] = [
                                                'icon' => 'plus',
                                                'icon_bg' => 'bg-blue-500',
                                                'title' => 'Transferência Criada',
                                                'user' => $transferencia->user->name ?? 'Sistema',
                                                'date' => $transferencia->data_inclusao
                                                    ? $transferencia->data_inclusao->format('d/m/Y H:i')
                                                    : 'Data não disponível',
                                                'description' => 'Transferência de imobilizado criada',
                                            ];

                                            if ($transferencia->log) {
                                                if (!empty($transferencia->log->situacao_trafego)) {
                                                    $historico[] = [
                                                        'icon' => 'clock',
                                                        'icon_bg' => 'bg-yellow-500',
                                                        'title' => 'Tráfego',
                                                        'user' => $transferencia->log->userTrafego->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_trafego
                                                            ? $transferencia->log->data_trafego->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Etapa de tráfego concluída.',
                                                    ];
                                                }
                                                // Jurídico só aparece se for COMODATO
                                                if (
                                                    !empty($transferencia->log->situacao_juridico) &&
                                                    $transferencia->tipo === 'COMODATO'
                                                ) {
                                                    $historico[] = [
                                                        'icon' => 'check',
                                                        'icon_bg' => 'bg-blue-500',
                                                        'title' => 'Jurídico',
                                                        'user' => $transferencia->log->userJuridico->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_juridico
                                                            ? $transferencia->log->data_juridico->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Etapa do jurídico concluída.',
                                                    ];
                                                }
                                                // Frota
                                                if (!empty($transferencia->log->situacao_frota)) {
                                                    $historico[] = [
                                                        'icon' => 'truck',
                                                        'icon_bg' => 'bg-green-500',
                                                        'title' => 'Frota',
                                                        'user' => $transferencia->log->userFrota->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_frota
                                                            ? $transferencia->log->data_frota->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Etapa da frota concluída.',
                                                    ];
                                                }
                                                // Patrimônio
                                                if (!empty($transferencia->log->situacao_patrimonio)) {
                                                    $historico[] = [
                                                        'icon' => 'clipboard-check',
                                                        'icon_bg' => 'bg-purple-500',
                                                        'title' => 'Patrimônio',
                                                        'user' =>
                                                            $transferencia->log->userPatrimonio->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_patrimonio
                                                            ? $transferencia->log->data_patrimonio->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Etapa do patrimônio concluída.',
                                                    ];
                                                }
                                                // Pendente
                                                if (!empty($transferencia->log->situacao_pendente)) {
                                                    $historico[] = [
                                                        'icon' => 'clipboard-check',
                                                        'icon_bg' => 'bg-purple-500',
                                                        'title' => 'Filial',
                                                        'user' => $transferencia->log->userPendente->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_pendente
                                                            ? $transferencia->log->data_pendente->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Etapa da filial concluída.',
                                                    ];
                                                }
                                                // Reprovado só aparece se houver reprovação
                                                if (!empty($transferencia->log->situacao_reprova)) {
                                                    $historico[] = [
                                                        'icon' => 'x-circle',
                                                        'icon_bg' => 'bg-red-500',
                                                        'title' => 'Reprovado',
                                                        'user' => $transferencia->user->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_reprova
                                                            ? $transferencia->log->data_reprova->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Transferência reprovada.',
                                                    ];
                                                }
                                                // Concluído
                                                if (!empty($transferencia->log->situacao_pendente)) {
                                                    $historico[] = [
                                                        'icon' => 'check-circle',
                                                        'icon_bg' => 'bg-green-500',
                                                        'title' => 'Concluído',
                                                        'user' => $transferencia->log->userPendente->name ?? 'Sistema',
                                                        'date' => $transferencia->log->data_pendente
                                                            ? $transferencia->log->data_pendente->format('d/m/Y H:i')
                                                            : 'Data não disponível',
                                                        'description' => 'Transferência concluída.',
                                                    ];
                                                }
                                            }
                                        @endphp

                                        @foreach ($historico as $index => $item)
                                            <li>
                                                <div class="relative pb-8">
                                                    @if ($index < count($historico) - 1)
                                                        <span
                                                            class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                                                            aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3">
                                                        <div>
                                                            <span
                                                                class="{{ $item['icon_bg'] }} flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white">
                                                                @if ($item['icon'] == 'plus')
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @elseif ($item['icon'] == 'clock')
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @elseif ($item['icon'] == 'check')
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @elseif ($item['icon'] == 'truck')
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path
                                                                            d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                                        <path
                                                                            d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z" />
                                                                    </svg>
                                                                @elseif ($item['icon'] == 'clipboard-check')
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                                        <path fill-rule="evenodd"
                                                                            d="M4 5a2 2 0 012-2v1a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 012 2v6h-3a4 4 0 00-4 4v4H6a2 2 0 01-2-2V5zm8 5a1 1 0 011-1h3a1 1 0 110 2h-3a1 1 0 01-1-1z"
                                                                            clip-rule="evenodd" />
                                                                        <path
                                                                            d="M15 13a1 1 0 100-2 1 1 0 000 2z M15 17a1 1 0 100-2 1 1 0 000 2z" />
                                                                    </svg>
                                                                @else
                                                                    <svg class="h-5 w-5 text-white"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd"
                                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="min-w-0 flex-1 pt-1.5">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $item['user'] }}
                                                                    <span
                                                                        class="font-normal text-blue-600">{{ $item['title'] }}</span>
                                                                </p>
                                                                <p class="mt-0.5 text-sm text-gray-500">
                                                                    {{ $item['date'] ?? 'Data não disponível' }}
                                                                </p>
                                                            </div>
                                                            <div class="mt-2 text-sm text-gray-700">
                                                                <p>{{ $item['description'] }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reprovação -->
    <div id="modalReprovacao" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <form id="formReprovacao" action="{{ route('admin.transfimobilizadoveiculo.reprovar') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="id"
                        value="{{ $transferencia->id_transferencia_imobilizado_veiculo }}">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                    Reprovar Transferência
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Tem certeza que deseja reprovar esta transferência? Esta ação não poderá ser
                                        desfeita.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <label for="justificativa" class="block text-sm font-medium text-gray-700">
                                        Justificativa da Reprovação *
                                    </label>
                                    <textarea id="justificativa" name="justificativa" rows="4" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Descreva o motivo da reprovação..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            Reprovar
                        </button>
                        <button type="button" onclick="fecharModal('modalReprovacao')"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Fechar modal clicando fora dele
        document.querySelectorAll('.fixed.inset-0').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModal(this.parentElement.id);
                }
            });
        });

        // Submissão de formulários com AJAX
        function submitAction(action, transferId) {
            if (confirm('Tem certeza que deseja executar esta ação?')) {
                fetch(`/admin/transfimobilizadoveiculo/${transferId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: transferId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recarregar a página para mostrar as mudanças
                            window.location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao executar a ação');
                    });
            }
        }

        // Submissão do formulário de reprovação
        document.getElementById('formReprovacao').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fecharModal('modalReprovacao');
                        window.location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao reprovar transferência');
                });
        });
    </script>

    @push('scripts')
        <script>
            // Funções para as ações da transferência
            function enviarParaJuridico() {
                if (confirm('Tem certeza que deseja enviar esta transferência para o Jurídico?')) {
                    fetch(`{{ route('admin.transfimobilizadoveiculo.juridico', $transferencia->id_transferencia_imobilizado_veiculo) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Checklist não preenchido.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Checklist não preenchido.');
                        });
                }
            }

            function enviarParaFrota() {
                if (confirm('Tem certeza que deseja enviar esta transferência para a Frota?')) {
                    fetch(`{{ route('admin.transfimobilizadoveiculo.frota', $transferencia->id_transferencia_imobilizado_veiculo) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erro ao processar a Transferência.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao processar a Transferência.');
                        });
                }
            }

            function enviarParaPatrimonio() {
                if (confirm('Tem certeza que deseja enviar esta transferência para o Patrimônio?')) {
                    fetch(`{{ route('admin.transfimobilizadoveiculo.patrimonio', $transferencia->id_transferencia_imobilizado_veiculo) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Checklist não preenchido.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Checklist não preenchido.');
                        });
                }
            }

            function enviarParaFilial() {
                if (confirm('Tem certeza que deseja finalizar esta etapa?')) {
                    fetch(`{{ route('admin.transfimobilizadoveiculo.filial', $transferencia->id_transferencia_imobilizado_veiculo) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Checklist não preenchido.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Checklist não preenchido.');
                        });
                }
            }

            function concluirTransferencia() {
                if (confirm('Tem certeza que deseja concluir esta transferência?')) {
                    fetch(`{{ route('admin.transfimobilizadoveiculo.concluir', $transferencia->id_transferencia_imobilizado_veiculo) }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Checklist não preenchido.');
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Checklist não preenchido.');
                        });
                }
            }

            function abrirModalReprovacao() {
                document.getElementById('modalReprovacao').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function fecharModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            // Fechar modal com ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.fixed.inset-0.z-50').forEach(modal => {
                        modal.classList.add('hidden');
                    });
                    document.body.classList.remove('overflow-hidden');
                }
            });

            // Fechar modal clicando fora
            document.addEventListener('DOMContentLoaded', function() {
                const modals = ['modalReprovacao'];

                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.addEventListener('click', function(event) {
                            if (event.target === modal) {
                                fecharModal(modalId);
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
