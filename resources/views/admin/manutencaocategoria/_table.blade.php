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
                <th class="text-sm centralizado">Cód. Planejamento <br>Categoria</th>
                <th class="text-sm centralizado">Data Inclusão</th>
                <th class="text-sm centralizado">Data Alteração</th>
                <th class="text-sm centralizado">Categoria</th>
                <th class="text-sm centralizado">Manutenção</th>
                <th class="text-sm centralizado">Ações</th>
            </x-slot>

            @forelse ($categoria as $controle)
                <tr data-id="{{ $controle->id_manutencao_categoria }}" class="text-xs cursor-pointer">
                    <td>{{ $controle->id_manutencao_categoria }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($controle->data_inclusao, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ format_date($controle->data_alteracao, 'd/m/Y') }}
                    </td>
                    <td>{{ $controle->id_categoria ? $controle->tipoCategoria->descricao_categoria : ''}}</td>
                    <td class="whitespace-pre-wrap">{{ $controle->id_planejamento ? $controle->planejamento->manutencao->descricao_manutencao : '' }}</td>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.manutencaocategoria.edit', $controle->id_manutencao_categoria) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [2, 3, 4, 25]))
                                <button type="button" onclick="confirmarExclusao({{ $controle->id_manutencao_categoria }})"
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
    {{ $categoria->links() }}
</div>



