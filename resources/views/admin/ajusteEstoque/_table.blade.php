<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código Acerto Estoque</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Data Acerto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Filial</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código do Estoque</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Código do Produto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Tipo Acerto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Quantidade Acerto</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase">Preço médio</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($ajustes as $ajuste)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <x-tooltip content="Editar">
                                <a href="{{ route('admin.ajusteEstoque.edit', $ajuste->id_acerto_estoque) }}"
                                    class="flex p-2 bg-indigo-600 rounded-full p-1 inline-flex items-center shadow-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-icons.edit class="w-3 h-3" />
                                </a>
                            </x-tooltip>
                            <x-tooltip content="Excluir">
                                <a href="#" onclick="confirmarExclusao({{ $ajuste->id_acerto_estoque }})"
                                    class="flex p-2 bg-red-600 rounded-full p-1 inline-flex items-center shadow-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="w-3 h-3 text-white" />
                                </a>
                            </x-tooltip>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->id_acerto_estoque }}
                        </td>
                        <td class="px-6 py-4 wrap text-sm text-gray-900">
                            {{ format_date($ajuste->data_acerto, 'd/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->filial->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->estoque->descricao_estoque }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $ajuste->produto->descricao_produto ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->tipo_acerto->descricao_tipo_acerto }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->quantidade_acerto }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ajuste->preco_medio }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $ajustes->links() }}
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.ajusteEstoque._scripts')
@endpush
