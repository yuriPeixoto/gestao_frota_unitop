<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Pedido de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }

        .container {
            width: 100%;
        }

        .company-info {
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .company-name {
            font-weight: bold;
            font-size: 12px;
        }

        .order-info {
            margin-bottom: 15px;
            text-align: center;
        }

        .order-title {
            font-weight: bold;
            font-size: 12px;
        }

        .order-number {
            font-weight: bold;
            font-size: 14px;
        }

        .divider {
            border-top: 1px solid #000;
            margin: 5px 0;
        }

        .supplier-info {
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .attention-box {
            border: 1px solid #000;
            padding: 3px;
            margin: 3px 0;
        }

        .buyer-info {
            margin-bottom: 10px;
            line-height: 1.2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-size: 9px;
        }

        td:first-child {
            text-align: left;
        }

        .totals {
            text-align: right;
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="company-info">
            <span class="company-name">CARVALIMA TRANSPORTES LTDA</span><br>
            CNPJ: 33.070.814/0010-42<br>
            ROD Palmiro Paes de Barros CEP: 78.095.295 N°: 2700<br>
            Bairro: Parque Cuiaba Município: Cuiaba UF: MT
        </div>

        <div class="order-info">
            <div class="order-title">PEDIDO DE COMPRAS</div>
            <div class="order-number">Nº {{ $listagempedido->id_pedido_compras }}</div>
            <div>Data de Emissão: {{ \Carbon\Carbon::parse($listagempedido->data_inclusao)->format('d/m/Y') }}</div>
            <div>Data de Aprovação: {{ optional($listagempedido->data_aprovacao) ?
                \Carbon\Carbon::parse($listagempedido->data_aprovacao)->format('d/m/Y') : '' }}</div>
        </div>

        <div class="divider"></div>

        <div class="supplier-info">
            <span class="bold">Fornecedor:</span> {{ $listagempedido->fornecedor->id_fornecedor }} - {{
            $listagempedido->fornecedor->nome_fornecedor }} / CNPJ {{ $listagempedido->fornecedor->cnpj_fornecedor
            }}<br>
            {{ $listagempedido->fornecedor->id_fornecedor }} - {{ $listagempedido->fornecedor->contato ?? '' }}<br>
            {{ $listagempedido->fornecedor->id_fornecedor }}

            <div class="attention-box">
                <span class="bold">Atenção:</span> Informar o Nº{{ $listagempedido->id_pedido_compras }} do Pedido na NF
            </div>
        </div>

        <div class="buyer-info">
            <span class="bold">Comprador:</span><br>
            {{ $listagempedido->comprador->name ?? '-' }}<br>
            {{ $listagempedido->comprador->email ?? '-' }}<br>
            Tel: {{ $listagempedido->comprador->telefone ?? '-' }}
        </div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Descrição Produtos</th>
                    <th>Unidade</th>
                    <th>Quantidade</th>
                    <th>Valor Produto</th>
                    <th>Valor Total</th>
                    <th>Valor Total Desconto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listagempedido->itens as $item)
                <tr>
                    <td>Cód: {{ $item->produtos->id_produto }}/ {{ $item->produtos->descricao_produto }}</td>
                    <td>{{ $item->unidade ?? 'UN' }}</td>
                    <td>{{ number_format($item->quantidade_produtos, 0, ',', '.') }}</td>
                    <td>{{ number_format($item->valor_produto, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->valor_total, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->valor_total_desconto, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <span>TOTAL: {{ number_format($listagempedido->itens->sum('valor_total'), 2, ',', '.') }}</span><br>
            <span>{{ number_format($listagempedido->itens->sum('valor_total_desconto'), 2, ',', '.') }}</span>
        </div>
    </div>
</body>

</html>