<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Tipo Desenhos Pneus</title>
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
        <h1>Relatório de Tipo Desenhos Pneus</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Número de Sulcos</th>
                <th>Quantidade de Lona</th>
                <th>Dias Calibragem</th>
                <th>Data Inclusão</th>
                <th>Data Alteração</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $index => $tipo)
            <tr :index="$index" data-id="{{ $tipo['id'] }}">
                <td>{{ $tipo->id_desenho_pneu }}</td>
                <td>{{ $tipo->descricao_desenho_pneu }}</td>
                <td>{{ $tipo->numero_sulcos }}</td>
                <td>{{ $tipo->quantidade_lona_pneu }}</td>
                <td>{{ $tipo->dias_calibragem }}</td>
                <td>{{ $tipo->data_inclusao }}</td>
                <td>{{ $tipo->data_alteracao }}</td>
            </tr>
            @empty
            <x-tables.empty cols="8" message="Nenhum tipo de desenho encontrado" />
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>