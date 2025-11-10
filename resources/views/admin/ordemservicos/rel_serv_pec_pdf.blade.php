<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Relatório de Ordem de Serviço</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            margin: 10mm 8mm 10mm 8mm;
        }

        /* Tabela do cabeçalho */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        .logo-cell {
            width: 25%;
            height: 80px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-cell img {
            max-height: 70px;
            max-width: 100%;
        }

        .logo-placeholder {
            font-size: 12px;
            font-weight: bold;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
        }

        .title-cell {
            width: 50%;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            vertical-align: middle;
        }

        .info-cell {
            width: 25%;
            font-size: 10px;
            line-height: 1.3;
        }

        /* Tabela principal de peças e serviços */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 10px;
            vertical-align: top;
        }

        .main-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            height: 25px;
        }

        .main-table .qtd-col {
            width: 8%;
        }

        .main-table .pecas-col {
            width: 46%;
            text-align: left;
            padding-left: 8px;
        }

        .main-table .servicos-col {
            width: 46%;
            text-align: left;
            padding-left: 8px;
        }

        /* Linhas da tabela */
        .main-table .data-row {
            height: 20px;
        }

        /* Estilo para dados preenchidos */
        .filled-row {
            background-color: #fff;
        }

        .item-description {
            font-weight: normal;
            word-wrap: break-word;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Cabeçalho com layout similar ao modelo -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if (isset($base64Svg) && !empty($base64Svg))
                    <img src="data:image/svg+xml;base64,{{ $base64Svg }}" alt="Logo da Empresa">
                @else
                    <div class="logo-placeholder">
                        LOGO
                    </div>
                @endif
            </td>
            <td class="title-cell">
                Descrição Serviço / Peças
            </td>
            <td class="info-cell">
                <strong>O.S:</strong> {{ $data->id_ordem_servico ?? 'N/A' }}<br>
                <strong>Placa:</strong> {{ optional($data->veiculo)->placa ?? 'N/A' }}<br>
                <strong>Data:</strong>
                {{ isset($data->created_at) ? $data->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

    <!-- Tabela principal de peças e serviços -->
    <table class="main-table">
        <thead>
            <tr>
                <th class="qtd-col">QTD</th>
                <th class="pecas-col">Peças</th>
                <th class="servicos-col">Serviços</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Preparar dados de peças e serviços
                $pecas = isset($data->pecas) ? $data->pecas : collect();
                $servicos = isset($data->servicos) ? $data->servicos : collect();

                // Combinar peças e serviços em uma lista única
                $itens = collect();

                // Adicionar peças
                foreach ($pecas as $peca) {
                    $descricaoPeca = '';
                    if ($peca->produto) {
                        // Usar descricao_produto do model Produto
                        $descricaoPeca = $peca->produto->descricao_produto ?? 'Produto não identificado';

                        // Se houver código do produto, incluir
                        if ($peca->produto->codigo_produto) {
                            $descricaoPeca = '[' . $peca->produto->codigo_produto . '] ' . $descricaoPeca;
                        }
                    } elseif ($peca->aplicacao) {
                        $descricaoPeca = $peca->aplicacao;
                    } else {
                        $descricaoPeca = 'Peça não especificada';
                    }

                    $itens->push([
                        'tipo' => 'peca',
                        'quantidade' => $peca->quantidade ?? 1,
                        'descricao' => $descricaoPeca,
                        'valor' => $peca->valor_pecas ?? 0,
                    ]);
                }

                // Adicionar serviços
                foreach ($servicos as $servico) {
                    $descricaoServico = '';
                    if ($servico->servicos) {
                        // Usar descricao_servico do model Servico
                        $descricaoServico = $servico->servicos->descricao_servico ?? 'Serviço não identificado';
                    } else {
                        $descricaoServico = 'Serviço não especificado';
                    }

                    $itens->push([
                        'tipo' => 'servico',
                        'quantidade' => $servico->quantidade_servico ?? 1,
                        'descricao' => $descricaoServico,
                        'valor' => $servico->valor_servico ?? 0,
                    ]);
                }

                $totalLinhas = 25;
                $linhasPreenchidas = $itens->count();
                $linhasVazias = max(0, $totalLinhas - $linhasPreenchidas);
            @endphp

            <!-- Linhas com dados reais -->
            @foreach ($itens as $index => $item)
                <tr class="data-row filled-row">
                    <td class="qtd-col">{{ $item['quantidade'] }}</td>
                    @if ($item['tipo'] == 'peca')
                        <td class="pecas-col">
                            <span class="item-description">{{ $item['descricao'] }}</span>
                        </td>
                        <td class="servicos-col">&nbsp;</td>
                    @else
                        <td class="pecas-col">&nbsp;</td>
                        <td class="servicos-col">
                            <span class="item-description">{{ $item['descricao'] }}</span>
                        </td>
                    @endif
                </tr>
            @endforeach

            <!-- Linhas em branco para completar a tabela -->
            @for ($i = 0; $i < $linhasVazias; $i++)
                <tr class="data-row">
                    <td class="qtd-col">&nbsp;</td>
                    <td class="pecas-col">&nbsp;</td>
                    <td class="servicos-col">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Frota - Gerado em {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>
