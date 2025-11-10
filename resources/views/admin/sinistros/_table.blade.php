<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Sinistro</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Motorista</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Ocorrência</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Responsabilidade</x-tables.head-cell>
            <x-tables.head-cell>Orgão Registro</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($sinistros as $index => $sinistro)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- <a href="{{ route('admin.sinistros.show', $sinistro->id_sinistro) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            title="Visualizar">
                            <x-icons.eye class="h-3 w-3" />
                        </a> --}}
                            @if (auth()->user()->is_superuser || $sinistro->status != 'FINALIZADA' || in_array(auth()->user()->id, [2, 269]))
                                <a href="{{ route('admin.sinistros.edit', $sinistro->id_sinistro) }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    title="Editar">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            @endif

                            @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [2, 269]))
                                <button type="button" onclick="confirmarExclusao({{ $sinistro->id_sinistro }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $sinistro->id_sinistro }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->veiculo->placa ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->filial->name ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->pessoal->nome ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($sinistro->data_sinistro, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->status }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->situacaoAtual->descricao_situacao ?? 'Sem situação' }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->responsabilidade_sinistro ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $sinistro->orgao->descricao_tipo_orgao ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($sinistro->data_inclusao) }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $sinistros->links() }}
    </div>
</div>
