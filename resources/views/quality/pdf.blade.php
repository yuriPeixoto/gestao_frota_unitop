<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Melhorias - Qualidade</title>
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
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        .header p {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .stat-row {
            display: table-row;
        }
        .stat-box {
            display: table-cell;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #f9f9f9;
            width: 20%;
        }
        .stat-box strong {
            display: block;
            font-size: 12px;
            color: #333;
            margin-bottom: 3px;
        }
        .stat-box span {
            display: block;
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        .status-aguardando {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-aprovado {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejeitado {
            background-color: #fee2e2;
            color: #991b1b;
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
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Melhorias - Equipe de Qualidade</h1>
        <p>Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Data de Geração: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    {{-- Estatísticas --}}
    <div class="stats">
        <div class="stat-row">
            <div class="stat-box">
                <strong>{{ $stats['total'] }}</strong>
                <span>Total</span>
            </div>
            <div class="stat-box">
                <strong>{{ $stats['aguardando'] }}</strong>
                <span>Aguardando</span>
            </div>
            <div class="stat-box">
                <strong>{{ $stats['aprovadas'] }}</strong>
                <span>Aprovadas</span>
            </div>
            <div class="stat-box">
                <strong>{{ $stats['rejeitadas'] }}</strong>
                <span>Rejeitadas</span>
            </div>
            <div class="stat-box">
                <strong>{{ round($stats['tempo_medio_revisao'] ?? 0) }}h</strong>
                <span>Tempo Médio</span>
            </div>
        </div>
    </div>

    {{-- Tabela de Melhorias --}}
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Ticket</th>
                <th style="width: 25%;">Título</th>
                <th style="width: 15%;">Solicitante</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 15%;">Revisor</th>
                <th style="width: 12%;">Data Criação</th>
                <th style="width: 13%;">Data Revisão</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $melhoria)
                <tr>
                    <td style="font-weight: bold; font-family: 'Courier New', monospace;">#{{ $melhoria->ticket_number }}</td>
                    <td>{{ $melhoria->subject }}</td>
                    <td>{{ $melhoria->user->name }}</td>
                    <td>
                        @if($melhoria->status->value === 'aguardando_qualidade')
                            <span class="status-badge status-aguardando">AGUARDANDO</span>
                        @elseif($melhoria->status->value === 'aprovado_qualidade')
                            <span class="status-badge status-aprovado">APROVADO</span>
                        @elseif($melhoria->status->value === 'rejeitado_qualidade')
                            <span class="status-badge status-rejeitado">REJEITADO</span>
                        @else
                            <span class="status-badge">{{ $melhoria->status->label() }}</span>
                        @endif
                    </td>
                    <td>{{ $melhoria->qualityReviewer->name ?? '-' }}</td>
                    <td>{{ $melhoria->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $melhoria->quality_reviewed_at ? $melhoria->quality_reviewed_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Nenhuma melhoria encontrada no período</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestão de Frota - Relatório de Qualidade</p>
    </div>
</body>
</html>