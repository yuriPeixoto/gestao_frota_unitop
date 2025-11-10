<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Nome Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Tipo Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>UF</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($fornecedores as $index => $fornecedor)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $fornecedor->id_fornecedor }}</x-tables.cell>
                <x-tables.cell>{{ $fornecedor->nome_fornecedor }} /{{ $fornecedor->cnpj_fornecedor}}</x-tables.cell>
                <x-tables.cell>{{ $fornecedor->tipoFornecedor->descricao_tipo ?? 'NÃO INFORMADO' }}</x-tables.cell>
                <x-tables.cell>{{ $fornecedor->endereco->first()?->uf?->uf ?? 'NÃO INFORMADO' }}</x-tables.cell>
                <x-tables.cell>{{ $fornecedor->filial->name ?? 'NÃO INFORMADO' }}</x-tables.cell>
                <x-tables.cell>{{ $fornecedor->is_ativo == 1 ? 'SIM' : 'NÃO' }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($fornecedor->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.fornecedores.edit', $fornecedor->id_fornecedor) }}"
                            title="Editar {{ $fornecedor->nome_fornecedor }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                        <button type="button" onclick="confirmarExclusao({{ $fornecedor->id_fornecedor }})"
                            title="Excluir {{ $fornecedor->nome_fornecedor }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3 w-3" />
                        </button>
                        @endif
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $fornecedores->links() }}
    </div>
</div>