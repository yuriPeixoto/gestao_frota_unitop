<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notas Lançadas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
<h2>Notas Lançadas</h2>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Fornecedor</th>
        <th>Número NF</th>
        <th>Série</th>
        <th>Data Emissão</th>
        <th>Valor Serviço</th>
        <th>Valor NF</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $nota)
        <tr>
            <td>{{ $nota->id_nf_avulsa ?? $nota->id ?? '-' }}</td>
            <td>{{ optional($nota->fornecedor)->nome_fornecedor ?? ($nota->nome_fornecedor ?? '-') }}</td>
            <td>{{ $nota->numero_nf ?? '-' }}</td>
            <td>{{ $nota->serie_nf ?? '-' }}</td>
            <td>{{ isset($nota->data_emissao) ? (string) $nota->data_emissao : '-' }}</td>
            <td class="text-right">{{ isset($nota->valor_pecas) ? number_format((float) $nota->valor_pecas, 2, ',', '.') : '-' }}</td>
            <td class="text-right">{{ isset($nota->valor_total_nf) ? number_format((float) $nota->valor_total_nf, 2, ',', '.') : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
