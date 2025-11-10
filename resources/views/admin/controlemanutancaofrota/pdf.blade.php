<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Controle de Manutenção de Frota</title>
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
        <h1>Relatório de Controle de Manutenção de Frota</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Data Entrada</th>
                <th>Previsão Saída</th>
                <th>Data Encerramento</th>
                <th>Tipo O.S.</th>
                <th>Status</th>
                <th>Local</th>
                <th>Filial</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $controleManutFrota)
                <tr>
                    <td>{{ $controleManutFrota->os }}</td>
                    <td>{{ $controleManutFrota->placa }}</td>
                    <td>{{ $controleManutFrota->modeloveiculo }}</td>
                    <td>{{ format_date($controleManutFrota->dataentrada) }}</td>
                    <td>{{ format_date($controleManutFrota->dataprevisaosaida) }}</td>
                    <td>{{ format_date($controleManutFrota->data_encerramento) }}</td>
                    <td>{{ $controleManutFrota->tipoos }}</td>
                    <td>{{ $controleManutFrota->statusordem }}</td>
                    <td>{{ $controleManutFrota->localmanutencao }}</td>
                    <td>{{ $controleManutFrota->filial }}</td>
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
