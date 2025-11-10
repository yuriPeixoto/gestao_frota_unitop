<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Abastecimentos Manuais</title>
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
        <h1>Ordem de Serviço Canceladas</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód. OS</th>
                <th>Placa</th>
                <th>Data Abertura</th>
                <th>Tipo O.S.</th>
                <th>Situação O.S.</th>
                <th>Data Encerramento</th>
                <th>Recepcionista</th>
                <th>Local Manutenção</th>
                <th>Recepcionista Encerramento</th>
                <th>Cód. Lcto. O.S. Auxiliar</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $osCancelada->id_ordem_servico }}</td>
                <td>{{ $osCancelada->veiculo->placa }}</td>
                <td>{{ format_date($osCancelada->data_abertura, 'd/m/Y H:i') ?? '' }}</td>
                <td>{{ $osCancelada->tipoOrdemServico->descricao_tipo_ordem }}</td>
                <td>{{ $osCancelada->statusOrdemServico->situacao_ordem_servico }}</td>
                <td>{{ format_date($osCancelada->data_encerramento, 'd/m/Y H:i') ?? '' }}</td>
                <td>{{ $osCancelada->usuario->name ?? '' }}</td>
                <td>{{ $osCancelada->local_manutencao ?? '' }}</td>
                <td>{{ $osCancelada->usuarioEncerramento->name ?? '' }}</td>
                <td>{{ $osCancelada->id_lancamento_os_auxiliar ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Gerado por Sistema de Gestão de Frota</p>
    </div>
</body>

</html>
