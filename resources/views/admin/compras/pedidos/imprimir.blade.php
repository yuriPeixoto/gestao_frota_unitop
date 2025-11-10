<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pedido de Compra #{{ $pedido->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .print-header {
            text-align: right;
            margin-bottom: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 22px;
            margin: 0;
            padding: 0;
        }

        .pedido-numero {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .field {
            margin-bottom: 10px;
        }

        .field-label {
            font-weight: bold;
            font-size: 12px;
            color: #666;
            display: block;
            margin-bottom: 3px;
        }

        .field-value {
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
            font-size: 13px;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row td {
            font-weight: bold;
        }

        .approval-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .signature-block {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 10px;
            text-align: center;
            font-size: 14px;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #666;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .buttons {
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            padding: 8px 16px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
        }

        .btn:hover {
            background-color: #4338ca;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="no-print buttons">
            <button onclick="window.print()" class="btn">Imprimir</button>
            <a href="{{ route('compras.pedidos.show', $pedido->id_pedido_compras) }}" class="btn">Voltar</a>
        </div>

        <div class="print-header">
            <div>{{ config('app.name') }}</div>
            <div>{{ date('d/m/Y H:i') }}</div>
        </div>

        <div class="page-title">
            <h1>PEDIDO DE COMPRA</h1>
            <div class="pedido-numero">Nº {{ $pedido->numero }}</div>
        </div>

        <div class="section">
            <div class="section-title">Informações do Pedido</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Data de Emissão</div>
                    <div class="field-value">{{ $pedido->data_inclusao->format('d/m/Y') }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Solicitação de Compra</div>
                    <div class="field-value">{{ $pedido->solicitacaoCompra->numero_solicitacao ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Tipo</div>
                    <div class="field-value">{{ $pedido->isProduto() ? 'Produto' : 'Serviço' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Status</div>
                    <div class="field-value">{{ $pedido->status }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Fornecedor</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Nome</div>
                    <div class="field-value">{{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">CNPJ</div>
                    <div class="field-value">{{ $pedido->fornecedor->cnpj_fornecedor ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Email</div>
                    <div class="field-value">{{ $pedido->fornecedor->email ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Telefone</div>
                    <div class="field-value">{{ $pedido->fornecedor->telefone ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Local</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Filial</div>
                    <div class="field-value">{{ $pedido->filial->descricao_filial ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Filial de Entrega</div>
                    <div class="field-value">{{ $pedido->filialEntrega->descricao_filial ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Filial de Faturamento</div>
                    <div class="field-value">{{ $pedido->filialFaturamento->descricao_filial ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Responsáveis</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Solicitante</div>
                    <div class="field-value">{{ $pedido->solicitacaoCompra->solicitante->name ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Comprador</div>
                    <div class="field-value">{{ $pedido->comprador->name ?? 'N/A' }}</div>
                </div>

                <div class="field">
                    <div class="field-label">Aprovador</div>
                    <div class="field-value">{{ $pedido->aprovador->name ?? 'Pendente' }}</div>
                </div>
            </div>
        </div>

        @if($pedido->observacao_pedido)
        <div class="section">
            <div class="section-title">Observações</div>
            <div>{{ $pedido->observacao_pedido }}</div>
        </div>
        @endif

        <div class="section">
            <div class="section-title">Itens do Pedido</div>
            <table>
                <thead>
                    <tr>
                        <th width="5%">Item</th>
                        <th width="40%">Descrição</th>
                        <th width="10%">Qtde</th>
                        <th width="10%">Unidade</th>
                        <th width="15%">Valor Unit.</th>
                        <th width="20%">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedido->itens as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td>{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                        <td>{{ $item->unidade_medida }}</td>
                        <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhum item encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" align="right">TOTAL:</td>
                        <td>R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="approval-section">
            <div class="signature-block">
                <div>_______________________________________________</div>
                <div>{{ $pedido->comprador->name ?? 'Comprador' }}</div>
            </div>

            <div class="signature-block">
                <div>_______________________________________________</div>
                <div>{{ $pedido->aprovador->name ?? 'Aprovador' }}</div>
            </div>
        </div>

        <div class="footer">
            <div>{{ config('app.name') }} - Módulo de Compras</div>
            <div>Documento impresso em {{ date('d/m/Y \à\s H:i:s') }}</div>
        </div>
    </div>
</body>

</html>