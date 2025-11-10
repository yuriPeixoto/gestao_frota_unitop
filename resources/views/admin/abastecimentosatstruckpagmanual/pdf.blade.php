<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Abastecimentos ATS/Truckpag/Manual</title>
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
        .origem-ats {
            color: #1e40af;
            font-weight: bold;
        }
        .origem-truckpag {
            color: #065f46;
            font-weight: bold;
        }
        .origem-manual {
            color: #525252;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Abastecimentos ATS/Truckpag/Manual</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Origem</th>
                <th>Bomba/Posto</th>
                <th>Placa</th>
                <th>Data Abast.</th>
                <th>Combustível</th>
                <th>Volume(L)</th>
                <th>Km Abast.</th>
                <th>Km Rodado</th>
                <th>Média(Km/L)</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $abastecimento)
                <tr>
                    <td>{{ $abastecimento->id }}</td>
                    <td>
                        @if($abastecimento->tipo == 'ABASTECIMENTO VIA ATS')
                            <span class="origem-ats">ATS</span>
                        @elseif($abastecimento->tipo == 'ABASTECIMENTO VIA TRUCKPAG')
                            <span class="origem-truckpag">TRUCKPAG</span>
                        @else
                            <span class="origem-manual">MANUAL</span>
                        @endif
                    </td>
                    <td>{{ $abastecimento->descricao_bomba }}</td>
                    <td>{{ $abastecimento->placa }}</td>
                    <td>{{ $abastecimento->data_inicio ? $abastecimento->data_inicio->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $abastecimento->tipocombustivel }}</td>
                    <td class="text-right">{{ number_format($abastecimento->volume, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($abastecimento->km_abastecimento, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($abastecimento->km_rodado, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($abastecimento->media, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($abastecimento->valor_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center;">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>
</html>