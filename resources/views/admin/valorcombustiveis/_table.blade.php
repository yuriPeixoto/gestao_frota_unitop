<div class="results-table">
    <x-tables.table class="text-xs">
        <x-tables.header>
            <x-tables.head-cell class="px-2 py-1">Cód. Preço<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Valor Combustível</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Valor de Venda Interno</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Valor de Venda Terceiro</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1 w-64">Bomba Combustível</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Tipo de Combustível</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Data Inicial</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Data Final</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Usuário</x-tables.head-cell>
            <x-tables.head-cell class="px-2 py-1">Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($valorCombustiveis as $index => $valorCombustivel)
            <x-tables.row :index="$index">
                <x-tables.cell class="px-2 py-1">{{ $valorCombustivel->id_valor_combustivel_terceiro }}</x-tables.cell>
                <x-tables.cell class="px-2 py-1" nowrap>{{ format_date($valorCombustivel->data_inclusao, 'd/m/y') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1">R$ {{ number_format($valorCombustivel->valor_diesel, 4, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1">R$ {{ number_format($valorCombustivel->valor_acrescimo, 4, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1">R$ {{ number_format($valorCombustivel->valor_terceiro, 4, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1 w-64">
                    {{ $valorCombustivel->bomba->descricao_bomba ?? 'Não Informado' }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1" nowrap
                    title="{{ $valorCombustivel->tipoCombustivel->descricao ?? 'Não Informado' }}">
                    @php
                    $descricaoTipo = $valorCombustivel->tipoCombustivel->descricao ?? 'Não Informado';
                    echo strlen($descricaoTipo) > 15 ? substr($descricaoTipo, 0, 15).'...' : $descricaoTipo;
                    @endphp
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1" nowrap>{{ format_date($valorCombustivel->data_inicio, 'd/m/y') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1" nowrap>{{ format_date($valorCombustivel->data_fim, 'd/m/y') }}
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1" nowrap
                    title="{{ $valorCombustivel->usuario->name ?? 'Não Informado' }}">
                    @php
                    $nomeUsuario = $valorCombustivel->usuario->name ?? 'Não Informado';
                    echo strlen($nomeUsuario) > 10 ? substr($nomeUsuario, 0, 10).'...' : $nomeUsuario;
                    @endphp
                </x-tables.cell>
                <x-tables.cell class="px-2 py-1">
                    <div class="flex items-center space-x-1">
                        <a href="{{ route('admin.valorcombustiveis.show', $valorCombustivel->id_valor_combustivel_terceiro) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            title="Visualizar">
                            <x-icons.eye class="h-3 w-3" />
                        </a>
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="11" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $valorCombustiveis->links() }}
    </div>
</div>