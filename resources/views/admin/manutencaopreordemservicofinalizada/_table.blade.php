<x-bladewind::card reduce_padding="true">
    <div>

        <x-bladewind::table selectable="false" checkable="false" name="controleFrota">
            <style>
                .bw-table th {
                    line-height: 1.2;
                    padding-top: 0.5rem;
                    padding-bottom: 0.5rem;
                }

                .centralizado {
                    text-align: center !important;
                }
            </style>
            <x-slot name="header">
                <th class="text-sm centralizado">Código pré-OS</th>
                <th class="text-sm centralizado">Data<br>Inclusão</th>
                <th class="text-sm centralizado">Placa</th>
                <th class="text-sm centralizado">Motorista</th>
                <th class="text-sm centralizado">Descrição Reclamação</th>
                <th class="text-sm centralizado">Usuário</th>
                <th class="text-sm centralizado">Status</th>
                <th class="text-sm centralizado">Filial</th>
                <th class="text-sm centralizado">Grupo Resolvedor</th>
                <th class="text-sm centralizado">Ações</th>
            </x-slot>

            @forelse ($preOrdemOs as $manutencao)
            <tr data-id="{{ $manutencao->id_pre_os }}" class="text-xs cursor-pointer"
                @click="toggleRow('{{ $manutencao->id_pre_os }}')">
                <td>{{ $manutencao->id_pre_os }}</td>
                <td class="whitespace-nowrap">
                    {{ format_date($manutencao->data_inclusao, 'd/m/Y') }}
                </td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_veiculo ? $manutencao->veiculo->placa : '' }}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_motorista ? $manutencao->pessoal->nome : '' }}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->descricao_reclamacao }}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_usuario ? $manutencao->pessoal?->nome : '' }}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_status ?
                    $manutencao->tipoStatusPreOs->descricao_tipo_status : '' }}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_filial ? $manutencao->filial->name : ''}}</td>
                <td class="whitespace-pre-wrap">{{ $manutencao->id_grupo_resolvedor ?
                    $manutencao->descricao_grupo_resolvedor : '' }}</td>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.manutencaopreordemservicofinalizada.edit', $manutencao->id_pre_os) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

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
    {{ $preOrdemOs->links() }}
</div>