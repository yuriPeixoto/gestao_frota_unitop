
<x-app-layout>
    <style>
        .timeline-link {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .timeline-link:hover {
            text-decoration: underline;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            position: relative;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 30px;
        }

        /* Timeline Horizontal Styles */
        .timelinetransf {
            position: relative;
            padding: 40px 20px;
            overflow-x: auto;
        }

        /* Linha horizontal conectora */
        .timelinetransf::before {
            content: '';
            position: absolute;
            left: 28px;
            right: 0;
            top: 0;
            height: auto;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to right, #e0e0e0, #bdbdbd);
            border-radius: 2px;
        }

        /* Container dos itens em linha */
        .timelinetransf-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 30px;
            width: 100%;
            max-width: 500px; /* limita o tamanho máximo */

        }

        .timelinetransf-item {
            position: relative;
            flex: 1;
            min-width: 200px;
            max-width: 300px;
        }

        .timelinetransf-marker {
            position: absolute;
            left: 20px%;
            top: 20px;
            transform: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .timelinetransf-marker.completed {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .timelinetransf-marker.current {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            animation: pulse 2s infinite;
        }

        .timelinetransf-marker.pending {
            background: #e0e0e0;
        }

        .timelinetransf-marker.rejected {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(33, 150, 243, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(33, 150, 243, 0); }
            100% { box-shadow: 0 0 0 0 rgba(33, 150, 243, 0); }
        }

        .timelinetransf-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid #e0e0e0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-top: none;
            transition: all 0.3s ease;
            margin-top: 0;
            margin-left: 60px;
            width: 100%;
            max-width: 500px; /* limita o tamanho máximo */

        }

        .timelinetransf-content.completed {
            border-left-color: #4CAF50;
            background: linear-gradient(135deg, #f8fff8, #ffffff);
        }

        .timelinetransf-content.current {
            border-left-color: #2196F3;
            background: linear-gradient(135deg, #f0f8ff, #ffffff);
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(33, 150, 243, 0.2);
        }

        .timelinetransf-content.rejected {
            border-left-color: #f44336;
            background: linear-gradient(135deg, #fff8f8, #ffffff);
        }

        .timelinetransf-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            text-align: left;
        }

        .timelinetransf-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 8px;
            text-align: left;
        }

        .timelinetransf-user {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 8px;
            text-align: left;
        }

        .timelinetransf-anexo {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 8px;
            text-align: left;
        }

        .timelinetransf-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 auto;
            display: block;
            text-align: left;
            width: fit-content;
        }

        .status-aprovado {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-pendente {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-reprovado {
            background: #ffebee;
            color: #c62828;
        }

        .transfer-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .transfer-info h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
            text-align: left;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .timelinetransf-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .timelinetransf::before {
                left: 30px;
                top: 0;
                bottom: 0;
                width: 4px;
                height: auto;
            }
            
            .timelinetransf-marker {
                left: 20px;
                top: 20px;
                transform: none;
            }
            
            .timelinetransf-title, .timelinetransf-date, .timelinetransf-user {
                text-align: left;
            }
            
            .timelinetransf-status {
                display: inline-block;
                text-align: center;
            }
        }
    </style>
    
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de Devolução Veículos em Imobilizado ') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.devolucaoimobilizadoveiculo.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Devolução Veículos
                </a>

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <x-bladewind::notification />


                <!-- Search Form -->
                @include('admin.devolucaoimobilizadoveiculo._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.devolucaoimobilizadoveiculo._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
@include('admin.devolucaoimobilizadoveiculo._scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const problematicContainers = document.querySelectorAll('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg');
        
        problematicContainers.forEach(container => {
        const smartSelects = container.querySelectorAll('[x-data*="simpleSelect"]');
        
        smartSelects.forEach(smartSelect => {
            smartSelect.classList.add('smart-select-container');
            
            const dropdownButton = smartSelect.querySelector('[x-ref="button"]');
            
            if (dropdownButton) {
            dropdownButton.addEventListener('click', function() {
                container.classList.toggle('dropdown-active');
            });
            
            document.addEventListener('click', function(event) {
                if (!smartSelect.contains(event.target)) {
                container.classList.remove('dropdown-active');
                }
            });
            }
        });
        });
    });
</script>

<script>
    // Dados de exemplo - substitua pela chamada AJAX real
    const transferenciasData = @json($transferencias);
    
    // Verificar se os dados foram carregados corretamente
    if (!transferenciasData) {
        console.error('Dados de Devoluçãos não foram carregados!');
    }
    
    // Variável para controlar o modal atual
    let current_modal = null;

    function openTimeline(transferId) {
        
        const data = transferenciasData[transferId];
        console.log('Abrindo timeline para a Devolução:', transferId, data);
        if (!data) {
            console.error('Dados não encontrados para o ID:', transferId);
            alert('Dados não encontrados para esta Devolução!');
            return;
        }

        // Verificar se os dados essenciais existem
        if (!data.log) {
            console.error('Dados de log não encontrados para o ID:', transferId);
            alert('Dados de log não encontrados para esta Devolução!');
            return;
        }

        // Preencher informações da Devolução
        const transferInfo = document.getElementById('transferInfo');
        if (!transferInfo) {
            console.error('Elemento transferInfo não encontrado!');
            return;
        }

        transferInfo.innerHTML = `
            <h4>Informações da Transferência #${data.id_devolucao_imobilizado_veiculo || 'N/A'}</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="info-row">
                    <div>
                        <span class="info-label">Data de Inclusão:</span>
                    </div>
                    <div>
                        <span class="info-value">${formatDate(data.data_inclusao)}</span>
                    </div>
                </div>
                ${data.data_inicio ? `<div class="info-row">
                    <div>
                        <span class="info-label">Data de Inicio:</span>
                    </div>
                    
                    <div>
                        <span class="info-value">${formatDate(data.data_inicio)}</span>
                    </div>
                </div>` : ''}
                ${data.data_fim ? `<div class="info-row">
                    <div>
                        <span class="info-label">Data do Fim:</span>
                    </div>
                    <div>
                        <span class="info-value">${formatDate(data.data_fim)}</span>
                    </div>
                </div>` : ''}
                <div class="info-row">
                    <div>
                        <span class="info-label">Tipo:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.tipo || 'N/A'}</span>
                    </div>
                </div>
                ${data.filial_origem ? `<div class="info-row">
                    <div>
                        <span class="info-label">Filial Origem:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.filial_origem}</span>
                    </div>
                </div>` : ''}
                ${data.filial_destino ? `<div class="info-row">
                    <div>
                        <span class="info-label">Filial Destino:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.filial_destino}</span>
                    </div>
                </div>` : ''}
                ${data.fornecedor ? `<div class="info-row">
                    <div>
                        <span class="info-label">Fornecedor:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.fornecedor}</span>
                    </div>
                </div>` : ''}
                ${data.departamento ? `<div class="info-row">
                    <div>
                        <span class="info-label">Departamento:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.departamento}</span>
                    </div>
                </div>` : ''}
                ${data.idVeiculo ? `<div class="info-row">
                    <div>
                        <span class="info-label">Veiculo:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.veiculo}</span>
                    </div>
                </div>` : ''}
            </div>
            <div class="grid grid-cols-1 md:grid-cols-1">
                <div class="info-row">
                    <div>
                        <span class="info-label">Observação:</span>
                    </div>
                    <div>
                        <span class="info-value">${data.observacao || 'Sem observação'}</span>
                    </div>
                </div>
            </div>
        `;

        // Construir timeline
        const timeline = buildTimeline(data.log, data.tipo);
        const timelineContainer = document.getElementById('timelineContainer');
        if (!timelineContainer) {
            console.error('Elemento timelineContainer não encontrado!');
            return;
        }
        
        timelineContainer.innerHTML = timeline;

        // Mostrar modal
        const modal = document.getElementById('timelineModal');
        if (!modal) {
            console.error('Modal não encontrado!');
            return;
        }
        
        // Definir o modal atual
        current_modal = modal;
        
        // Aplicar estilos para mostrar o modal
        modal.style.cssText = `
            display: block !important; 
            visibility: visible !important; 
            opacity: 1 !important; 
            z-index: 99999 !important; 
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            pointer-events: auto !important;
        `;
        
        // Adicionar classe para animação
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    function buildTimeline(log, tipo) {
        let html = '';
        
        // Verificar se log existe e tem as propriedades necessárias
        if (!log) {
            console.error('Log não encontrado');
            return '<div class="error">Dados de timeline não disponíveis</div>';
        }
        
        // Verificar se há reprovação para interromper o fluxo
        const isReprovado = log.data_reprova;
        
        // 1. Solicitação - sempre aparece como aprovado
        html += createTimelineItem(
            'Solicitação',
            log.data_solicitante,
            log.usuario_solicitante,
            'APROVADO',
            'Solicitação criada pelo usuário',
            'completed'
        );

        // 2. Tráfego - sempre aparece
        if (log.data_trafego) {
            html += createTimelineItem(
                'Tráfego',
                log.data_trafego,
                log.usuario_trafego,
                log.situacao_trafego,
                'Análise do departamento de tráfego',
                getStatusClass(log.situacao_trafego)
            );
        } else if (!isReprovado) {
            html += createTimelineItem(
                'Tráfego',
                null,
                null,
                'PENDENTE',
                'Aguardando análise do departamento de tráfego',
                'current'
            );
        }

        // 3. Jurídico - sempre aparece, mas comportamento diferente por tipo
        if (tipo === 'COMODATO') {
            // Para COMODATO: Jurídico é processado normalmente
            if (log.data_juridico) {
                html += createTimelineItem(
                    'Jurídico',
                    log.data_juridico,
                    log.usuario_juridico,
                    log.situacao_juridico,
                    'Análise do departamento jurídico',
                    getStatusClass(log.situacao_juridico),
                    '',
                    log.anexo_documento,
                    log.anexo_checklist
                );
            } else if (log.situacao_trafego === 'APROVADO' && !isReprovado) {
                html += createTimelineItem(
                    'Jurídico',
                    null,
                    null,
                    'PENDENTE',
                    'Aguardando análise do departamento jurídico',
                    'current'
                );
            } else if (!isReprovado) {
                html += createTimelineItem(
                    'Jurídico',
                    null,
                    null,
                    'PENDENTE',
                    'Aguardando aprovação do tráfego',
                    'pending'
                );
            }
        }

        // 4. Frota - sempre aparece (exceto se reprovado)
        if (log.data_frota) {
            html += createTimelineItem(
                'Frota',
                log.data_frota,
                log.usuario_frota,
                log.situacao_frota,
                'Análise do departamento de frota',
                getStatusClass(log.situacao_frota)
            );
        } else if (!isReprovado) {
            if (tipo === 'COMODATO') {
                // Para COMODATO: Frota vem após Jurídico
                if (log.situacao_juridico === 'APROVADO') {
                    html += createTimelineItem(
                        'Frota',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento de frota',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Frota',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação do jurídico',
                        'pending'
                    );
                }
            } else if (tipo === 'FILIAL') {
                // Para FILIAL: Frota vem após Tráfego (ignora Jurídico)
                if (log.situacao_trafego === 'APROVADO') {
                    html += createTimelineItem(
                        'Frota',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento de frota',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Frota',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação do tráfego',
                        'pending'
                    );
                }
            }
        }

        // 5. Patrimônio - sempre aparece (exceto se reprovado)
        if (log.data_patrimonio) {
            html += createTimelineItem(
                'Patrimônio',
                log.data_patrimonio,
                log.usuario_patrimonio,
                log.situacao_patrimonio,
                'Análise do departamento de patrimônio',
                getStatusClass(log.situacao_patrimonio)
            );
        } else if (!isReprovado) {
            if (tipo === 'COMODATO') {
                // Para COMODATO: Patrimônio vem após Frota
                if (log.situacao_frota === 'APROVADO') {
                    html += createTimelineItem(
                        'Patrimônio',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento de patrimônio',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Patrimônio',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação da frota',
                        'pending'
                    );
                }
            } else if (tipo === 'FILIAL') {
                // Para FILIAL: Patrimônio vem após Frota
                if (log.situacao_frota === 'APROVADO') {
                    html += createTimelineItem(
                        'Patrimônio',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento de patrimônio',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Patrimônio',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação da frota',
                        'pending'
                    );
                }
            }
        }

        // 6. Pendente - sempre aparece (exceto se reprovado)
        if (log.data_pendente) {
            html += createTimelineItem(
                'Pendente',
                log.data_pendente,
                log.usuario_pendente,
                log.situacao_pendente,
                'Análise do departamento para Concluir o processo',
                getStatusClass(log.situacao_pendente)
            );
        } else if (!isReprovado) {
            if (tipo === 'COMODATO') {
                // Para COMODATO: Pendente vem após Frota
                if (log.situacao_frota === 'APROVADO') {
                    html += createTimelineItem(
                        'Pendente',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento para Concluir o processo',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Pendente',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação da frota',
                        'pending'
                    );
                }
            } else if (tipo === 'FILIAL') {
                // Para FILIAL: Pendente vem após Frota
                if (log.situacao_frota === 'APROVADO') {
                    html += createTimelineItem(
                        'Pendente',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando análise do departamento para Concluir o processo',
                        'current'
                    );
                } else {
                    html += createTimelineItem(
                        'Pendente',
                        null,
                        null,
                        'PENDENTE',
                        'Aguardando aprovação da frota',
                        'pending'
                    );
                }
            }
        }

        // Reprovação (se houver) 
        if (isReprovado) {
            html += createTimelineItem(
                'Reprovado',
                log.data_reprova,
                log.usuario_reprova,
                'REPROVADO',  // Status correto
                'Processo reprovado',
                'rejected',
                log.justiticativa_reprova  // Adicionar justificativa
            );
        }

        return html;
    }

    function createTimelineItem(title, date, user, status, description, statusClass, justificativa = null, anexoDocumento = null, anexoChecklist = null) {
        const formattedDate = date ? formatDateTime(date) : '';
        const userInfo = user ? user : '';
        const statusText = status || 'PENDENTE';
        
        // Sanitizar strings para evitar problemas com valores null/undefined
        const safeTitle = title || 'Etapa';
        const safeDescription = description || 'Sem descrição';
        const safeAnexoDocumento = anexoDocumento || '';
        const safeJustificativa = justificativa || '';

        return `
            <div class="timelinetransf-item">
                <div class="timelinetransf-marker ${statusClass}"></div>
                <div class="timelinetransf-content ${statusClass}">
                    <div class="timelinetransf-title">${safeTitle}</div>
                    ${formattedDate ? `<div class="timelinetransf-date">📅 ${formattedDate}</div>` : ''}
                    ${userInfo ? `<div class="timelinetransf-user">👤 ${userInfo}</div>` : ''}
                    ${safeAnexoDocumento ? `<div class="timelinetransf-anexo">🗒️ <span class="text-sm text-gray-500">Documento atual: </span><a href="/storage/${safeAnexoDocumento}" target="_blank"class="text-sm text-indigo-600 hover:text-indigo-800">Visualizar</a></div>` : ''}
                    <div class="timelinetransf-status status-${statusText.toLowerCase()}">${statusText}</div>
                    <p style="margin-top: 10px; color: #666; font-size: 0.9em;">${safeDescription}</p>
                    ${safeJustificativa ? `<div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;"><strong>Justificativa:</strong><br><span style="color: #856404;">${safeJustificativa}</span></div>` : ''}
                </div>
            </div>
        `;
    }

    function getStatusClass(situacao) {
        if (!situacao) return 'pending';
        switch (situacao.toUpperCase()) {
            case 'APROVADO': return 'completed';
            case 'REPROVADO': return 'rejected';
            case 'IGNORADO': return 'ignored';
            default: return 'pending';
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'Data não informada';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return 'Data inválida';
            }
            return date.toLocaleDateString('pt-BR');
        } catch (error) {
            console.error('Erro ao formatar data:', error);
            return 'Data inválida';
        }
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'Data não informada';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return 'Data inválida';
            }
            return date.toLocaleString('pt-BR');
        } catch (error) {
            console.error('Erro ao formatar data/hora:', error);
            return 'Data inválida';
        }
    }

    function closeModal() {
        const modal = document.getElementById('timelineModal');
        if (modal) {
            modal.style.cssText = 'display: none !important;';
            modal.classList.remove('show');
            // Limpar a referência do modal atual
            current_modal = null;
        }
    }

    // Fechar modal clicando fora dele
    window.onclick = function(event) {
        const modal = document.getElementById('timelineModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Fechar modal com ESC - versão melhorada
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            // Verificar se há um modal aberto antes de tentar fechar
            if (current_modal && current_modal.style.display !== 'none') {
                closeModal();
            }
        }
    });

    // Alternativa mais robusta para o evento ESC
    document.addEventListener('keyup', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            const modal = document.getElementById('timelineModal');
            if (modal && modal.style.display !== 'none') {
                closeModal();
            }
        }
    });


</script>

@endpush
</x-app-layout>