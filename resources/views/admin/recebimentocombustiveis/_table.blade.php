<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Número NF</x-tables.head-cell>
            <x-tables.head-cell>NF2</x-tables.head-cell>
            <x-tables.head-cell>NF3</x-tables.head-cell>
            <x-tables.head-cell>NF4</x-tables.head-cell>
            <x-tables.head-cell>Data/Hora<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data/Hora<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Tanque</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Entrada</x-tables.head-cell>
            <x-tables.head-cell>Quantidade</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Tipo<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>

            {{-- Coluna Ações - Só aparece se usuário tem pelo menos uma permissão de ação --}}
            @if(auth()->user()->hasAnyPermission(['editar_recebimentocombustivel', 'excluir_recebimentocombustivel']))
            <x-tables.head-cell>Ações</x-tables.head-cell>
            @endif
        </x-tables.header>

        <x-tables.body>
            @forelse ($recebimentoCombustiveis as $index => $recebimento)
            <x-tables.row :index="$index" data-id="{{ $recebimento->id_recebimento_combustivel }}">
                <x-tables.cell>{{ $recebimento->id_recebimento_combustivel }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->numeronotafiscal }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->numero_nf2 }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->numero_nf3 }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->numero_nf4 }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $recebimento->data_inclusao ? $recebimento->data_inclusao->format('d/m/Y H:i')
                    : '' }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $recebimento->data_alteracao ? $recebimento->data_alteracao->format('d/m/Y
                    H:i') : '' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->tanque_nome ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->nome_fornecedor ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $recebimento->data_entrada ? date('d/m/Y',
                    strtotime($recebimento->data_entrada)) : '' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->quantidade ? number_format((float)$recebimento->quantidade, 2, ',', '.')
                    : '' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->filial_nome ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->tipo_combustivel_nome ?? 'Não Informado' }}</x-tables.cell>
                <x-tables.cell>{{ $recebimento->situacao_nf ?? 'Não Informado' }}</x-tables.cell>

                {{-- Coluna Ações com Controle de Permissões --}}
                @if(auth()->user()->hasAnyPermission(['editar_abastecimentomanual', 'excluir_abastecimentomanual']))
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @can('editar_recebimentocombustivel')
                        <a href="{{ route('admin.recebimentocombustiveis.edit', $recebimento->id_recebimento_combustivel) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>
                        @endcan

                        @can('excluir_recebimentocombustivel')
                        <button type="button"
                            onclick="confirmarExclusao({{ $recebimento->id_recebimento_combustivel }})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3 w-3" />
                        </button>
                        @endcan
                    </div>
                </x-tables.cell>
                @endif
            </x-tables.row>
            @empty
            <x-tables.empty cols="15" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $recebimentoCombustiveis->links() }}
    </div>
</div>