@if(isset($inconsistencias) && is_countable($inconsistencias) && count($inconsistencias) > 0)
<div class="overflow-x-auto">
    <div class="overflow-y-auto max-h-[600px] border rounded-md">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="sticky top-0 bg-gray-100 z-10">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data
                        Abastecimento</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bomba
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo da
                        Inconsistência</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Km
                        Abastecimento</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Combustível</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Departamento</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($inconsistencias as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{
                        $item->id_abastecimento_integracao }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{
                        $item->data_inclusao?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->placa }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->descricao_bomba }}</td>
                    <td class="px-4 py-2 text-sm text-gray-500 max-w-md">
                        <span class="inline-block truncate max-w-xs" title="{{ $item->mensagem }}">
                            {{ $item->mensagem }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->volume, 2,
                        ',', '.') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{
                        number_format($item->km_abastecimento, 0, '', '.') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->tipo_combustivel }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->nomefilial ?? '' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->descricao_departamento ?? ''
                        }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('admin.inconsistencias.ats.edit', $item->id_abastecimento_integracao) }}"
                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-indigo-600 hover:bg-indigo-500">
                                ✏️
                            </a>

                            @if($item->isSemEstoque())
                            <button type="button"
                                onclick="confirmAction('{{ $item->id_abastecimento_integracao }}', 'ats', 'reprocessar')"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-500">
                                🔄
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="text-center py-8 text-gray-600">
    <p>Nenhuma inconsistência encontrada.</p>
</div>
@endif