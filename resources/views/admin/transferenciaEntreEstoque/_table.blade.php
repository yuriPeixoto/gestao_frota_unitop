<div class="results-table">

    <x-tables.table>
        <x-tables.header>
            {{-- Ações --}}
            <x-tables.head-cell>Cód. Transferência</x-tables.head-cell>
            <x-tables.head-cell>Cód. Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Filial Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Filial Baixa</x-tables.head-cell>
            <x-tables.head-cell>Usuario Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Recebido</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($transferencias as $result)
                <x-tables.row>
                    <x-tables.cell> {{ $result->id_tranferencia }}</x-tables.cell>
                    <x-tables.cell></x-tables.cell>
                    <x-tables.cell>{{ $result->filial->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $result->filialBaixa->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $result->usuario->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $result->departamento->descricao_departamento ?? '' }}</x-tables.cell>
                    <x-tables.cell>
                        @if ($result->situacao == 'RECEBIDO PARCIAL')
                            <span
                                class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800"
                                title="{{ $result->observacao_inconsistencia ?? 'Recebido parcialmente' }}">
                                {{ $result->situacao }}
                                @if ($result->observacao_inconsistencia)
                                    <svg class="ml-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </span>
                        @elseif($result->situacao == 'RECEBIDO')
                            <span
                                class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                {{ $result->situacao }}
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                {{ $result->situacao ?? 'AGUARDANDO' }}
                            </span>
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>{{ $result->recebido == 1 ? 'sim' : '' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex justify-end space-x-2">
                            @if ($result->recebido == null)
                                @if ($result->relacaoSolicitacaoPecas && $result->relacaoSolicitacaoPecas->situacao === 'TRANSFERIDO')
                                    <a href="{{ route('admin.transferenciaEntreEstoque.edit', $result->id_tranferencia) }}"
                                        title="Confirmar recebimento"
                                        class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <x-icons.check class="h-3 w-3" />
                                    </a>
                                @elseif (!$result->relacaoSolicitacaoPecas)
                                    <a href="{{ route('admin.transferenciaEntreEstoque.edit', $result->id_tranferencia) }}"
                                        title="Confirmar recebimento"
                                        class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <x-icons.check class="h-3 w-3" />
                                    </a>
                                @endif
                            @endif

                            <button onclick="abrirModalTransferencia({{ $result->id_tranferencia }})"
                                class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-icons.eye class="h-3 w-3" />
                            </button>
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="10" message="Nenhuma transferência encontrada." />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    {{-- Paginação --}}
    <div class="mt-4">
        {{ $transferencias->links() }}
    </div>
    <!-- REMOVA Alpine.js e use apenas isso no HTML -->
    <div id="modalTransferencia" class="fixed inset-0 z-40 flex hidden items-center justify-center bg-opacity-80">
        <div class="relative w-full max-w-5xl rounded-lg bg-white p-6 shadow-lg">
            <h2 class="mb-4 text-xl font-semibold">Visualizar Transferência</h2>

            <div id="conteudoModalTransferencia" class="max-h-[70vh] overflow-y-auto">
                <p class="text-gray-500">Carregando...</p>
            </div>

            <button onclick="fecharModalTransferencia()" class="absolute right-3 top-3 text-red-500 hover:text-red-700">
                Fechar
            </button>
        </div>
    </div>

    <script>
        function abrirModalTransferencia(id) {
            if (!id || isNaN(Number(id))) {
                alert('ID inválido para abrir modal: ' + id);
                return;
            }
            const modal = document.getElementById('modalTransferencia');
            const conteudo = document.getElementById('conteudoModalTransferencia');

            modal.classList.remove('hidden');
            conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

            fetch(`/admin/transferencia-entre-estoque/visualizar/${id}`)
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao carregar dados');
                    return response.text();
                })
                .then(html => {
                    conteudo.innerHTML = html;
                })
                .catch(() => {
                    conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar a transferência.</p>';
                });
        }

        function fecharModalTransferencia() {
            const modal = document.getElementById('modalTransferencia');
            modal.classList.add('hidden');
        }
    </script>

</div>
