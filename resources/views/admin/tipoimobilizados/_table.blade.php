<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Imobilizado</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoImobilizado as $index => $Imobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <a href="{{ route('admin.tipoimobilizados.edit', $Imobilizado->id_tipo_imobilizados) }}"
                                alt="Editar">
                                <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                            </a>
                        </div>
                        <div>
                            <button type="button" onclick="confirmarExclusao({{ $Imobilizado->id_tipo_imobilizados }})">
                                <x-icons.trash class="w-4 h-4 mr-2 text-red-600" />
                            </button>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $Imobilizado->id_tipo_imobilizados }}</x-tables.cell>
                <x-tables.cell>{{ format_date($Imobilizado->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($Imobilizado->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $Imobilizado->descricao_tipo_imobilizados }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoImobilizado->links() }}
    </div>
    @push('scripts')
    @include('admin.tipoimobilizados._scripts')
    @endpush
</div>