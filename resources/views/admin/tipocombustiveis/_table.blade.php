<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Combustivel</x-tables.head-cell>
            <x-tables.head-cell>Unidade de Medida</x-tables.head-cell>
            <x-tables.head-cell>NCM</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoCombustivel as $index => $combustivel)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- <a href="{{ route('admin.tipocombustiveis.show', $combustivel->id_tipo_combustivel) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a> --}}
                            <a href="{{ route('admin.tipocombustiveis.edit', $combustivel->id_tipo_combustivel) }}"
                                title="Editar"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                            <button type="button" onclick="confirmarExclusao({{ $combustivel->id_tipo_combustivel }})"
                                title="Excluir"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $combustivel->id_tipo_combustivel }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($combustivel->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($combustivel->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $combustivel->descricao }}</x-tables.cell>
                    <x-tables.cell>{{ $combustivel->unidade_medida }}</x-tables.cell>
                    <x-tables.cell>{{ $combustivel->ncm ?? '-' }}</x-tables.cell>

                </x-tables.row>
            @empty
                <x-tables.empty cols="7" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoCombustivel->links() }}
    </div>
    @push('scripts')
        @include('admin.tipocombustiveis._scripts')
    @endpush
</div>
