<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório Abastecimento por Bomba/Posto</title>
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
            margin-bottom: 25px;
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

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .grupo {
            background-color: #eaeaea;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-linha {
            background-color: #f2f2f2;
            font-weight: bold;
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
        <h1>Relatório Abastecimento por Bomba/Posto</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @php
    // Agrupar os abastecimentos por descricao_bomba
    $grupos = $data->groupBy('descricao_bomba');
    @endphp

    @forelse ($grupos as $descricao_bomba => $abastecimentos)
    <table>
        <thead>
            <tr class="grupo">
                <th colspan="7">{{ $descricao_bomba }}</th>
            </tr>
            <tr>
                <th>Data</th>
                <th>Placa</th>
                <th>Combustível</th>
                <th>Volume (L)</th>
                <th>Valor</th>
                <th>Km</th>
                <th>Motorista</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($abastecimentos as $a)
            <tr>
                <td>{{ \Carbon\Carbon::parse($a->data_inicio)->format('d/m/Y H:i') }}</td>
                <td>{{ $a->placa }}</td>
                <td>{{ $a->tipocombustivel ?? '-' }}</td>
                <td>{{ number_format($a->volume, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($a->valor, 2, ',', '.') }}</td>
                <td>{{ $a->km_abastecimento ?? '-' }}</td>
                <td>{{ $a->motorista ?? '-' }}</td>
            </tr>
            @endforeach

            {{-- Total por bomba --}}
            <tr class="total-linha">
                <td colspan="3" style="text-align: right;">Total {{ $descricao_bomba }}</td>
                <td>{{ number_format($abastecimentos->sum('volume'), 2, ',', '.') }}</td>
                <td>R$ {{ number_format($abastecimentos->sum('valor'), 2, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    @empty
    <p style="text-align:center;">Nenhum registro encontrado</p>
    @endforelse


</body>

</html>