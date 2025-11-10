<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Venda - Requisição #{{ $requisicao->id_requisicao_pneu }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-section h3 {
            background-color: #f5f5f5;
            padding: 8px 12px;
            margin: 0 0 15px 0;
            border-left: 4px solid #333;
            font-size: 14px;
            font-weight: bold;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .totals {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .totals-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .total-item {
            text-align: center;
        }
        
        .total-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .total-value {
            font-size: 16px;
            color: #333;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 10px;
            color: #666;
        }
        
        .observacoes {
            margin-top: 20px;
        }
        
        .observacoes textarea {
            width: 100%;
            min-height: 80px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            resize: none;
        }
        
        @media print {
            body {
                margin: 0;
                font-size: 11px;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DOCUMENTO DE VENDA DE PNEUS</h1>
        <h2>Requisição #{{ $requisicao->id_requisicao_pneu }}</h2>
    </div>

    <!-- Informações da Requisição -->
    <div class="info-section">
        <h3>Informações da Requisição</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Código:</span>
                <span class="info-value">{{ $requisicao->id_requisicao_pneu }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Data Inclusão:</span>
                <span class="info-value">{{ format_date($requisicao->data_inclusao, 'd/m/Y H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Situação:</span>
                <span class="info-value">{{ $requisicao->situacao === 'FINALIZADA' ? 'BAIXADA' : $requisicao->situacao }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Filial:</span>
                <span class="info-value">{{ $requisicao->filial->name ?? 'Não encontrada' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Usuário Solicitante:</span>
                <span class="info-value">{{ $requisicao->usuarioSolicitante->name ?? 'Não encontrado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Usuário Estoque:</span>
                <span class="info-value">{{ $requisicao->usuarioEstoque->name ?? 'Não definido' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Terceiro:</span>
                <span class="info-value">{{ $requisicao->terceiro->nome_fornecedor ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Transferência Entre Filiais:</span>
                <span class="info-value">{{ $requisicao->transferencia_entre_filiais ? 'Sim' : 'Não' }}</span>
            </div>
        </div>
    </div>

    <!-- Resumo por Modelo -->
    <div class="info-section">
        <h3>Resumo por Modelo</h3>
        <table>
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Quantidade</th>
                    <th>Quantidade Baixa</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requisicao->requisicaoPneuModelos as $modelo)
                <tr>
                    <td>{{ $modelo->modeloPneu->descricao_modelo ?? 'Modelo não encontrado' }}</td>
                    <td>{{ $modelo->quantidade }}</td>
                    <td>{{ $modelo->quantidade_baixa ?? 0 }}</td>
                    <td>{{ $modelo->valor_total ? 'R$ ' . number_format($modelo->valor_total, 2, ',', '.') : 'R$ 0,00' }}</td>
                    <td>
                        @php
                            $baixados = $modelo->quantidade_baixa ?? 0;
                            $total = $modelo->quantidade;
                            $percentual = $total > 0 ? ($baixados / $total) * 100 : 0;
                        @endphp
                        
                        @if($percentual == 100)
                            Completo
                        @elseif($percentual > 0)
                            Parcial ({{ number_format($percentual, 0) }}%)
                        @else
                            Pendente
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Lista Detalhada de Pneus -->
    @if($requisicao->requisicaoPneuModelos->sum(fn($m) => $m->requisicaoPneuItens->count()) > 0)
        <div class="info-section">
            <h3>Lista Detalhada de Pneus</h3>
            
            @foreach($requisicao->requisicaoPneuModelos as $modelo)
                @if($modelo->requisicaoPneuItens->count() > 0)
                    <h4>{{ $modelo->modeloPneu->descricao_modelo ?? 'Modelo não encontrado' }} ({{ $modelo->requisicaoPneuItens->count() }} pneus)</h4>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>N° de Fogo</th>
                                <th>Código Antigo</th>
                                <th>Status</th>
                                <th>Data Baixa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modelo->requisicaoPneuItens as $item)
                            <tr>
                                <td>{{ $item->id_pneu }}</td>
                                <td>{{ $item->pneu->cod_antigo ?? '-' }}</td>
                                <td>{{ $item->pneu->status_pneu }}</td>
                                <td>{{ $item->data_baixa ? format_date($item->data_baixa, 'd/m/Y') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Observações -->
    @if($requisicao->observacao_solicitante || $requisicao->observacao || $requisicao->justificativa_de_finalizacao)
        <div class="info-section observacoes">
            <h3>Observações</h3>
            
            @if($requisicao->observacao_solicitante)
                <div style="margin-bottom: 15px;">
                    <strong>Observação Solicitante:</strong>
                    <textarea readonly>{{ $requisicao->observacao_solicitante }}</textarea>
                </div>
            @endif
            
            @if($requisicao->observacao)
                <div style="margin-bottom: 15px;">
                    <strong>Observação:</strong>
                    <textarea readonly>{{ $requisicao->observacao }}</textarea>
                </div>
            @endif
            
            @if($requisicao->justificativa_de_finalizacao)
                <div style="margin-bottom: 15px;">
                    <strong>Justificativa de Finalização:</strong>
                    <textarea readonly>{{ $requisicao->justificativa_de_finalizacao }}</textarea>
                </div>
            @endif
        </div>
    @endif

    <!-- Totais -->
    <div class="totals">
        <div class="totals-grid">
            <div class="total-item">
                <div class="total-label">Total Quantidade</div>
                <div class="total-value">{{ $totalQuantidade }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Total Baixado</div>
                <div class="total-value">{{ $totalQuantidadeBaixa }}</div>
            </div>
            <div class="total-item">
                <div class="total-label">Valor Total</div>
                <div class="total-value">R$ {{ number_format($valorTotalGeral, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado automaticamente em {{ date('d/m/Y H:i:s') }}</p>
        <p>Sistema de Gestão de Frota</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>