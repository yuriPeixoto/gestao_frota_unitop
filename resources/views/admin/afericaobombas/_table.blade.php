<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Abastecimento<br>Integração</x-tables.head-cell>
            <x-tables.head-cell>Bomba</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Volume</x-tables.head-cell>
            <x-tables.head-cell>Data</x-tables.head-cell>
            <x-tables.head-cell>Entrada</x-tables.head-cell>
            {{-- Verificar se tem permissão para gerar entrada --}}
            @can('criar_entradaafericaoabastecimento')
            <x-tables.head-cell>Ações</x-tables.head-cell>
            @endcan
        </x-tables.header>

        <x-tables.body>
            @forelse ($afericoes as $index => $afericao)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $afericao->id_abastecimento_integracao }}</x-tables.cell>
                <x-tables.cell>{{ $afericao->descricao_bomba ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $afericao->placa }}</x-tables.cell>
                <x-tables.cell>{{ number_format($afericao->volume, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ is_object($afericao->data_inicio) ? $afericao->data_inicio->format('d/m/Y H:i')
                    : (is_string($afericao->data_inicio) && $afericao->data_inicio ? date('d/m/Y H:i',
                    strtotime($afericao->data_inicio)) : 'N/A') }}</x-tables.cell>
                <x-tables.cell>
                    @if (in_array($afericao->id_abastecimento_integracao, $entradasRealizadas))
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Sim
                    </span>
                    @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Não
                    </span>
                    @endif
                </x-tables.cell>
                {{-- Coluna Ações - Só aparece se usuário tem permissão para criar entrada --}}
                @can('criar_entradaafericaoabastecimento')
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @if (!in_array($afericao->id_abastecimento_integracao, $entradasRealizadas))
                        <a href="{{ route('admin.afericaobombas.edit', $afericao->id_abastecimento_integracao) }}"
                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            title="Gerar entrada por aferição">
                            <x-icons.gas-pump class="h-3 w-3 mr-1 text-white" />
                            Gerar Entrada
                        </a>
                        @else
                        <span class="text-gray-400 text-xs italic">Entrada já realizada</span>
                        @endif
                    </div>
                </x-tables.cell>
                @endcan
            </x-tables.row>
            @empty
            @php
            $totalCols = 6; // Colunas básicas
            if(auth()->user()->can('criar_entradaafericaoabastecimento')) {
            $totalCols = 7; // + coluna ações
            }
            @endphp
            <x-tables.empty cols="{{ $totalCols }}" message="Nenhuma aferição encontrada" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $afericoes->links() }}
    </div>
</div>