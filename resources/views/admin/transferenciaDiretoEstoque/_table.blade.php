<div class="results-table">


    <x-tables.table>
        <x-tables.header>
            {{-- Ações --}}
            <x-tables.head-cell>
                <!-- Ações -->
            </x-tables.head-cell>
            <x-tables.head-cell>Cód. Transferência</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Observação</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Filial Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($results as $index => $result)
            <x-tables.row :index="$index">
                {{-- Ações --}}
                <x-tables.cell>
                    @php
                    $filialUsuario = Auth::user()->filial_id ?? null;
                    @endphp

                    <div class="flex gap-2">
                        @php
                        $statusNormalized = strtoupper(trim(\Illuminate\Support\Str::ascii($result->status ?? '')));
                        $filialId = $result->filial ?? ($result->filial_relacao->id ?? null);
                        @endphp

                        @if ($filialId != 1 && $statusNormalized != 'AGUARDANDO TRANSFERENCIA' && $statusNormalized ===
                        'INICIADA')
                        <x-tooltip content="Solicitar Transferência" placement="bottom">
                            <button type="button"
                                onclick="abrirModalTransferencia2({{ $result->id_transferencia_direta_estoque }})">
                                <x-icons.arrow-path-rounded-square
                                    class="w-5 h-5 text-black-500 hover:text-black-700" />
                            </button>
                        </x-tooltip>
                        @endif

                        {{-- Botão Baixar Estoque (filial matriz) --}}
                        @if ($filialId == 1)
                        <x-tooltip content="Baixar Estoque" placement="bottom">
                            <a
                                href="{{ route('admin.transferenciaDiretoEstoque.baixarview', $result->id_transferencia_direta_estoque) }}">
                                <x-icons.inventory class="w-5 h-5" />
                            </a>
                        </x-tooltip>
                        @endif


                        {{-- Botão Enviar Transferência (filial matriz) --}}
                        @if ($filialId == 1 && $statusNormalized == 'EM BAIXA')
                        <x-tooltip content="Enviar Transferência" placement="bottom">
                            <a
                                href="{{ route('admin.transferenciaDiretoEstoque.envio', $result->id_transferencia_direta_estoque) }}">
                                <x-icons.refresh class="w-5 h-5" />
                            </a>
                        </x-tooltip>
                        @endif


                        {{-- Botão Confirmar Recebimento (filial solicitante) --}}
                        @if ($filialId != 1 && $statusNormalized === 'FINALIZADA')

                        <x-tooltip content="Confirmar Recebimento de Mercadoria" placement="bottom">
                            <a
                                href="{{ route('admin.transferenciaDiretoEstoque.confirmarRecebimento', $result->id_transferencia_direta_estoque) }}">
                                <x-icons.check class="w-5 h-5" />
                            </a>
                        </x-tooltip>
                        @endif


                        {{-- Ações sempre visíveis --}}
                        <x-tooltip content="Imprimir" placement="bottom">
                            <a
                                href="{{ route('admin.transferenciaDiretoEstoque.gerarPDF', $result->id_transferencia_direta_estoque) }}">
                                <x-icons.print class="w-5 h-5" />
                            </a>
                        </x-tooltip>

                        <x-tooltip content="Visualizar" placement="bottom">
                            <button onclick="abrirModalTransferencia({{ $result->id_transferencia_direta_estoque }})">
                                <x-icons.eye class="w-5 h-5 text-black-500 hover:text-black-700" />
                            </button>
                        </x-tooltip>

                        <x-tooltip content="Editar" placement="bottom">
                            <a
                                href="{{ route('admin.transferenciaDiretoEstoque.edit', $result->id_transferencia_direta_estoque) }}">
                                <x-icons.pencil class="w-5 h-5" />
                            </a>
                        </x-tooltip>
                    </div>

                </x-tables.cell>

                {{-- Demais colunas --}}
                <x-tables.cell>{{ $result->id_transferencia_direta_estoque }}</x-tables.cell>
                <x-tables.cell>{{ $result->status }}</x-tables.cell>
                <x-tables.cell>{{ $result->usuario?->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $result->departamento->descricao_departamento ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $result->observacao }}</x-tables.cell>
                <x-tables.cell>{{ $result->getRelation('filial')->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $result->filial_solicita_->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ format_date($result->data_inclusao) }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhuma transferência encontrada." />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    {{-- Paginação --}}
    <div class="mt-4">
        {{ $results->appends(request()->query())->links() }}
    </div>
    <!-- REMOVA Alpine.js e use apenas isso no HTML -->
    <div id="modalTransferencia" class="hidden fixed inset-0 z-40 bg-opacity-80 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
            <h2 class="text-xl font-semibold mb-4">Visualizar Transferência</h2>

            <div id="conteudoModalTransferencia" class="overflow-y-auto max-h-[70vh]">
                <p class="text-gray-500">Carregando...</p>
            </div>

            <button onclick="fecharModalTransferencia()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                Fechar
            </button>
        </div>
    </div>



    <script>
        function abrirModalTransferencia(id) {
            const modal = document.getElementById('modalTransferencia');
            const conteudo = document.getElementById('conteudoModalTransferencia');

            modal.classList.remove('hidden');
            conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

            fetch(`/admin/transferenciaDiretoEstoque/visualizar-modal/${id}`)
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
    <script>
        const button = document.getElementById('menu-button');
        const dropdown = document.getElementById('dropdown-menu');

        button.addEventListener('click', () => {
            dropdown.classList.toggle('hidden');
        });

        // Fecha o menu ao clicar fora
        document.addEventListener('click', (event) => {
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    <script>
        function abrirModalTransferencia2(id) {
            const modal = document.getElementById('modalTransferencia');
            const conteudo = document.getElementById('conteudoModalTransferencia');

            modal.classList.remove('hidden');
            conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

            fetch(`/admin/transferenciaDiretoEstoque/recebimento/${id}`)
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