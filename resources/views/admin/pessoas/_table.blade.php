<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Nome</x-tables.head-cell>
            <x-tables.head-cell>RG</x-tables.head-cell>
            <x-tables.head-cell>CPF</x-tables.head-cell>
            <x-tables.head-cell>CNH</x-tables.head-cell>
            <x-tables.head-cell>Tipo CNH</x-tables.head-cell>
            <x-tables.head-cell>Validade CNH</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($pessoas as $index => $pessoa)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $pessoa->id_pessoal }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->nome }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->rg }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->cpf }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->cnh }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->tipo_cnh ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($pessoa->validade_cnh, 'd/m/Y') ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $pessoa->ativo ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($pessoa->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($pessoa->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.pessoas.edit', $pessoa->id_pessoal) }}"
                                title="Editar {{ $pessoa->nome }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button" title="Excluir {{ $pessoa->nome }}"
                                    onclick="confirmarExclusao({{ $pessoa->id_pessoal }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>


                </x-tables.row>
            @empty
                <x-tables.empty cols="12" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $pessoas->links() }}
    </div>
</div>
