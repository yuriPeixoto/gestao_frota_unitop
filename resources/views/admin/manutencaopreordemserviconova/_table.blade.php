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
                <th class="text-sm centralizado">Ações</th>
                <th class="text-sm centralizado">Código pré-OS</th>
                <th class="text-sm centralizado">Data<br>Inclusão</th>
                <th class="text-sm centralizado">Placa</th>
                <th class="text-sm centralizado">Tipo Veículo</th>
                <th class="text-sm centralizado">Motorista</th>
                <th class="text-sm centralizado">Descrição Reclamação</th>
                <th class="text-sm centralizado">Usuário</th>
                <th class="text-sm centralizado">Status</th>
                <th class="text-sm centralizado">Filial</th>
                <th class="text-sm centralizado">Ordem Serviço</th>
                <th class="text-sm centralizado">Grupo Resolvedor</th>
                <th class="text-sm centralizado">Origem Pré O.S.</th>
            </x-slot>

            @forelse ($preOrdemOs as $manutencao)
                <tr data-id="{{ $manutencao->id_pre_os }}" class="text-xs cursor-pointer"
                    @click="toggleRow('{{ $manutencao->id_pre_os }}')">
                    <td class="whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <x-tooltip content="Editar">
                                <a href="{{ route('admin.manutencaopreordemserviconova.edit', $manutencao->id_pre_os) }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            </x-tooltip>

                            <x-tooltip content="Assumir">
                                <button type="button" onclick="confirmarAssumir({{ $manutencao->id_pre_os }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <x-icons.plus class="h-3 w-3" />
                                </button>
                            </x-tooltip>

                            @if (auth()->user()->is_superuser || auth()->user()->departamento_id == 9)
                                <x-tooltip content="Excluir">
                                    <button type="button" onclick="confirmarExclusao({{ $manutencao->id_pre_os }})"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <x-icons.trash class="h-3 w-3" />
                                    </button>
                                </x-tooltip>
                            @endif
                        </div>
                    </td>
                    <td>{{ $manutencao->id_pre_os }}</td>
                    <td class="whitespace-nowrap">
                        {{ format_date($manutencao->data_inclusao, 'd/m/Y') }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->veiculo->placa ?? $manutencao->id_veiculo }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->veiculo->tipoEquipamento->descricao_tipo ?? $manutencao->id_veiculo }}
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->pessoal->nome ?? $manutencao->id_motorista }}
                    </td>
                    <td class="whitespace-pre-wrap">
                        {{ $manutencao->descricao_reclamacao }}</td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->id_usuario ? optional($manutencao->user)->name : '' }}</td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->id_status ? $manutencao->tipoStatusPreOs->descricao_tipo_status : '' }}</td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->filial->name ?? $manutencao->id_filial }}</td>
                    <td>
                        @if ($manutencao->ordemServico)
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-green-600/20 ring-inset">
                                {{ $manutencao->ordemServico->id_ordem_servico }}
                            </span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $manutencao->tipo ?? '-' }}
                    </td>

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
