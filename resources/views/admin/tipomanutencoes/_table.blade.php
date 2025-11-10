<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Manutenção</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Manutenção</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoManutencao as $index => $Manutencao)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <div>
                                <a href="{{ route('admin.tipomanutencoes.edit', $Manutencao->id_tipo_manutencao) }}"
                                    alt="Editar">
                                    <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                </a>
                            </div>
                            <div>
                                <button type="button" onclick="confirmarExclusao({{ $Manutencao->id_tipo_manutencao }})">
                                    <x-icons.trash class="w-4 h-4 mr-2 text-red-600" />
                                </button>
                            </div>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $Manutencao->id_tipo_manutencao }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Manutencao->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Manutencao->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $Manutencao->tipo_manutencao_descricao }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoManutencao->links() }}
    </div>
    @push('scripts')
        @include('admin.tipomanutencoes._scripts')
    @endpush
</div>
