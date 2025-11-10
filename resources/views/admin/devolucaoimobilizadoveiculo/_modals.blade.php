{{-- Resto dos modals permanecem iguais, só mudando as variáveis --}}
<x-bladewind.modal
    name="mudar-status-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}"
    cancel_button_label="Cancelar" ok_button_label="" type="info" title="Confirmar aprovação" size="big">
    @if (($devolucaoImobilizadoVeiculos->status ?? '') == '5')
    <form id="form-aprovacao-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}">
        <div class="space-y-4">
            
            <!-- Campo Checklist - sempre obrigatório -->
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="checklist-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}"
                    name="checklist"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    required
                >
                <label 
                    for="checklist-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}" 
                    class="text-sm font-medium text-gray-700"
                >
                    <span class="text-red-500">*</span> Confirmo que o checklist foi verificado
                </label>
            </div>

            <!-- Campo Documento - apenas para COMODATO -->
            @if($devolucaoImobilizadoVeiculos->tipo === 'COMODATO')
                <div class="flex items-center space-x-3">
                    <input 
                        type="checkbox" 
                        id="documento-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}"
                        name="documento"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        required
                    >
                    <label 
                        for="documento-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}" 
                        class="text-sm font-medium text-gray-700"
                    >
                        <span class="text-red-500">*</span> Confirmo que a documentação foi verificada
                    </label>
                </div>
            @endif

            <!-- Informações do tipo -->
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    <strong>Tipo:</strong> {{ $devolucaoImobilizadoVeiculos->tipo }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    @if($devolucaoImobilizadoVeiculos->tipo === 'COMODATO')
                        Verificação obrigatória: Checklist e Documentação
                    @else
                        Verificação obrigatória: Checklist
                    @endif
                </p>
            </div>

        </div>
    </form>                        
    @else                        
    <b>Atenção:</b> essa ação devolverá o veículo <b class="title"></b> ?
    <br>
    <b>Esta ação não pode ser desfeita.</b>
    @endif
    <br>
    @if ((!$devolucaoImobilizadoVeiculos->id_ordem_servico) && ($devolucaoImobilizadoVeiculos->status ?? '') == '4')
        <x-bladewind::button type="button" color="yellow"
            onclick="onEmitirOrdemServico({{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}); 
                hideModal('mudar-status-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}')"
            class="mt-3 me-2 text-white">
            Emitir Ordem de Serviço
        </x-bladewind::button>
    @elseif ((!$devolucaoImobilizadoVeiculos->id_sinistro) && ($devolucaoImobilizadoVeiculos->status == '4'))
        <x-bladewind::button type="button" color="yellow"
            onclick="onEmitirSinistro({{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }})"
            class="mt-3 me-2 text-white">
            Emitir Sinistro
        </x-bladewind::button>
    @else
        <x-bladewind::button type="button" color="green"
            onclick="onVerificarSituacao({{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}, {{ $devolucaoImobilizadoVeiculos->status }}); 
            hideModal('mudar-status-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}'); "
            class="mt-3 me-2 text-white">
            Aprovar
        </x-bladewind::button>
    @endif
</x-bladewind.modal>

{{-- Modal para mostrar o sinistro e ordem de serviço --}}
<x-bladewind.modal
    name="visualizar-sinistro-os-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}"
    cancel_button_label="Fechar" 
    ok_button_label="" 
    type="info" 
    title="Visualizar Sinistro e O.S." 
    size="big">
    
    <div class="space-y-4">
        @php
            $finalizado = (($devolucaoImobilizadoVeiculos->sinistro->status ?? '') == 'Finalizada' && 
                        ($devolucaoImobilizadoVeiculos->ordemServico->id_status_ordem_servico ?? '') == 4);

            $finalizadoOs = (($devolucaoImobilizadoVeiculos->ordemServico->id_status_ordem_servico ?? '') == 4);

            $finalizadoSinistro = (($devolucaoImobilizadoVeiculos->sinistro->status ?? '') == 'Finalizada');
        @endphp

        <!-- Informações do Status -->
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">
                        <strong>Status:</strong> 
                        <span class="px-2 py-1 rounded-full text-xs {{ $finalizado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $finalizado ? 'Finalizado' : 'Em Andamento' }}
                        </span>
                    </p>
                </div>
                @if(($devolucaoImobilizadoVeiculos->sinistro->status ?? '') == 'Finalizada' && ($devolucaoImobilizadoVeiculos->ordemServico->id_status_ordem_servico ?? '') == 4)  
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                @else
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-yellow-500"></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sinistro - Só aparece se existir -->
        @if($devolucaoImobilizadoVeiculos->id_sinistro)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Sinistro
                    </h3>
                    <span class="px-2 py-1 text-xs rounded-full {{ $finalizadoSinistro ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $finalizadoSinistro ? 'Finalizado' : 'Em Andamento' }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.sinistros.edit', $devolucaoImobilizadoVeiculos->id_sinistro) }}" 
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visualizar Sinistro
                    </a>
                </div>
                
                @if($devolucaoImobilizadoVeiculos->id_sinistro)
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Número:</strong> {{ $devolucaoImobilizadoVeiculos->id_sinistro }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Ordem de Serviço -->
        @if($devolucaoImobilizadoVeiculos->id_ordem_servico)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        Ordem de Serviço
                    </h3>
                    <span class="px-2 py-1 text-xs rounded-full {{ $finalizadoOs ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                         {{ $finalizadoOs ? 'Finalizado' : 'Em Andamento' }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.ordemservicos.edit_diagnostico', $devolucaoImobilizadoVeiculos->id_ordem_servico) }}" 
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visualizar O.S.
                    </a>
                </div>
                
                @if($devolucaoImobilizadoVeiculos->id_ordem_servico)
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Número:</strong> {{ $devolucaoImobilizadoVeiculos->id_ordem_servico }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Caso não tenha sinistro emitido -->
        @if(!$devolucaoImobilizadoVeiculos->id_sinistro)
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sinistro não emitido</h3>
                <p class="mt-1 text-sm text-gray-500">
                    O sinistro ainda não foi gerado para este item.
                </p>
            </div>
        @endif

        {{-- {{ dd($devolucaoImobilizadoVeiculos->ordemServico->id_status_ordem_servico) }} --}}

        <!-- Informações adicionais -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informação:</strong> 
                        @if(($devolucaoImobilizadoVeiculos->sinistro->status ?? '') == 'Finalizada' && ($devolucaoImobilizadoVeiculos->ordemServico->id_status_ordem_servico ?? '') == 4)
                            Processo finalizado. Todos os documentos estão disponíveis para visualização.
                        @else
                            Processo em andamento. Os documentos serão atualizados conforme o progresso.
                        @endif
                    </p>
                </div>
            </div>
        </div>

    </div>
    
</x-bladewind.modal>

{{-- Modal para mostrar os checklists --}}
@if ($devolucaoImobilizadoVeiculos->checklist_id)                    
<x-bladewind.modal
    name="visualizar-checklist-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}"
    cancel_button_label="Fechar" 
    ok_button_label="" 
    type="info" 
    title="Visualizar Checklist" 
    size="big">
    
    <div class="space-y-4">
        @php
            $finalizado = (($devolucaoImobilizadoVeiculos->checklist->status ?? '') == 'completed' && ($devolucaoImobilizadoVeiculos->checklistDevo->status ?? '') == 'completed');

            $finalizadoChecklist = (($devolucaoImobilizadoVeiculos->checklist->status ?? '') == 'completed');
            $finalizadoChecklistDevo = (($devolucaoImobilizadoVeiculos->checklistDevo->status ?? '') == 'completed');
        @endphp

        <!-- Informações do Status -->
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">
                        <strong>Status:</strong> 
                        <span class="px-2 py-1 rounded-full text-xs {{ $finalizado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $finalizado ? 'Finalizado' : 'Em Andamento' }}
                        </span>
                    </p>
                </div>
                @if($finalizado)  
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                @else
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-yellow-500"></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- checklist frota -->
        @if($devolucaoImobilizadoVeiculos->checklist_id)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        Checklist
                    </h3>
                    <span class="px-2 py-1 text-xs rounded-full {{ $finalizadoChecklist ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $finalizadoChecklist ? 'Finalizado' : 'Em Andamento' }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $devolucaoImobilizadoVeiculos->checklist_id }}" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visualizar Checklist
                    </a>
                </div>
                
                @if($devolucaoImobilizadoVeiculos->checklist_id)
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Número:</strong> {{ $devolucaoImobilizadoVeiculos->checklist_id }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Checklist Devolutiva -->
        @if($devolucaoImobilizadoVeiculos->checklist_devo)
            <div class="border rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                        Checklist
                    </h3>
                    <span class="px-2 py-1 text-xs rounded-full {{ $finalizadoChecklistDevo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $finalizadoChecklistDevo ? 'Finalizado' : 'Em Andamento' }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-3">
                    <a href="https://lcarvalima.unitopconsultoria.com.br:8443/dashboards/checklist/checklist/{{ $devolucaoImobilizadoVeiculos->checklist_id }}" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visualizar Checklist Devolutiva
                    </a>
                </div>

                @if($devolucaoImobilizadoVeiculos->checklist_devo)
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Número:</strong> {{ $devolucaoImobilizadoVeiculos->checklist_devo }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Informações adicionais -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informação:</strong> 
                        @if($finalizado)
                            Processo finalizado. Todos os documentos estão disponíveis para visualização.
                        @else
                            Processo em andamento. Os documentos serão atualizados conforme o progresso.
                        @endif
                    </p>
                </div>
            </div>
        </div>

    </div>
    
</x-bladewind.modal>
@endif