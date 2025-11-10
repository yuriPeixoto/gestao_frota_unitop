<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de IPVA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            /* um pouco menor */
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
            font-size: 14px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 9px;
            color: #667;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* 🔹 garante ajuste proporcional */
            word-wrap: break-word;
            /* 🔹 permite quebra nas células */
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 3px;
            font-size: 8px;
            text-align: left;
            vertical-align: top;
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
            font-size: 8px;
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
                <th>Fornecedor</th>
                <th>Número NF</th>
                <th>Chave </br> Nota</th>
                <th>Pedido Geral</th>
                <th>Valor Total</th>
                <th>Valor Total com Desconto</th>
                <th>Data Solicitação</th>
                <th>Cód. Solicitação</th>
                <th>Solicitante</th>
                <th>Solicitação</th>
                <th>Tipo Pedido</th>
                <th>XML Integrado</th>
                <th>Data Inclusão</th>
                <th>Usuário Lançamento</th>
                <th>OS</th>
                <th>Filial</th>
                <th>Placa</th>
                <th>Valor Total Nota</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $consulta)
            <tr>
                <td>{{ $consulta->id_pedido_compras }}</td>
                <td>{{ $consulta->nome_fornecedor}}</td>
                <td>{{ $consulta->numero_nf}}</td>
                <td>{{ $consulta->chave_nf}}</td>
                <td>{{ $consulta->id_pedido_geral}}</td>
                <td>{{ number_format($consulta->pedidoCompra->valor_total, 2,',','.')}}</td>
                <td>{{ number_format($consulta->pedidoCompra->valor_total_desconto, 2,',','.')}}</td>
                <td>{{ $consulta->data_solicitacao ?? '-'}}</td>
                <td>{{ $consulta->id_solicitacao}}</td>
                <td>{{ $consulta->solicitante ?? '-'}}</td>
                <td>{{ $consulta->solicitacao}}</td>
                <td>{{ $consulta->tipo_pedido}}</td>
                <td>{{ $consulta->xml_integrado}}</td>
                <td>{{ $consulta->data_inclusao ?? '-'}}</td>
                <td>{{ $consulta->user->name ?? '-'}}</td>
                <td>{{ $consulta->os}}</td>
                <td>{{ $consulta->filial ?? '-'}}</td>
                <td>{{ $consulta->placa ?? '-'}}</td>
                <td>{{ number_format($consulta->valor_nota_fiscal, 2,',','.')}}</td>
            </tr>
            @empty
            <tr>
                <td colspan="20" style="text-align: center;">Nenhum registro encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>