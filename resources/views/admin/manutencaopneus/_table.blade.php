<div class="results-table mt-4">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód.OS</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Situação Envio</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($manutencaoPneus as $index => $manutencaoPneu)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @if ($manutencaoPneu->situacao_envio != 'Aguardando Aprovação' &&
                        $manutencaoPneu->situacao_envio != 'Pneus Aprovado para Saída')
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.manutencaopneus.edit', $manutencaoPneu->id_manutencao_pneu) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        @endif


                        @if ($manutencaoPneu->is_borracharia && empty($manutencaoPneu->id_borracheiro))
                        <x-tooltip content="Assumir Manutenção">
                            <a href="{{ route('admin.manutencaopneus.onassumir', $manutencaoPneu->id_manutencao_pneu) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.user-check class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        @endif

                        <x-tooltip content="Imprimir Relação">
                            <a href="{{ route('admin.manutencaopneus.imprimir', $manutencaoPneu->id_manutencao_pneu) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.print class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        <x-tooltip content="Ver movimentação">
                            <a href="{{route('admin.manutencaopneus.getStatus', $manutencaoPneu->id_manutencao_pneu)}}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a>
                        </x-tooltip>

                        <x-tooltip content="Excluir">
                            <form
                                action="{{ route('admin.manutencaopneus.destroy', $manutencaoPneu->id_manutencao_pneu) }}"
                                method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Tem certeza que deseja excluir?')"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />

                                </button>
                            </form>
                        </x-tooltip>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $manutencaoPneu->id_manutencao_pneu }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $manutencaoPneu->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $manutencaoPneu->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoPneu->filialPneu->name }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $manutencaoPneu->fornecedor->nome_fornecedor }}</x-tables.cell>
                <x-tables.cell>{{ $manutencaoPneu->situacao_envio}}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="6" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $manutencaoPneus->links() }}
    </div>
</div>