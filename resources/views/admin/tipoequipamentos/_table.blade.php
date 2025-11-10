<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. Tipo Equipamento</x-tables.head-cell>
            <x-tables.head-cell>Descrição do Equipamento</x-tables.head-cell>
            <x-tables.head-cell>Número de Eixos</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoequipamentos as $index => $tipoequipamento)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $tipoequipamento->id_tipo_equipamento }}</x-tables.cell>
                <x-tables.cell>{{ $tipoequipamento->descricao_tipo }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $tipoequipamento->numero_eixos }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($tipoequipamento->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($tipoequipamento->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.tipoequipamentos.edit', $tipoequipamento->id_tipo_equipamento) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        {{-- @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25])) --}}
                        <button type="button" onclick="confirmarExclusao({{ $tipoequipamento->id_tipo_equipamento }})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3 w-3" />
                        </button>
                        {{-- @endif --}}
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoequipamentos->links() }}
    </div>
</div>