<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Ações</x-tables.head-cell>
        <x-tables.head-cell>Placa</x-tables.head-cell>
        <x-tables.head-cell>KM</x-tables.head-cell>
        <x-tables.head-cell>Média</x-tables.head-cell>
        <x-tables.head-cell>Data Inicial</x-tables.head-cell>
        <x-tables.head-cell>Data Final</x-tables.head-cell>
    </x-tables.header>

    <x-tables.body>
        @forelse($listagem as $registro)
        <x-tables.row>
            <x-tables.cell>
                <a href="{{route('admin.manutencaopremio.editKm', $registro->id_distancia_sem)}}"
                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    <x-icons.pencil class="h-3 w-3" />
                </a>
            </x-tables.cell>
            <x-tables.cell>{{ $registro->veiculo->placa ?? '-' }}</x-tables.cell>
            <x-tables.cell>{{ $registro->km_sem_mot ?? '-' }}</x-tables.cell>
            <x-tables.cell>{{ $registro->media ?? '-' }}</x-tables.cell>
            <x-tables.cell>{{ format_date($registro->data_inicial) ?? '-' }}</x-tables.cell>
            <x-tables.cell>{{ format_date($registro->data_final) ?? '-' }}</x-tables.cell>
        </x-tables.row>
        @empty
        <x-tables.empty cols="6" message="Nenhum registro encontrado" />
        @endforelse
    </x-tables.body>
</x-tables.table>