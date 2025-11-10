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
                <th class="text-sm centralizado">Os</th>
                <th class="text-sm centralizado">Placa</th>
                <th class="text-sm centralizado">Modelo</th>
                <th class="text-sm centralizado">Data<br>Entrada</th>
                <th class="text-sm centralizado">Previsão<br>Saída</th>
                <th class="text-sm centralizado">Data<br>Encerramento</th>
                <th class="text-sm centralizado">Tipo de O.S</th>
                <th class="text-sm centralizado">Status</th>
                <th class="text-sm centralizado">Local</th>
                <th class="text-sm centralizado">Filial</th>
            </x-slot>

            @forelse ($controleManutencaoFrota as $controle)
                <tr data-id="{{ $controle->os }}" class="text-xs cursor-pointer"
                    @click="toggleRow('{{ $controle->os }}')">
                    <td>{{ $controle->os }}</td>
                    <td>{{ $controle->placa }}</td>
                    <td class="whitespace-pre-wrap">{{ $controle->modeloveiculo }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($controle->dataentrada, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ format_date($controle->dataprevisaosaida, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ format_date($controle->data_encerramento, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-pre-wrap">{{ $controle->tipoos }}</td>
                    <td class="whitespace-nowrap">{{ $controle->statusordem }}</td>
                    <td class="whitespace-pre-wrap">{{ $controle->localmanutancao }}</td>
                    <td class="whitespace-pre-wrap">{{ $controle->filial }}</td>
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
    {{ $controleManutencaoFrota->links() }}
</div>
