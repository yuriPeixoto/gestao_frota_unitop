<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Categoria</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Categoria</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Quantide Veiculos</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($categoriaVeiculos as $index => $categoria)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- <a href="{{ route('admin.tipocategorias.show', $categoria->id_categoria) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a> --}}
                            <a href="{{ route('admin.tipocategorias.edit', $categoria->id_categoria) }}" title="Editar"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                            <button type="button" onclick="confirmarExclusao({{ $categoria->id_categoria }})"
                                title="Excluir"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $categoria->id_categoria }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($categoria->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($categoria->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $categoria->descricao_categoria }}</x-tables.cell>
                    <x-tables.cell>{{ $categoria->ativo ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell class="text-center">{{ $categoria->veiculo_count }}</x-tables.cell>

                </x-tables.row>
            @empty
                <x-tables.empty cols="7" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $categoriaVeiculos->links() }}
    </div>
    @push('scripts')
        @include('admin.tipocategorias._scripts')
    @endpush
</div>
