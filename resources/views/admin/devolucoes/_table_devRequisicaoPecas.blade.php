<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Solicitações
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Situação</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Status Devolução
                        Requisição</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Filial</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Departamento</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Filial Solicitante
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Data Inclusão</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($devRequisicao as $itemRequisicao)
                    <tr>
                        @if (auth()->user()->is_superuser || (isset($itemRequisicao->devolucoes) && count($itemRequisicao->devolucoes) > 0))
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                <x-forms.button
                                    href="{{ route('admin.devolucoes.edit_devRequisicaoPecas', $itemRequisicao->id_solicitacao_pecas) }}"
                                    variant="outlined">
                                    <x-icons.cubes class="w-5 h-5 mr-2" />
                                    Gerar Devolução
                                </x-forms.button>
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->id_solicitacao_pecas }}
                        </td>
                        <td class="px-6 py-4 wrap text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->situacao }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->devolucoes[0]->situcao_pecas ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->filial->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->departamentoPecas->descricao_departamento }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 text-center">
                            {{ $itemRequisicao->transferenciaEstoqueAux[0]->filialSolicitante->name ?? ' ' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                            {{ format_date($itemRequisicao->data_inclusao) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $transfDireta->links() }}
        </div>
    </div>
</div>
