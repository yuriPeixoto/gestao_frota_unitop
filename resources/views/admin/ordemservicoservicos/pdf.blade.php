<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Serviços para Lançamento de NF</title>
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
        th, td {
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
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Serviços para Lançamento de NF</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>O.S.</th>
                <th>Fornecedor</th>
                <th>Serviço</th>
                <th>Valor Serviço</th>
                <th>Desconto</th>
                <th>Valor Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $servico)
                <tr>
                    <td>{{ $servico->id_ordem_servico }}</td>
                    <td>{{ $servico->fornecedor->nome_fornecedor ?? 'N/A' }}</td>
                    <td>{{ $servico->servicos->descricao_servico ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($servico->valor_servico ?? 0, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($servico->valor_descontoservico ?? 0, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($servico->valor_total_com_desconto ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $servico->status_servico ?? 'PENDENTE' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>
</html>