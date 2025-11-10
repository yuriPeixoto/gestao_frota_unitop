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
                <th class="text-sm centralizado">Cód. O.S</th>
                <th class="text-sm centralizado">Data<br>Inclusão</th>
                <th class="text-sm centralizado">Placa</th>
                <th class="text-sm centralizado">Descrição Categoria</th>
                <th class="text-sm centralizado">Descrição Manutenção</th>
                <th class="text-sm centralizado">Tipo Manutenção</th>
                <th class="text-sm centralizado">Km Manutenção</th>
                <th class="text-sm centralizado">Filial</th>
            </x-slot>

            @forelse ($preOrdemOs as $manutencao)
                <tr data-id="{{ $manutencao->id_ordem_servico }}" class="text-xs cursor-pointer"
                    @click="toggleRow('{{ $manutencao->id_ordem_servico }}')">
                    <td>{{ $manutencao->id_ordem_servico }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($manutencao->data_inclusao, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->id_veiculo ? $manutencao->veiculo->placa : ''}}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->descricao_categoria }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->descricao_manutencao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->tipo_manutencao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->km_manutencao }}</td>
                    <td class="whitespace-pre-wrap">{{ $manutencao->nome_filial }}</td>
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



