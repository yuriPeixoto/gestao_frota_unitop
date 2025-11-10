<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>placa</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Base Veículo</x-tables.head-cell>
            <x-tables.head-cell>Cód. Categoria </x-tables.head-cell>
            <x-tables.head-cell>renavam</x-tables.head-cell>
            <x-tables.head-cell>Marca Veículo</x-tables.head-cell>
            <x-tables.head-cell>Data Compra</x-tables.head-cell>
            <x-tables.head-cell>Veículo de Terceiro</x-tables.head-cell>
            <x-tables.head-cell>Veículo ativo</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($veiculos as $index => $item)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $item->placa ? $item->placa : 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->filialVeiculo ? $item->filialVeiculo->name : 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->baseVeiculo ? $item->baseVeiculo->descricao_base : 'Não Informado' }}
                    </x-tables.cell>
                    <x-tables.cell>{{ $item->categoriaVeiculo->descricao_categoria ?? 'Não Informado' }}
                    </x-tables.cell>
                    <x-tables.cell>{{ $item->renavam ? $item->renavam : 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->modelo ? $modelo : 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($item->data_compra, 'd/m/Y') ?: 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->is_terceiro ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->situacao_veiculo ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell>
                        <span id="status-badge-{{ $item->status->id ?? 'unknown' }}"
                            class="px-2 py-1 text-xs font-medium rounded-full 
                        @if (isset($item->status->descricao) && $item->status->descricao == 'Finalizado') bg-green-100 text-green-800
                        @elseif (isset($item->status->descricao) && $item->status->descricao == 'Pendente') bg-red-100 text-red-800 @endif">
                            {{ $item->status->descricao ?? 'Não Informado' }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.veiculos.show', $item->id) }}"
                                title="Visualizar {{ $item->placa ?: 'veículo' }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a>

                            <a href="{{ route('admin.veiculos.edit', $item->id) }}"
                                title="Editar {{ $item->placa ?: 'veículo' }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25]))
                                <button type="button" onclick="destroyVeiculo({{ $item->id }})"
                                    title="Excluir {{ $item->placa ?: 'veículo' }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>

                    <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                        type="error" title="Confirmar desativação">
                        Tem certeza que deseja desativar esse Veiculo <b class="title"></b>? <br>
                        Esta ação não pode ser desfeita. <br>
                        <x-bladewind::button name="botao-delete" type="button" color="red"
                            onclick="confirmarExclusao({{ $item->id }})" class="mt-3 text-white">
                            Desativar
                        </x-bladewind::button>
                    </x-bladewind.modal>


                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    <div class="mt-4">
        {{ $veiculos->links() }}
    </div>
</div>
