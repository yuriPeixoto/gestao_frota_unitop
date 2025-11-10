<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Início</x-tables.head-cell>
            <x-tables.head-cell>Data Fim</x-tables.head-cell>
            <x-tables.head-cell>Valor Meta</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Tipo Equipamento</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($metaTipoEquipamentos as $index => $metaTipoEquipamento)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $metaTipoEquipamento->id_meta }}</x-tables.cell>
                    <x-tables.cell nowrap>
                        @if ($metaTipoEquipamento->data_inclusao instanceof \DateTime)
                            {{ $metaTipoEquipamento->data_inclusao->format('d/m/Y H:i') }}
                        @else
                            {{ is_string($metaTipoEquipamento->data_inclusao) ? date('d/m/Y H:i', strtotime($metaTipoEquipamento->data_inclusao)) : '' }}
                        @endif
                    </x-tables.cell>
                    <x-tables.cell nowrap>
                        @if ($metaTipoEquipamento->data_inicial instanceof \DateTime)
                            {{ $metaTipoEquipamento->data_inicial->format('d/m/Y') }}
                        @else
                            {{ is_string($metaTipoEquipamento->data_inicial) ? date('d/m/Y', strtotime($metaTipoEquipamento->data_inicial)) : '' }}
                        @endif
                    </x-tables.cell>
                    <x-tables.cell nowrap>
                        @if ($metaTipoEquipamento->data_final instanceof \DateTime)
                            {{ $metaTipoEquipamento->data_final->format('d/m/Y') }}
                        @else
                            {{ is_string($metaTipoEquipamento->data_final) ? date('d/m/Y', strtotime($metaTipoEquipamento->data_final)) : '' }}
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>R$ {{ number_format($metaTipoEquipamento->vlr_meta, 2, ',', '.') }}</x-tables.cell>
                    <x-tables.cell>{{ $metaTipoEquipamento->filial->name ?? 'Filial não encontrada' }}</x-tables.cell>
                    <x-tables.cell>{{ $metaTipoEquipamento->tipoEquipamento->descricao_tipo ?? 'Tipo não encontrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $metaTipoEquipamento->ativo ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.metatipoequipamentos.edit', $metaTipoEquipamento->id_meta) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button" onclick="confirmarExclusao({{ $metaTipoEquipamento->id_meta }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $metaTipoEquipamentos->links() }}
    </div>
</div>