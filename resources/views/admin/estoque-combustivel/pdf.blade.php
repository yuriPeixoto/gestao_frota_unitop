<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Estoque de Combustível</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
            font-size: 11px;
        }

        h1 {
            font-size: 16px;
            text-align: center;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            font-weight: bold;
            color: #333;
        }

        td {
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .total {
            font-weight: bold;
        }

        .positive {
            color: green;
        }

        .negative {
            color: red;
        }
    </style>
</head>

<body>
    <div class="logo">
        <h1>Relatório de Estoque de Combustível</h1>
    </div>

    <div class="info">
        <p>
            <strong>Data de Geração:</strong> {{ date('d/m/Y H:i:s') }}<br>
            <strong>Usuário:</strong> {{ Auth::user()->name }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Filial</th>
                <th>Tanque</th>
                <th>Combustível</th>
                <th>Quantidade Anterior (L)</th>
                <th>Quantidade Atual (L)</th>
                <th>Diferença (L)</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $mov)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->data_alteracao)->format('d/m/Y H:i') }}</td>
                <td>{{ $mov->nome_filial }}</td>
                <td>{{ $mov->tanque }}</td>
                <td>{{ $mov->tipo_combustivel }}</td>
                <td style="text-align: right;">{{ number_format($mov->quantidade_anterior, 2, ',', '.') }}</td>
                <td style="text-align: right;">{{ number_format($mov->quantidade_em_estoque, 2, ',', '.') }}</td>
                <td style="text-align: right; {{ $mov->diferenca > 0 ? 'color: green;' : 'color: red;' }}">
                    {{ number_format(abs($mov->diferenca), 2, ',', '.') }}
                </td>
                <td>
                    {{ $mov->diferenca > 0 ? 'Entrada' : 'Saída' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Relatório gerado via sistema em {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>