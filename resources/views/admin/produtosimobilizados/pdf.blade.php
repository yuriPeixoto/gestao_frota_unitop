<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Produtos Imobilizados</title>
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
        <h1>Relatório de Produtos Imobilizados</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cód.<br>Imobilizados</th>
                <th>Cód.<br>Patrimonio</th>
                <th>Placa</th>
                <th>Produto</th>
                <th>Cód.<br>Tipo Imobilizado</th>
                <th>Departamento</th>
                <th>Filial</th>
                <th>Status</th>
                <th>Usuário</th>
                <th>Data Inclusão</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $produtosImobilizado)
            <tr>
                <td>{{ $produtosImobilizado->id_produtos_imobilizados }}</td>
                <td>{{ $produtosImobilizado->cod_patrimonio }}</td>
                <td>{{ $produtosImobilizado->veiculo->placa ?? 'Não Informado'}}</td>
                <td>{{ $produtosImobilizado->produto->descricao_produto }}</td>
                <td>{{ $produtosImobilizado->tipoImobilizado->descricao_tipo_imobilizados }}</td>
                <td>{{ $produtosImobilizado->departamento->descricao_departamento ?? 'Não informado' }}
                </td>
                <td>{{ $produtosImobilizado->filial->name }}</td>
                <td>{{ $produtosImobilizado->status }}</td>
                <td>{{ $produtosImobilizado->user->name ?? $produtosImobilizado->id_usuario }}
                </td>
                <td nowrap>{{ $produtosImobilizado->data_inclusao?->format('d/m/Y H:i') }}</td>
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