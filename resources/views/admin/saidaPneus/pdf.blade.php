<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Saída de Pneus</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 10px;
            margin: 0;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 8px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Saída de Pneus</h1>
        <p>Gerado em {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if($data->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Cód</th>
                <th>Data Inclusão</th>
                <th>Data Alteração</th>
                <th>Usuário Solicitante</th>
                <th>Situação</th>
                <th>Usuário Estoque</th>
                <th>Filial</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $requisicao)
            <tr>
                <td>{{ $requisicao->id_requisicao_pneu }}</td>
                <td>{{ $requisicao->data_inclusao ? $requisicao->data_inclusao->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $requisicao->data_alteracao ? $requisicao->data_alteracao->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $requisicao->usuarioSolicitante->name ?? '-' }}</td>
                <td>{{ $requisicao->situacao === 'FINALIZADA' ? 'BAIXADA' : $requisicao->situacao }}</td>
                <td>{{ $requisicao->usuarioEstoque->name ?? '-' }}</td>
                <td>{{ $requisicao->filial->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>Nenhum registro encontrado com os filtros aplicados.</p>
    </div>
    @endif

    <div class="footer">
        Total de registros: {{ $data->count() }}
    </div>
</body>
</html>