<!-- Tabela de Solicitações -->
<div class="overflow-hidden rounded-lg bg-white shadow-md">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>ID</x-tables.head-cell>
            <x-tables.head-cell>Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Data</x-tables.head-cell>
            <x-tables.head-cell>Tipo Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse($solicitacoes as $index => $solicitacao)
                <x-tables.row :index="$index">
                    <x-tables.cell class="font-medium text-gray-900">
                        {{ $solicitacao->id_solicitacoes_compras }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $solicitacao->solicitante->name ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $solicitacao->departamento->descricao_departamento ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $solicitacao->data_inclusao ? $solicitacao->data_inclusao->format('d/m/Y') : 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        @if ($solicitacao->tipo_solicitacao == 1)
                            Solicitação de Produto
                        @elseif ($solicitacao->tipo_solicitacao == 2)
                            Solicitação de Serviço
                        @else
                            N/A
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $solicitacao->situacao_compra }}
                    </x-tables.cell>
                    <x-tables.cell>
                        <div>
                            <!-- Visualizar -->
                            <a href="{{ route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras) }}"
                                title="Visualizar"
                                class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <x-icons.eye class="h-3 w-3" />
                            </a>

                            <!-- Editar (apenas para solicitações não aprovadas/rejeitadas) -->
                            @if ($solicitacao->podeSerEditada())
                                @can('update', $solicitacao)
                                    <a href="{{ route('admin.compras.solicitacoes.edit', $solicitacao->id_solicitacoes_compras) }}"
                                        title="Editar"
                                        class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </a>
                                @endcan
                            @endif

                            <!-- Aprovar (apenas para pending) -->
                            @if ($solicitacao->podeSerAprovada())
                                @can('approve', $solicitacao)
                                    <button onclick="abrirModalAprovacao({{ $solicitacao->id_solicitacoes_compras }})"
                                        title="Aprovar"
                                        class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <x-icons.check class="h-3 w-3" />
                                    </button>
                                @endcan
                            @endif

                            <!-- Rejeitar (apenas para pendentes) -->
                            @if ($solicitacao->podeSerAprovada())
                                @can('reject', $solicitacao)
                                    <button onclick="abrirModalRejeicao({{ $solicitacao->id_solicitacoes_compras }})"
                                        title="Rejeitar"
                                        class="inline-flex items-center rounded-full border border-transparent bg-red-600 p-1 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        <x-icons.x-mark class="h-3 w-3" />
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="7" message="Nenhuma solicitação de compra encontrada" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <!-- Paginação -->
    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        {{ $solicitacoes->links() }}
    </div>

    <!-- Botões de ação em massa e exportação -->
    <div class="flex justify-between border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6">
        <div>
            <!-- Botão para selecionar itens quando implementar ações em massa -->
        </div>
        <div class="flex space-x-2">
            {{-- @can('visualizar_relatorios_compras')
                <a href="{{ route('admin.compras.solicitacoes.exportCsv', request()->query()) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Exportar CSV
                </a>
            @endcan --}}
        </div>
    </div>
</div>
