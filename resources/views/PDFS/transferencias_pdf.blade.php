<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Transferências</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            /* menor fonte */
            line-height: 1.1;
            /* menos espaçamento entre linhas */
            margin: 5px 10px;
            /* margens menores na página */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
            /* força colunas com largura fixa para evitar quebras estranhas */
            word-wrap: break-word;
            /* quebra texto dentro das células se necessário */
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            /* padding menor */
            text-align: left;
            vertical-align: middle;
            overflow-wrap: break-word;
            /* para textos grandes quebrarem linha */
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }

        h2 {
            text-align: center;
            font-weight: bold;
            font-size: 15;
        }
    </style>

</head>

<body>
    <h2>Relatório de Transferências</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 11%;">ID Transferencia</th>
                <th style="width: 11%;">Status</th>
                <th style="width: 11%;">Departamento</th>
                <th style="width: 11%;">Usuário</th>
                <th style="width: 11%;">Observação</th>
                <th style="width: 11%;">Filial</th>
                <th style="width: 11%;">Filial Solicitante</th>
                <th style="width: 11%;">Data Inclusão</th>
                {{-- adicione mais colunas se quiser --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($resultados as $item)
            <tr>
                <td>{{ $item->id_transferencia_direta_estoque}}</td>
                <td>{{ $item->status }}</td>
                <td>{{ $item->departamento?->descricao_departamento }}</td>
                <td>{{ $item->usuario?->name }}</td>
                <td>{{ $item->observacao }}</td>
                <td>{{ $item->getRelation('filial')->name ?? '-' }}</td>
                <td>{{ $item->filial_solicita_->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->data_inclusao)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>