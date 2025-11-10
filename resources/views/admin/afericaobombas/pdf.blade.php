<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Entradas por Aferição de Bomba</title>
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
        .status {
            font-weight: bold;
        }
        .status-realizada {
            color: green;
        }
        .status-pendente {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Entradas por Aferição de Bomba</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Bomba</th>
                <th>Placa</th>
                <th>Volume</th>
                <th>Data</th>
                <th>Status Entrada</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $afericao)
                <tr>
                    <td>{{ $afericao->id_abastecimento_integracao }}</td>
                    <td>{{ $afericao->descricao_bomba ?? 'N/A' }}</td>
                    <td>{{ $afericao->placa }}</td>
                    <td>{{ number_format($afericao->volume, 2, ',', '.') }}</td>
                    <td>{{ is_object($afericao->data_inicio) ? $afericao->data_inicio->format('d/m/Y H:i') : (is_string($afericao->data_inicio) && $afericao->data_inicio ? date('d/m/Y H:i', strtotime($afericao->data_inicio)) : 'N/A') }}</td>
                    <td class="status {{ in_array($afericao->id_abastecimento_integracao, $entradasRealizadas) ? 'status-realizada' : 'status-pendente' }}">
                        {{ in_array($afericao->id_abastecimento_integracao, $entradasRealizadas) ? 'REALIZADA' : 'PENDENTE' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>
</html>