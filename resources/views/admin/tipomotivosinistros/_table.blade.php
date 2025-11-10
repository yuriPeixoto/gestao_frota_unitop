<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Motivo Sinistro</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Motivo Sinistro</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoMotivoSinistro as $index => $Sinistro)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <div>
                                <a href="{{ route('admin.tipomotivosinistros.edit', $Sinistro->id_motivo_cinistro) }}"
                                    alt="Editar">
                                    <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                </a>
                            </div>
                            <div>
                                <button type="button" onclick="confirmarExclusao({{ $Sinistro->id_motivo_cinistro }})">
                                    <x-icons.trash class="w-4 h-4 mr-2 text-red-600" />
                                </button>
                            </div>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $Sinistro->id_motivo_cinistro }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Sinistro->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Sinistro->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $Sinistro->descricao_motivo }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoMotivoSinistro->links() }}
    </div>
    @push('scripts')
        @include('admin.tipomotivosinistros._scripts')
    @endpush
</div>
