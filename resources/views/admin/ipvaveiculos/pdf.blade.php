<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de IPVA Veículos</title>
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
        <h1>Relatório de IPVA Veículos</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Placa</th>
                <th>Filial do Veiculo</th>
                <th>Renavam</th>
                <th>Ano Validade</th>
                <th>Status</th>
                <th>Quantidade de Parcelas</th>
                <th>Data Pagamento</th>
                <th>Valor Previsto</th>
                <th>Valor Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $IpvaVeiculo)
                <tr>
                    <td>{{ $IpvaVeiculo->id_ipva_veiculo }}</td>
                    <td>{{ $IpvaVeiculo->veiculo->placa ?? '' }}</td>
                    <td>{{ $IpvaVeiculo->veiculo->filial->name ?? '' }}</td>
                    <td>{{ $IpvaVeiculo->veiculo->renavam ?? '' }}</td>
                    </td>
                    <td>{{ $IpvaVeiculo->ano_validade }}
                    </td>
                    <td>{{ $IpvaVeiculo->status_ipva }}</td>
                    <td>{{ $IpvaVeiculo->quantidade_parcelas }}</td>
                    <td>{{ $IpvaVeiculo->data_pagamento_ipva ? date('d/m/Y', strtotime($IpvaVeiculo->data_pagamento_ipva)) : 'N/A' }}
                    </td>
                    <td>{{ number_format($IpvaVeiculo->valor_previsto_ipva ?? 0, 2, ',', '.') }}</td>
                    <td>{{ number_format($IpvaVeiculo->valor_pago_ipva ?? 0, 2, ',', '.') }}</td>
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
