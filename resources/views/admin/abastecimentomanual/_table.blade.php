<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. <br>Abastecimento</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Abast.</x-tables.head-cell>
            <x-tables.head-cell>Nº NF</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Equipamento</x-tables.head-cell>

            {{-- Coluna Ações - Só aparece se usuário tem pelo menos uma permissão de ação --}}
            @if (auth()->user()->hasAnyPermission(['editar_abastecimentomanual', 'excluir_abastecimentomanual']))
                <x-tables.head-cell>Ações</x-tables.head-cell>
            @endif
        </x-tables.header>

        <x-tables.body>
            @forelse ($abastecimentos as $index => $abastecimento)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $abastecimento->id_abastecimento }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->placa }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->data_abastecimento?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $abastecimento->numero_nota_fiscal }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->nome_fornecedor }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->descricao_departamento }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $abastecimento->descricao_tipo }}</x-tables.cell>

                    {{-- Coluna Ações com Controle de Permissões --}}
                    @if (auth()->user()->hasAnyPermission(['editar_abastecimentomanual', 'excluir_abastecimentomanual']))
                        <x-tables.cell>
                            <div class="flex items-center space-x-2">
                                {{-- Botão Editar - Protegido por Permissão --}}
                                @can('editar_abastecimentomanual')
                                    <a href="{{ route('admin.abastecimentomanual.edit', $abastecimento->id_abastecimento) }}"
                                        class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        title="Editar">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </a>
                                @endcan

                                {{-- Botão Excluir - Protegido por Permissão + Regra Adicional --}}
                                @can('excluir_abastecimentomanual')
                                    @if (auth()->user()->isSuperuser() ||
                                            \App\Models\AbastecimentoManual::usuarioTemAutorizacaoEspecial(auth()->user()->id, 'excluir_abastecimento'))
                                        <button type="button"
                                            onclick="confirmarExclusao({{ $abastecimento->id_abastecimento }})"
                                            class="inline-flex items-center rounded-full border border-transparent bg-red-600 p-1 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            title="Excluir">
                                            <x-icons.trash class="h-3 w-3" />
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </x-tables.cell>
                    @endif
                </x-tables.row>
            @empty
                {{-- Ajustar número de colunas dinamicamente --}}
                @php
                    $totalCols = 8; // Colunas básicas
                    if (
                        auth()
                            ->user()
                            ->hasAnyPermission(['editar_abastecimentomanual', 'excluir_abastecimentomanual'])
                    ) {
                        $totalCols = 9; // + coluna ações
                    }
                @endphp
                <x-tables.empty cols="{{ $totalCols }}" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $abastecimentos->links() }}
    </div>
</div>
