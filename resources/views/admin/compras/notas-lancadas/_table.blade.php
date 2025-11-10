<div id="tabela-compras">
    @php
    $hasData = isset($paginatedNotasLancadas) && count($paginatedNotasLancadas) > 0;
    @endphp

    @if(!$hasData)
    <div class="py-6 text-center text-gray-500">Nenhum registro encontrado.</div>
    @else
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2">
                    <input type="checkbox" class="select-all-checkbox h-4 w-4">
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Inclusão
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número NF
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Série</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Emissão
                </th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor
                    Serviço</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor NF
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($paginatedNotasLancadas as $linha)
            <tr>
                <td class="px-3 py-2">
                    <input type="checkbox" class="pedido-checkbox h-4 w-4" value="{{ $linha['id'] }}">
                </td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['tipo'] ?? '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ isset($linha['data_inclusao']) ? (string)
                    $linha['data_inclusao'] : '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['numero_pedido'] ?? '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['fornecedor'] ?? '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['numero_nf'] ?? '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ $linha['serie_nf'] ?? '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700">{{ format_date(isset($linha['data_emissao']) ? (string)
                    $linha['data_emissao'] : '-' )}}</td>
                <td class="px-3 py-2 text-sm text-gray-700 text-right">{{ isset($linha['valor_servico']) ?
                    number_format((float) $linha['valor_servico'], 2, ',', '.') : '-' }}</td>
                <td class="px-3 py-2 text-sm text-gray-700 text-right">{{ isset($linha['valor_total']) ?
                    number_format((float) $linha['valor_total'], 2, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        @if(method_exists($paginatedNotasLancadas, 'links'))
        {{ $paginatedNotasLancadas->links() }}
        @endif
    </div>
    @endif
</div>