<x-bladewind::card reduce_padding="true">
    <div>
        <x-bladewind::table selectable="false" checkable="false" name="controleFrota">
            <style>
                .bw-table th {
                    line-height: 1.2;
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                }

                /* .centralizado {
                    text-align: center !important;
                } */
            </style>
            <x-slot name="header">
                <th class="text-sm centralizado">Cód. NF</th>
                <th class="text-sm centralizado">Fornecedor</th>
                <th class="text-sm centralizado">Número da NF</th>
                <th class="text-sm centralizado">Data do Serviço</th>
                <th class="text-sm centralizado">Valor Total do Serviço</th>
                <th class="text-sm centralizado">Ações</th>
            </x-slot>

            @forelse ($cadastros as $controle)
                <tr data-id="{{ $controle->id_nota_fiscal_servico }}" class="text-xs cursor-pointer">
                    <td>{{ $controle->id_nota_fiscal_servico }}</td>
                    <td>{{ $controle->fornecedor->nome_fornecedor ?? 'N/A' }}</td>
                    <td>{{ $controle->numero_nota_fiscal }}</td>
                    <td>{{ format_date($controle->data_servico, 'd/m/Y') }}</td>
                    <td>{{ $controle->valor_total_servico }}</td>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.listagemoslacamentoservicorateio.edit', $controle->id_nota_fiscal_servico) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [2, 3, 4, 25]))
                                <button type="button"
                                    onclick="confirmarExclusao({{ $controle->id_nota_fiscal_servico }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </x-bladewind::table>

    </div>
</x-bladewind::card>

<div class="mt-4">
    {{ $cadastros->links() }}
</div>
