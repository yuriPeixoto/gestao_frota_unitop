<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Vida do Pneu - {{ $pneu->id_pneu }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 10px;
            color: #666;
        }
        
        .pneu-info {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #ddd;
        }
        
        .pneu-info-row {
            display: table-row;
        }
        
        .pneu-info-cell {
            display: table-cell;
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .pneu-info-cell:first-child {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 25%;
        }
        
        .timeline {
            margin-bottom: 20px;
        }
        
        .timeline h2 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .evento {
            margin-bottom: 15px;
            border-left: 3px solid #007bff;
            padding-left: 15px;
            page-break-inside: avoid;
        }
        
        .evento.entrada { border-left-color: #28a745; }
        .evento.aplicado { border-left-color: #007bff; }
        .evento.estoque { border-left-color: #6c757d; }
        .evento.manutencao { border-left-color: #fd7e14; }
        .evento.calibragem { border-left-color: #6f42c1; }
        .evento.transferencia { border-left-color: #e83e8c; }
        .evento.venda { border-left-color: #20c997; }
        .evento.descarte { border-left-color: #dc3545; }
        
        .evento-header {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .evento-data {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .evento-detalhes {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            margin-top: 8px;
        }
        
        .detalhe-item {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        
        .detalhe-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding-right: 10px;
        }
        
        .detalhe-valor {
            display: table-cell;
        }
        
        .resumo {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .resumo h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .resumo-grid {
            display: table;
            width: 100%;
        }
        
        .resumo-row {
            display: table-row;
        }
        
        .resumo-cell {
            display: table-cell;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            width: 12.5%;
        }
        
        .resumo-number {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .resumo-label {
            font-size: 9px;
            color: #666;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Histórico de Vida do Pneu</h1>
        <p>N° de Fogo: {{ $pneu->id_pneu }} | Gerado em: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Informações do Pneu -->
    <div class="pneu-info">
        <div class="pneu-info-row">
            <div class="pneu-info-cell">N° de Fogo:</div>
            <div class="pneu-info-cell">{{ $pneu->id_pneu }}</div>
        </div>
        
        @if($pneu->cod_antigo)
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Código Antigo:</div>
            <div class="pneu-info-cell">{{ $pneu->cod_antigo }}</div>
        </div>
        @endif
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Modelo:</div>
            <div class="pneu-info-cell">{{ $pneu->getModeloPneuFromHistorico()?->descricao_modelo ?? 'N/A' }}</div>
        </div>
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Status Atual:</div>
            <div class="pneu-info-cell">{{ $pneu->status_pneu }}</div>
        </div>
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Filial:</div>
            <div class="pneu-info-cell">{{ $pneu->filialPneu->name ?? 'N/A' }}</div>
        </div>
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Departamento:</div>
            <div class="pneu-info-cell">{{ $pneu->departamentoPneu->descricao_departamento ?? 'N/A' }}</div>
        </div>
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Data de Inclusão:</div>
            <div class="pneu-info-cell">{{ \Carbon\Carbon::parse($pneu->data_inclusao)->format('d/m/Y H:i') }}</div>
        </div>
        
        <div class="pneu-info-row">
            <div class="pneu-info-cell">Total de Eventos:</div>
            <div class="pneu-info-cell">{{ count($historico) }}</div>
        </div>
    </div>

    <!-- Timeline do Histórico -->
    <div class="timeline">
        <h2>Timeline de Movimentações</h2>
        
        @if(count($historico) > 0)
            @foreach($historico as $evento)
                @php
                    $eventoClass = match($evento['tipo']) {
                        'ENTRADA_SISTEMA' => 'entrada',
                        'APLICADO' => 'aplicado',
                        'ESTOQUE' => 'estoque', 
                        'MANUTENCAO', 'ENVIO_MANUTENCAO', 'RETORNO_MANUTENCAO' => 'manutencao',
                        'CALIBRAGEM' => 'calibragem',
                        'TRANSFERENCIA' => 'transferencia',
                        'VENDA' => 'venda',
                        'DESCARTE' => 'descarte',
                        default => 'aplicado'
                    };
                @endphp
                
                <div class="evento {{ $eventoClass }}">
                    <div class="evento-header">{{ $evento['descricao'] }}</div>
                    <div class="evento-data">{{ \Carbon\Carbon::parse($evento['data'])->format('d/m/Y H:i') }}</div>
                    
                    @if(!empty($evento['detalhes']))
                        <div class="evento-detalhes">
                            @foreach($evento['detalhes'] as $campo => $valor)
                                @if(!empty($valor))
                                    <div class="detalhe-item">
                                        <div class="detalhe-label">{{ ucfirst(str_replace('_', ' ', $campo)) }}:</div>
                                        <div class="detalhe-valor">
                                            @if($campo === 'valor_unitario' || $campo === 'valor_venda')
                                                R$ {{ number_format($valor, 2, ',', '.') }}
                                            @elseif($campo === 'km_rodados' && $valor > 0)
                                                {{ number_format($valor, 0, ',', '.') }} km
                                            @elseif($campo === 'diferenca')
                                                @if($valor > 0)
                                                    +{{ $valor }}
                                                @elseif($valor < 0)
                                                    {{ $valor }}
                                                @else
                                                    0
                                                @endif
                                            @elseif(str_contains($campo, 'data_') || str_ends_with($campo, '_data') || $campo === 'data_inclusao' || $campo === 'data_recebimento' || $campo === 'data_prevista_retorno')
                                                @php
                                                    try {
                                                        echo \Carbon\Carbon::parse($valor)->format('d/m/Y');
                                                    } catch (\Exception $e) {
                                                        echo $valor;
                                                    }
                                                @endphp
                                            @else
                                                {{ $valor }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p>Nenhum histórico encontrado para este pneu.</p>
        @endif
    </div>

    <!-- Resumo Estatístico -->
    @if(count($historico) > 0)
        <div class="resumo">
            <h2>Resumo de Atividades</h2>
            <div class="resumo-grid">
                @php
                    $resumo = collect($historico)->groupBy('tipo')->map->count();
                    $tiposCount = [
                        'ENTRADA_SISTEMA' => 'Entradas',
                        'APLICADO' => 'Aplicações',
                        'ESTOQUE' => 'Estoques',
                        'MANUTENCAO' => 'Manutenções',
                        'CALIBRAGEM' => 'Calibragens',
                        'TRANSFERENCIA' => 'Transferências',
                        'VENDA' => 'Vendas',
                        'DESCARTE' => 'Descartes'
                    ];
                @endphp
                
                <div class="resumo-row">
                    @foreach($tiposCount as $tipo => $label)
                        <div class="resumo-cell">
                            <div class="resumo-number">{{ $resumo[$tipo] ?? 0 }}</div>
                            <div class="resumo-label">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <p>Relatório gerado automaticamente pelo Sistema de Gestão de Frota</p>
    </div>
</body>
</html>