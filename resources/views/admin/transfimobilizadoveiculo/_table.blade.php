<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Transferência<br>Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Tipo Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Usuario</x-tables.head-cell>
            <x-tables.head-cell>Data Inicio</x-tables.head-cell>
            <x-tables.head-cell>Data Fim</x-tables.head-cell>
            <x-tables.head-cell>situação</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($transferenciaImobilizadoVeiculo as $index => $transferenciaImobilizadoVeiculos)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->data_inclusao?->format('d/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->data_alteracao?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->getTipoEquipamentoEixo() ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->veiculo->placa ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->user->name ?? $transferenciaImobilizadoVeiculos->id_usuario }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->data_inicio?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferenciaImobilizadoVeiculos->data_fim?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                    <x-tables.cell>
                        {{ $transferenciaImobilizadoVeiculos->departamentoTransferencia->departamento }}
                    </x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.transfimobilizadoveiculo.show', $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo) }}"
                                class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </a>

                            @if ($transferenciaImobilizadoVeiculos->status == '2')
                                <a href="{{ route('admin.transfimobilizadoveiculo.edit', $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo) }}"
                                    class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                            @endif

                            @if ($transferenciaImobilizadoVeiculos->checklist_id)
                                <a onclick="showModal(`visualizar-checklist-{{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}`)"
                                    class="text-indigo-600 hover:text-indigo-900" title="Checklist">
                                    <x-icons.clipboard-document-check class="h-3.5 w-3.5" />
                                </a>
                            @endif

                            {{-- Agora usa os dados processados no controller --}}
                            @if ($transferenciaImobilizadoVeiculos->status_config['processo'])
                                <a onclick="abrirModal({{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}, '{{ $transferenciaImobilizadoVeiculos->veiculo->placa ?? '' }}')"
                                    class="group cursor-pointer p-1.5"
                                    title="{{ $transferenciaImobilizadoVeiculos->status_config['title'] }}">
                                    {{-- Apenas o ícone de check, sem círculo de fundo --}}
                                    <svg class="{{ $transferenciaImobilizadoVeiculos->status_config['classes'] }} h-5 w-5 transition-colors duration-200 group-hover:text-green-900"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </a>
                            @endif

                            @if ($transferenciaImobilizadoVeiculos->status == '9')
                                <a href="{{ route('admin.veiculos.edit', $transferenciaImobilizadoVeiculos->id_veiculo) }}"
                                    class="group text-blue-600 hover:text-blue-900" title="Veiculo">
                                    <svg class="h-5 w-5 text-blue-600 group-hover:text-blue-900" fill="currentColor"
                                        stroke="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M32 160C32 124.7 60.7 96 96 96L384 96C419.3 96 448 124.7 448 160L448 192L498.7 192C515.7 192 532 198.7 544 210.7L589.3 256C601.3 268 608 284.3 608 301.3L608 448C608 483.3 579.3 512 544 512L540.7 512C530.3 548.9 496.3 576 456 576C415.7 576 381.8 548.9 371.3 512L268.7 512C258.3 548.9 224.3 576 184 576C143.7 576 109.8 548.9 99.3 512L96 512C60.7 512 32 483.3 32 448L32 160zM544 352L544 301.3L498.7 256L448 256L448 352L544 352zM224 488C224 465.9 206.1 448 184 448C161.9 448 144 465.9 144 488C144 510.1 161.9 528 184 528C206.1 528 224 510.1 224 488zM456 528C478.1 528 496 510.1 496 488C496 465.9 478.1 448 456 448C433.9 448 416 465.9 416 488C416 510.1 433.9 528 456 528z" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </x-tables.cell>

                    {{-- Resto dos modals permanecem iguais, só mudando as variáveis --}}
                    <x-bladewind.modal
                        name="mudar-status-{{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}"
                        cancel_button_label="Cancelar" ok_button_label="" type="info" title="Confirmar aprovação"
                        size="big">
                        <b>Atenção:</b> essa ação transferirá o veículo <b class="title"></b> ?
                        <br>
                        <b>Esta ação não pode ser desfeita.</b>
                        <br>
                        <x-bladewind::button type="button" color="green"
                            onclick="onVerificarSituacao(
                        {{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }},
                        {{ $transferenciaImobilizadoVeiculos->status }},
                        '{{ $transferenciaImobilizadoVeiculos->veiculo->placa ?? '' }}',
                        '{{ $transferenciaImobilizadoVeiculos->filialDestino->name ?? '' }}');
                        hideModal('mudar-status-{{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}'); "
                            class="me-2 mt-3 text-white">
                            Aprovar
                        </x-bladewind::button>
                    </x-bladewind.modal>

                    {{-- Modal para mostrar os checklists --}}
                    @if ($transferenciaImobilizadoVeiculos->checklist_id)
                        <x-bladewind.modal
                            name="visualizar-checklist-{{ $transferenciaImobilizadoVeiculos->id_transferencia_imobilizado_veiculo }}"
                            cancel_button_label="Fechar" ok_button_label="" type="info" title="Visualizar Checklist"
                            size="big">

                            <div class="space-y-4">
                                @php
                                    $finalizado =
                                        ($transferenciaImobilizadoVeiculos->checklist->status ?? '') == 'completed' &&
                                        ($transferenciaImobilizadoVeiculos->checklistDevo->status ?? '') == 'completed';

                                    $finalizadoChecklist =
                                        ($transferenciaImobilizadoVeiculos->checklist->status ?? '') == 'completed';
                                    $finalizadoChecklistDevo =
                                        ($transferenciaImobilizadoVeiculos->checklistDevo->status ?? '') == 'completed';
                                @endphp

                                <!-- Informações do Status -->
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">
                                                <strong>Status:</strong>
                                                <span
                                                    class="{{ $finalizado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-full px-2 py-1 text-xs">
                                                    {{ $finalizado ? 'Finalizado' : 'Em Andamento' }}
                                                </span>
                                            </p>
                                        </div>
                                        @if ($finalizado)
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <div class="flex items-center">
                                                <div
                                                    class="h-4 w-4 animate-spin rounded-full border-b-2 border-yellow-500">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- checklist frota -->
                                @if ($transferenciaImobilizadoVeiculos->checklist_id)
                                    <div class="rounded-lg border p-4">
                                        <div class="mb-3 flex items-center justify-between">
                                            <h3 class="flex items-center text-lg font-medium text-gray-900">
                                                <svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Checklist
                                            </h3>
                                            <span
                                                class="{{ $finalizadoChecklist ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full px-2 py-1 text-xs">
                                                {{ $finalizadoChecklist ? 'Finalizado' : 'Em Andamento' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $transferenciaImobilizadoVeiculos->checklist_id }}"
                                                target="_blank"
                                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-2 text-sm font-medium leading-4 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                                Visualizar Checklist
                                            </a>
                                        </div>

                                        @if ($transferenciaImobilizadoVeiculos->checklist_id)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Número:</strong>
                                                {{ $transferenciaImobilizadoVeiculos->checklist_id }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                <!-- Checklist Devolutiva -->
                                @if ($transferenciaImobilizadoVeiculos->checklist_devo)
                                    <div class="rounded-lg border p-4">
                                        <div class="mb-3 flex items-center justify-between">
                                            <h3 class="flex items-center text-lg font-medium text-gray-900">
                                                <svg class="mr-2 h-5 w-5 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Checklist
                                            </h3>
                                            <span
                                                class="{{ $finalizadoChecklistDevo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full px-2 py-1 text-xs">
                                                {{ $finalizadoChecklistDevo ? 'Finalizado' : 'Em Andamento' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-3">
                                            <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $transferenciaImobilizadoVeiculos->checklist_id }}"
                                                target="_blank"
                                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-3 py-2 text-sm font-medium leading-4 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                                Visualizar Checklist de Recebimento
                                            </a>
                                        </div>

                                        @if ($transferenciaImobilizadoVeiculos->checklist_devo)
                                            <p class="mt-2 text-sm text-gray-600">
                                                <strong>Número:</strong>
                                                {{ $transferenciaImobilizadoVeiculos->checklist_devo }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                <!-- Informações adicionais -->
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                <strong>Informação:</strong>
                                                @if ($finalizado)
                                                    Processo finalizado. Todos os documentos estão disponíveis para
                                                    visualização.
                                                @else
                                                    Processo em andamento. Os documentos serão atualizados conforme o
                                                    progresso.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </x-bladewind.modal>
                    @endif
                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse

        </x-tables.body>

        <!-- Modal -->
        <div id="timelineModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Timeline da Transferência</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="transferInfo" class="transfer-info">
                        <!-- Informações da transferência serão inseridas aqui -->
                    </div>
                    <div class="timelinetransf">
                        <div id="timelineContainer" class="timelinetransf-container">

                            <!-- Timeline será inserida aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </x-tables.table>

    <div class="mt-4">
        {{ $transferenciaImobilizadoVeiculo->links() }}
    </div>

</div>
