<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de IPVA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            margin: 15mm 10mm 15mm 10mm;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #667;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            font-size: 9px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #667;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório Listagem de Notas de Compras</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód. Pedido</th>
                <th>N° O.S</th>
                <th>Valor Pedido</th>
                <th>Fornecedor</th>
                <th>CNPJ|CPF</th>
                <th>Data Inclusão</th>
                <th>Placa</th>
                <th>Tipo Compra</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $listaCompra)
            <tr>
                <td>{{ $listaCompra->id_pedido_compras }}</td>
                <td>{{ $listaCompra->id_ordem_servico }}</td>
                <td>{{ $listaCompra->valor_total_desconto }}</td>
                <td>{{ $listaCompra->fornecedor->nome_fornecedor }}</td>
                <td>{{ $listaCompra->fornecedor->cnpj_fornecedor ?? $listaCompra->fornecedor->cpf_fornecedor }}</td>
                <td>{{ $listaCompra->data_inclusao }}</td>
                <td>{{ $listaCompra->placa }}</td>
                <td>{{ $listaCompra->tipo_compra }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Nenhum registro encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>