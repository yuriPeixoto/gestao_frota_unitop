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
                <th class="text-sm centralizado">Cod. O.S</th>
                <th class="text-sm centralizado">Fornecedor</th>
                <th class="text-sm centralizado">Serviço</th>
                <th class="text-sm centralizado">Valor Serviço</th>
                <th class="text-sm centralizado">Valor Com<br>Desconto</th>
                <th class="text-sm centralizado">Valor Total</th>
                <th class="text-sm centralizado">Status Serviço</th>
            </x-slot>

            @forelse ($cadastros as $controle)
                <tr data-id="{{ $controle->id_ordem_servico_serv }}" class="text-xs cursor-pointer">
                    <td>{{ $controle->id_ordem_servico_serv }}</td>
                    <td>{{ $controle->id_fornecedor }}</td>
                    <td>{{ $controle->id_ordem_servico }}</td>
                    <td>{{ $controle->valor_servico }}</td>
                    <td>{{ $controle->valor_descontoservico }}</td>
                    <td>{{ $controle->valor_total_com_desconto }}</td>
                    <td>{{ $controle->status_servico }}</td>
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



