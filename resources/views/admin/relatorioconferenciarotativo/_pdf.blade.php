<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Compra e Venda de Veículos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 10mm 12mm 15mm 12mm;
            /* margem ajustada */
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #444;
        }

        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #222;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0 0 0;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 5px 6px;
            font-size: 9.5px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            color: #222;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        .footer {
            position: fixed;
            bottom: 8px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #555;
            border-top: 1px solid #bbb;
            padding-top: 4px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Carvalima Transportes</h1>
        <p>Relatório de Conferência Rotativo Diario</p>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód. Produto</th>
                <th>Cód. Carvalima</th>
                <th>Produto</th>
                <th>Valor médio</th>
                <th>Quantiade</th>
                <th>Locação</th>
                <th>Contagem</th>

            </tr>
        </thead>
        <tbody>
            @forelse ($data as $resultado)
            <tr>
                <td>{{ $resultado->id_produto }}</td>
                <td>{{ $resultado->id_filial }}</td>
                <td>{{ $resultado->produto->descricao_produto ?? '' }}</td>
                <td>{{ number_format($resultado->valor_medio, 2, ',', '.') }}</td>
                <td style="text-align: center">{{ $resultado->quantidade }}</td>
                <td>{{ $resultado->localizacao_produto ?? '' }}</td>
                <td>{{ $resultado->contagem ?? '' }}</td>

            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; font-style: italic;">Nenhum registro encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Frota - Carvalima Transportes</p>
    </div>
</body>

</html>