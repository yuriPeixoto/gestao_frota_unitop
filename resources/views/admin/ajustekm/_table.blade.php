<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. Km<br>Abastecimento</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Abast.</x-tables.head-cell>
            <x-tables.head-cell>Veículo</x-tables.head-cell>
            <x-tables.head-cell>Km<br>Abastecimento</x-tables.head-cell>
            <x-tables.head-cell>Permissão<br>KM Manual</x-tables.head-cell>
            <x-tables.head-cell>Tipo<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Cód. Abastecimento ATS</x-tables.head-cell>

            {{-- Coluna Ações - Só aparece se usuário tem permissão de editar --}}
            @can('editar_ajustekmabastecimento')
            <x-tables.head-cell>Ações</x-tables.head-cell>
            @endcan
        </x-tables.header>

        <x-tables.body>
            @forelse ($ajustes as $index => $ajuste)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $ajuste->id_ajuste_km_abastecimento }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $ajuste->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $ajuste->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $ajuste->data_abastecimento?->format('d/m/Y') }}</x-tables.cell>
                <x-tables.cell>{{ $ajuste->veiculo->placa ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ number_format($ajuste->km_abastecimento, 0, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ $ajuste->id_permissao_km_manual ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $ajuste->tipo_combustivel }}</x-tables.cell>
                <x-tables.cell>{{ $ajuste->id_abastecimento_ats ?? 'N/A' }}</x-tables.cell>

                {{-- Coluna Ações com Controle de Permissões --}}
                @can('editar_ajustekmabastecimento')
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        {{-- Botão Editar - Protegido por Permissão --}}
                        <a href="{{ route('admin.ajustekm.edit', $ajuste->id_ajuste_km_abastecimento) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            title="Editar">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>
                    </div>
                </x-tables.cell>
                @endcan
            </x-tables.row>
            @empty
            {{-- Ajustar número de colunas dinamicamente --}}
            @php
            $totalCols = 9; // Colunas básicas
            if(auth()->user()->can('editar_ajustekmabastecimento')) {
            $totalCols = 10; // + coluna ações
            }
            @endphp
            <x-tables.empty cols="{{ $totalCols }}" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $ajustes->links() }}
    </div>
</div>