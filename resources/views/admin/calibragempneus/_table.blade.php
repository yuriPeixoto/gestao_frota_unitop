<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>
                <!-- Ações -->
            </x-tables.head-cell>
            <x-tables.head-cell>Cód. Calibragem</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Qtd Calibragem</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($calibragens as $index => $calibragem)
            <x-tables.row :index="$index">
                {{-- Ações --}}
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.calibragempneus.edit', $calibragem->id_calibragem_pneu) }}"
                            title="Editar">
                            <x-icons.edit class="w-4 h-4 text-blue-600" />
                        </a>
                    </div>
                </x-tables.cell>

                {{-- Cód. Calibragem --}}
                <x-tables.cell>{{ $calibragem->id_calibragem_pneu }}</x-tables.cell>

                {{-- Datas --}}
                <x-tables.cell>{{ format_date($calibragem->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($calibragem->data_alteracao) }}</x-tables.cell>

                {{-- Placa --}}
                <x-tables.cell>{{ $calibragem->veiculo->placa ?? '-' }}</x-tables.cell>

                {{-- Qtd Calibragem (placeholder) --}}
                <x-tables.cell>{{ $calibragem->pneus_count }}</x-tables.cell>



                {{-- Usuário --}}
                <x-tables.cell>{{ $calibragem->user->name ?? 'Não informado' }}</x-tables.cell>

                {{-- Filial --}}
                <x-tables.cell>{{ $calibragem->filial->name ?? '-' }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    {{-- Paginação --}}
    <div class="mt-4">
        {{ $calibragens->appends(request()->query())->links() }}
    </div>
</div>