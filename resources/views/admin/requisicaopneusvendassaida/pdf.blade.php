<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Requisição de Pneus - Venda</title>
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
            color: #666;
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
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório de Requisição de Pneus - Venda</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Data Inclusão</th>
                <th>Data Alteração</th>
                <th>Usuário Alteração</th>
                <th>Situação</th>
                <th>Usuário Estoque</th>
                <th>Filial</th>
                <th>Documento Autorização</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $reqPneuVenda)
                <tr>
                    <td>{{ $reqPneuVenda->id_requisicao_pneu }}</td>
                    <td>{{ format_date($reqPneuVenda->data_inclusao, 'd/m/Y') }}</td>
                    <td>{{ format_date($reqPneuVenda->data_alteracao, 'd/m/Y') }}</td>
                    <td>{{ $reqPneuVenda->usuarioSolicitante->name ?? 'N/A' }}</td>
                    <td>{{ $reqPneuVenda->situacao }}</td>
                    <td>{{ $reqPneuVenda->usuarioEstoque->name ?? 'N/A' }}</td>
                    <td>{{ $reqPneuVenda->filial->name }}</td>
                    <td>{{ $reqPneuVenda->documento_autorizacao }}</td>
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
