<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Serviços Mecanicos</title>
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
        <h1>Relatório de Serviços Mecanicos</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Data Inclusão</th>
                <th>Fornecedor</th>
                <th>Mecanico</th>
                <th>Serviço</th>
                <th>Cód. O.S.</th>
                <th>Placa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $servicoMecanico)
                <tr>
                    <td>{{ $servicoMecanico->id_servico_mecanico }}</td>
                    <td>{{ format_date($servicoMecanico->data_inclusao) }}
                    </td>
                    <td>{{ $servicoMecanico->fornecedor->nome_fornecedor }}</td>
                    <td>{{ $servicoMecanico->pessoal->nome ?? 'Não Informado' }}</td>
                    <td>{{ $servicoMecanico->servico->descricao_servico ?? 'Não Informado' }}</td>
                    <td>{{ $servicoMecanico->id_os }}</td>
                    <td>{{ $servicoMecanico->veiculo->placa }}</td>
                    <td>{{ $servicoMecanico->status_servico }}</td>
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
