<div class="mt-6 overflow-x-auto relative min-h-[400px]">
    <div id="table-loading"
        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10 hidden">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="min-w-[1200px] w-full text-sm text-left text-gray-700 divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Código Transferência
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Status</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Status Devolução</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Usuário</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Departamento</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Observação</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Filial</th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Filial Solicitante
                    </th>
                    <th class="px-3 py-2 text-xs font-medium text-gray-500 uppercase text-center">Data Inclusão</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($transfDireta as $itemDireta)
                <tr>
                    @if (auth()->user()->is_superuser || (isset($itemDireta->devolucoes) &&
                    count($itemDireta->devolucoes) > 0))
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                        <x-forms.button
                            href="{{ route('admin.devolucoes.edit_devTransfDireta', $itemDireta->id_transferencia_direta_estoque) }}"
                            variant="outlined">
                            <x-icons.cubes class="w-5 h-5 mr-2" />
                            Devolução de<br>Materiais
                        </x-forms.button>
                    </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->id_transferencia_direta_estoque }}
                    </td>
                    <td class="px-6 py-4 wrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->status }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->devolucoes[0]->stauts ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->usuario->name ?? $itemDireta->id_usuario }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->id_departamento }} - {{ $itemDireta->departamento->descricao_departamento }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-center">
                        {{ $itemDireta->observacao ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->filial_->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ $itemDireta->filial_solicita_->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                        {{ format_date($itemDireta->data_inclusao) }}
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