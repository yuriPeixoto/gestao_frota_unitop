<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Teste de Opacidade</title>
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
        <h1>Relatório de Teste de Opacidade</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Placa</th>
                <th>Filial Veiculo</th>
                <th>Data Realização</th>
                <th>Data Vencimento</th>
                <th>Situação</th>
                <th>K Máximo</th>
                <th>K Medido</th>
                <th>Resultado</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $testeopacidade)
                <tr>
                    <td>{{ $testeopacidade->id_teste_fumaca }}</td>
                    <td>{{ $testeopacidade->veiculo->placa }}</td>
                    <td>{{ $testeopacidade->veiculo->filial->name }}</td>
                    <td>{{ format_date($testeopacidade->data_de_realizacao, 'd/m/Y') }}</td>
                    <td>{{ format_date($testeopacidade->data_de_vencimento, 'd/m/Y') }}</td>
                    <td>{{ $testeopacidade->situacao ?? 'N/A' }}</td>
                    <td>{{ $testeopacidade->kmaximo }}</td>
                    <td>{{ $testeopacidade->kmedido }}</td>
                    <td>{{ $testeopacidade->resultado }}</td>
                    <td>{{ $testeopacidade->is_ativo ? 'Ativo' : 'Inativo' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>
