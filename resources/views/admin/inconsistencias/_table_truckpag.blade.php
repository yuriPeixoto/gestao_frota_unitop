@if(isset($inconsistencias) && is_countable($inconsistencias) && count($inconsistencias) > 0)
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data
                    Abastecimento</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posto</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo da
                    Inconsistência</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Km de
                    Abastecimento</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KM Rodado
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filial</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento
                </th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($inconsistencias as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{
                    $item->id_abastecimento_integracao }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->data_inclusao?->format('d/m/Y
                    H:i') }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->placa }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->descricao_bomba }}</td>
                <td class="px-4 py-2 text-sm text-gray-500 max-w-md">
                    <span class="inline-block truncate max-w-xs" title="{{ $item->mensagem }}">
                        {{ $item->mensagem }}
                    </span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->tipo_servico }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->volume, 2, ',',
                    '.') }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->km_abastecimento,
                    0, '', '.') }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->km_rodado, 0, '',
                    '.') }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->name ?? '' }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->descricao_departamento ?? '' }}
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 text-center">
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('admin.inconsistencias.truckpag.edit', $item->id_abastecimento_integracao) }}"
                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $inconsistencias->links() }}
</div>
@else
<div class="text-center py-8">
    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma inconsistência encontrada</h3>
    <p class="mt-1 text-sm text-gray-500">Verifique os filtros aplicados ou tente outro período.</p>
</div>
@endif