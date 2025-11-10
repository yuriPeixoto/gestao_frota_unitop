<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Listagem Pré-O.S (Finalizadas)</title>
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
        <h1>Relatório de Listagem Pré-O.S (Finalizadas)</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código pré-OS</th>
                <th>Data Inclusão</th>
                <th>Placa</th>
                <th>Motorista</th>
                <th>Descrição Reclamação</th>
                <th>Usuário</th>
                <th>Status</th>
                <th>Filial</th>
                <th>Grupo Resolvedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $preOrdemOs)
                <tr>
                    <td>{{ $preOrdemOs->id_pre_os }}</td>
                    <td>{{ $preOrdemOs->data_inclusao ? $preOrdemOs->data_inclusao->format('d/m/Y H:i') : '' }}</td>
                    <td>{{ $preOrdemOs->veiculo->placa }}</td>
                    <td>{{ $preOrdemOs->pessoal->nome ?? 'Não Informado' }}</td>
                    <td>{{ $preOrdemOs->descricao_reclamacao }}</td>
                    <td>{{ $preOrdemOs->id_usuario }}</td>
                    <td>{{ $preOrdemOs->tipoStatusPreOs->descricao_tipo_status }}</td>
                    <td>{{ $preOrdemOs->filial->name ?? 'Não Informado' }}</td>
                    <td>{{ $preOrdemOs->id_grupo_resolvedor }}</td>
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
