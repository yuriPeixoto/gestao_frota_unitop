<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Data Inicial</x-tables.head-cell>
            <x-tables.head-cell>Data Final</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>

        </x-tables.header>
        <x-tables.body>
            @forelse ($listagem as $premio)
            <x-tables.row>

                <x-tables.cell>{{ $premio->id_premio_superacao }}</x-tables.cell>
                <x-tables.cell>{{ format_date($premio->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ $premio->users->name ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $premio->situacao}}</x-tables.cell>
                <x-tables.cell>{{ format_date($premio->data_inicial)}}</x-tables.cell>
                <x-tables.cell>{{ format_date($premio->data_final)}}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <x-tooltip content="Excluir">
                            <form action="{{ route('admin.premiosuperacao.delete', $premio->id_premio_superacao) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Reprocessar">
                            <form
                                action="{{ route('admin.premiosuperacao.reprocessar', $premio->id_premio_superacao) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza Reprocessar prêmio ?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <x-icons.refresh class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Finalizar Prêmio">
                            <form action="{{ route('admin.premiosuperacao.finalizar', $premio->id_premio_superacao) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja Finalizar Prêmio ?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-archive" viewBox="0 0 16 16">
                                        <path
                                            d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5" />
                                    </svg>
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Confirmar Pagamento">
                            <form action="{{ route('admin.premiosuperacao.confirmar', $premio->id_premio_superacao) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que Confirmar Pagamento ?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-currency-dollar" viewBox="0 0 16 16">
                                        <path
                                            d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z" />
                                    </svg>
                                </button>
                            </form>
                        </x-tooltip>
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="6" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    {{ $listagem->links() }}
</div>
</div>