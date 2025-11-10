<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de IPVA</title>
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
        <h1>Relatório de IPVA</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Placa</th>
                <th>Renavam</th>
                <th>Proprietario</th>
                <th>Tipo</th>
                <th>Uf</th>
                <th>Cota Unica sem desconto</th>
                <th>Cota Unica desconto</th>
                <th>Boleto cota unica vencimento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $listagemIpva)
            <tr>
                <td>{{ $listagemIpva->placa }}</td>
                <td>{{ $listagemIpva->renavam }}</td>
                <td>{{ $listagemIpva->proprietario }}</td>
                <td>{{ $listagemIpva->tipo }}</td>
                <td>{{ $listagemIpva->uf }}</td>
                <td>R$ {{ number_format($listagemIpva->cota_unica_sem_desconto, 2, ',', '.') }}</td>
                <td>{{ $listagemIpva->cota_unica_desconto1 }}</td>
                <td>{{ $listagemIpva->boleto_cota_unica_vencimento }}</td>
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