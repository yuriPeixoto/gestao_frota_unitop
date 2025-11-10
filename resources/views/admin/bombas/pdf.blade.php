<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Bombas de Abastecimento</title>
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

        .status-ativo {
            color: #0D9488;
            font-weight: bold;
        }

        .status-inativo {
            color: #EF4444;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório de Bombas de Abastecimento</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Descrição</th>
                <th>Filial</th>
                <th>Tanque</th>
                <th>Bico 1</th>
                <th>Bico 2</th>
                <th>Status</th>
                <th>Tam. Máx. Encerrante</th>
                <th>Inclusão</th>
                <th>Alteração</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $bomba)
            <tr>
                <td>{{ $bomba->id_bomba }}</td>
                <td>{{ $bomba->descricao_bomba }}</td>
                <td>{{ $bomba->filial->name ?? 'N/A' }}</td>
                <td>{{ $bomba->tanque->tanque ?? 'N/A' }}</td>
                <td>{{ $bomba->bomba_ctf }}</td>
                <td>{{ $bomba->bomba_ctf_2_bico }}</td>
                <td class="{{ $bomba->is_ativo ? 'status-ativo' : 'status-inativo' }}">
                    {{ $bomba->is_ativo ? 'Ativo' : 'Inativo' }}
                </td>
                <td>{{ $bomba->tamanho_maximo_encerrante }}</td>
                <td>{{ $bomba->data_inclusao ? date('d/m/Y H:i', strtotime($bomba->data_inclusao)) : '' }}</td>
                <td>{{ $bomba->data_alteracao ? date('d/m/Y H:i', strtotime($bomba->data_alteracao)) : '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center;">Nenhum registro encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>