<x-bladewind::card reduce_padding="true">
    <div>
        <x-bladewind::table selectable="false" checkable="false" name="relacaoVeiculo">
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
                <th class="text-sm centralizado">Cód. Serviço</th>
                <th class="text-sm centralizado">Filial</th>
                <th class="text-sm centralizado">Manutenção</th>
                <th class="text-sm centralizado">Descrição do Serviço</th>
                <th class="text-sm centralizado">Cód. Grupo</th>
                <th class="text-sm centralizado">Tempo do Serviço</th>
                <th class="text-sm centralizado">Categoria Veículo</th>
                <th class="text-sm centralizado">Serviço Ativo</th>
                <th class="text-sm centralizado">Ações</th>
            </x-slot>

            @forelse ($manutencaoServico as $controle)
            <tr data-id="{{ $controle->id_servico }}" class="text-xs cursor-pointer">
                <td>{{ $controle->id_servico }}</td>
                <td class="whitespace-pre-wrap">{{ $controle->id_filial ? $controle->filial->name : ''}}</td>
                <td class="whitespace-pre-wrap">{{ $controle->id_manutencao ?
                    $controle->manutencao->descricao_manutencao : ''}}</td>
                <td class="whitespace-pre-wrap">{{ $controle->descricao_servico}}</td>
                <td class="whitespace-pre-wrap">{{ $controle->id_grupo ? $controle->grupoServico->descricao_grupo : ''}}
                </td>
                <td class="whitespace-pre-wrap">{{ $controle->hora_servico }}</td>
                <td class="whitespace-pre-wrap">{{ $controle->categoria->descricao_categoria ?? 'Não informado' }}</td>
                <td class="whitespace-pre-wrap">{{ $controle->ativo_servico ? 'SIM' : 'NÃO' }}</td>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.manutencaoservicos.edit', $controle->id_servico) }}"
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
    {{ $manutencaoServico->links() }}
</div>