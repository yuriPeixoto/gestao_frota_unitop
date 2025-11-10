<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Ajustes KM de Abastecimentos</title>
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
        <h1>Relatório de Ajustes KM de Abastecimentos</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Data Inclusão</th>
                <th>Data Alteração</th>
                <th>Data Abast.</th>
                <th>Veículo</th>
                <th>Km Abastecimento</th>
                <th>Permissão KM</th>
                <th>Tipo Combustível</th>
                <th>Cód. Abastecimento ATS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $ajuste)
            <tr>
                <td>{{ $ajuste->id_ajuste_km_abastecimento }}</td>
                <td>{{ $ajuste->data_inclusao ? $ajuste->data_inclusao->format('d/m/Y H:i') : '' }}</td>
                <td>{{ $ajuste->data_alteracao ? $ajuste->data_alteracao->format('d/m/Y H:i') : '' }}</td>
                <td>{{ $ajuste->data_abastecimento ? $ajuste->data_abastecimento->format('d/m/Y') : '' }}</td>
                <td>{{ $ajuste->veiculo->placa ?? 'N/A' }}</td>
                <td>{{ number_format($ajuste->km_abastecimento, 0, ',', '.') }}</td>
                <td>{{ $ajuste->id_permissao_km_manual ?? 'N/A' }}</td>
                <td>{{ $ajuste->tipo_combustivel }}</td>
                <td>{{ $ajuste->id_abastecimento_ats ?? 'N/A' }}</td>
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