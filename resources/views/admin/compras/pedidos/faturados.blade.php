request('id_fornecedor')"
asyncSearch="true"
/>
</div>
</div>

<div class="flex justify-between">
    <div>
        <x-ui.export-buttons route="compras.pedidos" :formats="['pdf', 'csv', 'xls']"
            :params="['status' => 'finalizado']" />
    </div>

    <div>
        <button type="submit"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
            Buscar
        </button>
    </div>
</div>
</form>

<!-- Tabela de Pedidos -->
<div class="mt-6 overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Número
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fornecedor
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Valor
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Notas Fiscais
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($pedidos as $pedido)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('compras.pedidos.show', $pedido->id_pedido_compras) }}"
                        class="text-indigo-600 hover:text-indigo-900">
                        {{ $pedido->numero }}
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{ $pedido->data_inclusao?->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($pedido->notasFiscais->count() > 0)
                    <div class="flex flex-col">
                        @foreach($pedido->notasFiscais->take(2) as $nota)
                        <a href="#" class="text-indigo-600 hover:text-indigo-900">
                            NF {{ $nota->numero_nota }} ({{ $nota->data_emissao?->format('d/m/Y') }})
                        </a>
                        @endforeach

                        @if($pedido->notasFiscais->count() > 2)
                        <span class="text-gray-500 text-xs">+{{ $pedido->notasFiscais->count() - 2 }} notas</span>
                        @endif
                    </div>
                    @else
                    <span class="text-gray-500">Nenhuma nota registrada</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <a href="{{ route('compras.pedidos.show', $pedido->id_pedido_compras) }}"
                            class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>

                        <a href="{{ route('compras.pedidos.imprimir', $pedido->id_pedido_compras) }}" target="_blank"
                            class="text-blue-600 hover:text-blue-900" title="Imprimir">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </a>

                        @if($pedido->notasFiscais->count() == 0)
                        <a href="#" title="Registrar Nota Fiscal" class="text-green-600 hover:text-green-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                    Nenhum pedido faturado encontrado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $pedidos->links() }}
</div>
</div>
</div>
</div>
</x-app-layout>