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
        <h1>Relatório Listagem de Notificações</h1>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pago</th>
                <th>Placa</th>
                <th>Prazo Indicacão</th>
                <th>Nome Motorista</th>
                <th>Data Infração</th>
                <th>Ait</th>
                <th>Ait Originária</th>
                <th>Orgão Autuador</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento <br> do Boleto</th>
                <th>Local</th>
                <th>Gravidade</th>
                <th>Envio P/ <br> Financeiro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $listagemMulta)
            <tr>
                <td>{{ $listagemMulta->confirmacao_pagamento }}</td>
                <td>{{ $listagemMulta->placa }}</td>
                <td>{{ $listagemMulta->prazo_indicacao }}</td>
                <td>{{ $listagemMulta->motorista_nome }}</td>
                <td>{{ $listagemMulta->data_infracao }}</td>
                <td>{{ $listagemMulta->ait }}</td>
                <td>{{ $listagemMulta->ait_originaria }}</td>
                <td>{{ $listagemMulta->orgao_autuador }}</td>
                <td>{{ $listagemMulta->descricao }}</td>
                <td>R$ {{ number_format($listagemMulta->valor_a_pagar, 2, ',', '.') }}</td>
                <td>{{ format_date($listagemMulta->boleto_vencimento, 'd/m/Y') }}</td>
                <td>{{ $listagemMulta->local }}</td>
                <td>{{ $listagemMulta->gravidade }}</td>
                <td>{{ $listagemMulta->confirmacao_pagamento_manual }}</td>
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