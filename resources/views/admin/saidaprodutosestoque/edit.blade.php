<x-app-layout>
    @if (session('error'))
        <div class="alert-danger alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4">
            <ul class="list-inside list-disc text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Baixar Itens
            </h2>
        </div>
    </x-slot>
    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">

        <form method="POST" action="{{ route('admin.saidaprodutosestoque.finalizarbaixa') }}" class="space-y-4">
            @csrf
            <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
                        {{-- Código da Transferência (apenas visualização) --}}
                        <x-forms.input name="id_solicitacao_pecas" label="Cód."
                            value="{{ $requisicao->id_solicitacao_pecas ?? '' }}" readonly />
                        {{-- value="{{
                        $transferencia->id_transferencia_direta_estoque
                        ?? '' }}" readonly --}}

                        <x-forms.input name="id_ordem_servico" label="Ordem de Serviço:"
                            value="{{ $requisicao->id_ordem_servico ?? '' }}" readonly />

                        {{-- Usuário --}}
                        <x-forms.input name="usuario_abertura" label="Usuário" value="{{ auth()->user()->name }}"
                            disabled />

                        {{-- Filial --}}
                        <x-forms.input name="placa" label="Placa Veiculo" value="{{ $requisicao->placa ?? '' }}"
                            readonly />
                        {{-- Departamento --}}
                        <x-forms.input name="filial_name" label="Filial"
                            value="{{ auth()->user()->filial->name ?? '' }}" disabled />
                        <input type="hidden" id="id_filial_atual" value="{{ auth()->user()->filial->id }}">
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        {{-- Departamento --}}
                        <x-forms.input name="descricao_departamento" label="Departamento"
                            value="{{ auth()->user()->departamento->descricao_departamento }}" disabled />
                    </div>
                    {{-- Observações --}}
                    <div class="mt-4 w-full">
                        <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                        <textarea name="justificativa_de_finalizacao" id="observacao" rows="3"
                            class="focus:ring-indig o-500 h-40 w-full resize-none rounded-md border-gray-300 shadow-sm focus:border-indigo-500"></textarea>

                    </div>
                </div>

                {{-- Tabela de Produtos --}}
                <div>
                    <h3 class="mb-4 text-xl font-semibold leading-tight text-gray-800">Produtos</h3>
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>Cód</x-tables.head-cell>
                            <x-tables.head-cell>Produtos</x-tables.head-cell>
                            <x-tables.head-cell>Quantidade Solicitada</x-tables.head-cell>
                            <x-tables.head-cell>QTD Estoque</x-tables.head-cell>
                            <x-tables.head-cell>Quantidade Baixa Atual</x-tables.head-cell>
                            <x-tables.head-cell>Data Baixa Atual</x-tables.head-cell>
                            <x-tables.head-cell>Nova QTD Baixa</x-tables.head-cell>
                            <x-tables.head-cell>Nova Data Baixa</x-tables.head-cell>
                            <x-tables.head-cell>Ações</x-tables.head-cell>

                        </x-tables.header>
                        <x-tables.body>
                            @forelse ($itens as $index => $result)
                                <x-tables.row :index="$index"
                                    class="{{ $result->situacao_pecas === 'INATIVO' ? 'bg-red-50 border-red-200' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'bg-blue-50 border-blue-200' : '') }}">
                                    <x-tables.cell
                                        class="{{ $result->situacao_pecas === 'INATIVO' ? 'text-red-600' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-600' : '') }}">
                                        {{ $result->id_protudos }}
                                        @if ($result->situacao_pecas === 'INATIVO')
                                            <span
                                                class="ml-2 inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                INATIVO
                                            </span>
                                        @elseif ($result->situacao_pecas === 'COMPRAS')
                                            <span
                                                class="ml-2 inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                COMPRAS
                                            </span>
                                        @elseif ($result->situacao_pecas === 'EM SOLICITACAO')
                                            <span
                                                class="ml-2 inline-flex items-center rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                EM SOLICITAÇÃO
                                            </span>
                                        @endif
                                    </x-tables.cell>
                                    <x-tables.cell
                                        class="{{ $result->situacao_pecas === 'INATIVO' ? 'text-red-600' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-600' : '') }}">{{ $result->produto->descricao_produto }}</x-tables.cell>
                                    <x-tables.cell
                                        class="{{ $result->situacao_pecas === 'INATIVO' ? 'text-red-600' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-600' : '') }}">{{ $result->quantidade }}</x-tables.cell>
                                    <x-tables.cell>
                                        <span
                                            class="{{ $result->quantidade_estoque_matriz > 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                            {{ number_format($result->quantidade_estoque_filial, 2, ',', '.') }}
                                        </span>
                                    </x-tables.cell>
                                    <x-tables.cell
                                        class="{{ $result->situacao_pecas === 'INATIVO' ? 'text-red-600' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-600' : '') }}">{{ $result->quantidade_baixa }}</x-tables.cell>
                                    <x-tables.cell
                                        class="{{ $result->situacao_pecas === 'INATIVO' ? 'text-red-600' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-600' : '') }}">
                                        {{ $result->data_baixa ? \Carbon\Carbon::parse($result->data_baixa)->format('d/m/Y') : '' }}
                                    </x-tables.cell>
                                    <x-tables.cell>
                                        <input type="number"
                                            id="quantidade_baixa_{{ $result->id_produtos_solicitacoes }}"
                                            name="quantidade_baixa_{{ $result->id_produtos_solicitacoes }}"
                                            step="0.01" min="0" max="{{ $result->quantidade }}"
                                            value="{{ $result->quantidade_baixa }}"
                                            {{ ($result->quantidade_baixa && $result->quantidade_baixa > 0) || $result->situacao_pecas === 'INATIVO' || $result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'disabled' : '' }}
                                            class="{{ ($result->quantidade_baixa && $result->quantidade_baixa > 0) || $result->situacao_pecas === 'INATIVO' || $result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'bg-gray-100 cursor-not-allowed' : '' }} {{ $result->situacao_pecas === 'INATIVO' ? 'text-red-500' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-500 border-blue-300' : '') }} w-32 min-w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        @if ($result->situacao_pecas === 'INATIVO')
                                            <div class="mt-1 text-xs font-medium text-red-500">Item inativo</div>
                                        @elseif ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO')
                                            <div class="mt-1 text-xs font-medium text-blue-500">Para compras</div>
                                        @endif
                                    </x-tables.cell>
                                    <x-tables.cell>
                                        <input type="date" id="data_baixa_{{ $result->id_produtos_solicitacoes }}"
                                            name="data_baixa_{{ $result->id_produtos_solicitacoes }}"
                                            value="{{ $result->data_baixa ? date('Y-m-d', strtotime($result->data_baixa)) : date('Y-m-d') }}"
                                            {{ ($result->quantidade_baixa && $result->quantidade_baixa > 0) || $result->situacao_pecas === 'INATIVO' || $result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'disabled' : '' }}
                                            class="{{ ($result->quantidade_baixa && $result->quantidade_baixa > 0) || $result->situacao_pecas === 'INATIVO' || $result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'bg-gray-100 cursor-not-allowed' : '' }} {{ $result->situacao_pecas === 'INATIVO' ? 'text-red-500' : ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO' ? 'text-blue-500 border-blue-300' : '') }} w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        @if ($result->situacao_pecas === 'INATIVO')
                                            <div class="mt-1 text-xs font-medium text-red-500">Item inativo</div>
                                        @elseif ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO')
                                            <div class="mt-1 text-xs font-medium text-blue-500">Para compras</div>
                                        @endif
                                    </x-tables.cell>
                                    <x-tables.cell>
                                        <div class="flex gap-2">
                                            @if ($result->situacao_pecas === 'INATIVO')
                                                {{-- Item inativo - todos os botões desabilitados --}}
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-gray-400 px-3 py-1 text-sm text-white">
                                                    <x-icons.disk class="h-4 w-4" />
                                                    Inativo
                                                </button>
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-gray-400 px-3 py-1 text-sm text-white">
                                                    Indisponível
                                                </button>
                                            @elseif ($result->situacao_pecas === 'COMPRAS' || $result->situacao_pecas === 'EM SOLICITACAO')
                                                {{-- Item para compras - todos os botões desabilitados --}}
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-blue-400 px-3 py-1 text-sm text-white">
                                                    <x-icons.disk class="h-4 w-4" />
                                                    Compras
                                                </button>
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-gray-400 px-3 py-1 text-sm text-white">
                                                    Indisponível
                                                </button>
                                            @elseif (!$result->quantidade_baixa || $result->quantidade_baixa == 0)
                                                {{-- Item ativo sem baixa - pode gravar --}}
                                                <button type="button"
                                                    onclick="gravarBaixa({{ $result->id_produtos_solicitacoes }}, {{ $result->id_protudos }}, {{ $requisicao->id_solicitacao_pecas ?? 0 }})"
                                                    data-qtd-solicitada="{{ $result->quantidade }}"
                                                    data-qtd-estoque-matriz="{{ $result->quantidade_estoque_matriz }}"
                                                    class="btn-gravar flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1 text-sm text-white shadow-md transition duration-200 ease-in-out hover:bg-green-700">
                                                    <x-icons.disk class="h-4 w-4" />
                                                    Gravar
                                                </button>
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-gray-400 px-3 py-1 text-sm text-white">
                                                    Estornar
                                                </button>
                                            @else
                                                {{-- Item ativo com baixa - pode estornar --}}
                                                <button type="button" disabled
                                                    class="flex cursor-not-allowed items-center gap-1 rounded-lg bg-gray-400 px-3 py-1 text-sm text-white">
                                                    <x-icons.disk class="h-4 w-4" />
                                                    Baixado
                                                </button>
                                                <button type="button"
                                                    onclick="abrirModalEstorno({{ $result->id_produtos_solicitacoes }}, '{{ $result->produto->descricao_produto }}')"
                                                    class="flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1 text-sm text-white shadow-md transition duration-200 ease-in-out hover:bg-red-700">
                                                    Estornar
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

                </div>
                <div class="col-span-full mb-4 flex justify-end space-x-3">
                    <a href="{{ route('admin.saidaprodutosestoque.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                        Cancelar
                    </a>
                    <button type="submit" id="submit-form"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                        Finalizar Baixa
                    </button>

                </div>

            </div>
        </form>
    </div>

    <!-- Modal de Justificativa para Estorno -->
    <div id="modalEstorno"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm transition-all duration-300">
        <div class="mx-4 w-full max-w-md scale-95 transform rounded-lg bg-white p-6 shadow-2xl transition-all duration-300"
            id="modalContent">
            <div class="mb-4">
                <h3 class="flex items-center text-lg font-semibold text-gray-900">
                    <svg class="mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    Confirmar Estorno
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Produto: <span id="nomeProdutoEstorno" class="font-medium text-gray-800"></span>
                </p>
                <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-3">
                    <p class="flex items-center text-sm text-red-700">
                        <svg class="mr-2 h-4 w-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        Esta ação irá remover a baixa do item e marcá-lo como INATIVO na requisição.
                    </p>
                </div>
            </div>

            <div class="mb-6">
                <label for="justificativaEstorno" class="mb-2 block text-sm font-medium text-gray-700">
                    <span class="flex items-center">
                        <svg class="mr-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Justificativa do Estorno
                        <span class="ml-1 text-red-500">*</span>
                    </span>
                </label>
                <textarea id="justificativaEstorno" name="justificativaEstorno" rows="4"
                    placeholder="Digite o motivo detalhado do estorno..."
                    class="mt-1 w-full resize-none rounded-md border-gray-300 shadow-sm transition-colors duration-200 focus:border-red-500 focus:ring-red-500 sm:text-sm"
                    maxlength="500" required></textarea>
                <div class="mt-1 flex justify-between">
                    <div id="erroJustificativa" class="flex hidden items-center text-sm text-red-600">
                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        A justificativa é obrigatória (mínimo 5 caracteres).
                    </div>
                    <div class="text-xs text-gray-500">
                        <span id="caracteresCount">0</span>/500 caracteres
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                <button type="button" onclick="fecharModalEstorno()"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </button>
                <button type="button" onclick="confirmarEstorno()"
                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    Confirmar Estorno
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

@verbatim
    <script>
        let idProdutoParaEstorno = null;

        function abrirModalEstorno(idProduto, nomeProduto) {
            idProdutoParaEstorno = idProduto;
            document.getElementById('nomeProdutoEstorno').textContent = nomeProduto;
            document.getElementById('justificativaEstorno').value = '';
            document.getElementById('caracteresCount').textContent = '0';
            document.getElementById('erroJustificativa').classList.add('hidden');

            const modal = document.getElementById('modalEstorno');
            const modalContent = document.getElementById('modalContent');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Animação de entrada
            setTimeout(() => {
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);

            // Foco no campo de justificativa
            setTimeout(() => {
                document.getElementById('justificativaEstorno').focus();
            }, 150);
        }

        function fecharModalEstorno() {
            const modal = document.getElementById('modalEstorno');
            const modalContent = document.getElementById('modalContent');

            // Animação de saída
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                idProdutoParaEstorno = null;
            }, 300);
        }

        // Contador de caracteres e validação em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('justificativaEstorno');
            const contador = document.getElementById('caracteresCount');
            const erroDiv = document.getElementById('erroJustificativa');

            if (textarea && contador) {
                textarea.addEventListener('input', function() {
                    const length = this.value.length;
                    contador.textContent = length;

                    // Validação em tempo real
                    if (length < 5 && length > 0) {
                        erroDiv.classList.remove('hidden');
                        this.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
                        this.classList.remove('border-gray-300', 'focus:border-indigo-500',
                            'focus:ring-indigo-500');
                    } else {
                        erroDiv.classList.add('hidden');
                        this.classList.remove('border-red-300', 'focus:border-red-500',
                            'focus:ring-red-500');
                        this.classList.add('border-gray-300', 'focus:border-red-500', 'focus:ring-red-500');
                    }

                    // Mudar cor do contador quando próximo do limite
                    if (length > 450) {
                        contador.parentElement.classList.add('text-red-500');
                        contador.parentElement.classList.remove('text-gray-500');
                    } else {
                        contador.parentElement.classList.remove('text-red-500');
                        contador.parentElement.classList.add('text-gray-500');
                    }
                });
            }

            // Fechar modal com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('modalEstorno');
                    if (!modal.classList.contains('hidden')) {
                        fecharModalEstorno();
                    }
                }
            });

            // Fechar modal clicando no fundo
            document.getElementById('modalEstorno').addEventListener('click', function(e) {
                if (e.target === this) {
                    fecharModalEstorno();
                }
            });
        });

        function confirmarEstorno() {
            const justificativa = document.getElementById('justificativaEstorno').value.trim();

            if (!justificativa) {
                document.getElementById('erroJustificativa').classList.remove('hidden');
                return;
            }

            document.getElementById('erroJustificativa').classList.add('hidden');

            // Proceder com o estorno
            processarEstorno(idProdutoParaEstorno, justificativa);
        }

        function processarEstorno(id, justificativa) {
            const url = `/admin/saidaprodutosestoque/estornarBaixa/${id}`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        justificativa: justificativa
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Item estornado com sucesso!');
                        fecharModalEstorno();
                        location.reload();
                    } else {
                        alert('Erro ao estornar o item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao estornar o item:', error);
                    alert('Ocorreu um erro ao tentar estornar o item.');
                });
        }

        async function gravarBaixa(idProdutoSolic, idProduto, idRequisicao) {
            console.log('=== INÍCIO gravarBaixa ===', {
                idProdutoSolic,
                idProduto,
                idRequisicao
            });

            const qtdBaixaInput = document.getElementById(`quantidade_baixa_${idProdutoSolic}`);
            const dataBaixaInput = document.getElementById(`data_baixa_${idProdutoSolic}`);

            // Buscar o botão que foi clicado para pegar os dados
            const botaoGravar = document.querySelector(`button[onclick*="${idProdutoSolic}"].btn-gravar`);
            const qtdSolicitada = parseFloat(botaoGravar.dataset.qtdSolicitada);
            const qtdEstoqueMatriz = parseFloat(botaoGravar.dataset.qtdEstoqueMatriz);

            const qtdBaixa = qtdBaixaInput.value ? parseFloat(qtdBaixaInput.value) : 0;
            const dataBaixa = dataBaixaInput.value;

            console.log('Dados capturados:', {
                qtdBaixa,
                qtdSolicitada,
                qtdEstoqueMatriz,
                dataBaixa
            });

            if (!dataBaixa) {
                alert('Preencha a data da baixa.');
                return;
            }

            if (qtdBaixa < 0) {
                alert('A quantidade não pode ser negativa.');
                return;
            }

            // Permitir quantidade 0 apenas se for estoque zerado
            if (qtdBaixa === 0 && qtdEstoqueMatriz > 0) {
                alert('A quantidade deve ser maior que zero quando há estoque disponível.');
                return;
            }

            if (qtdBaixa > qtdSolicitada) {
                alert('A quantidade de baixa não pode ser maior que a quantidade solicitada.');
                return;
            }

            // NOVA VALIDAÇÃO: Se Nova QTD Baixa for menor que Quantidade Solicitada
            if (qtdBaixa < qtdSolicitada && qtdBaixa > 0) {
                const qtdRestante = qtdSolicitada - qtdBaixa;
                const mensagem =
                    `A Nova QTD Baixa (${qtdBaixa}) é menor que a Quantidade Solicitada (${qtdSolicitada}).\n` +
                    `Será baixado: ${qtdBaixa} do estoque disponível\n` +
                    `Quantidade restante: ${qtdRestante}\n\n` +
                    `Deseja colocar o restante (${qtdRestante}) como compra?`;

                const confirmarCompraRestante = confirm(mensagem);
                if (confirmarCompraRestante) {
                    // Proceder com a baixa da quantidade informada e marcar o restante para compras
                    const response = await processarBaixaComCompra(idProdutoSolic, idProduto, idRequisicao, qtdBaixa,
                        qtdSolicitada, dataBaixa);
                    return;
                } else {
                    return; // Cancelar a operação
                }
            }

            // Verificar se a quantidade de baixa é maior que o estoque da matriz
            let qtdBaixaFinal = qtdBaixa;
            let iniciarProcessoCompras = false;

            console.log('Verificando estoque:', {
                qtdBaixa,
                qtdEstoqueMatriz,
                qtdSolicitada,
                'qtdBaixa > qtdEstoqueMatriz': qtdBaixa > qtdEstoqueMatriz,
                'qtdBaixa == qtdSolicitada': qtdBaixa == qtdSolicitada
            });

            // NOVO CENÁRIO: COMPRA PARCIAL - quando Nova QTD Baixa = Quantidade Solicitada mas estoque insuficiente
            if (qtdBaixa == qtdSolicitada && qtdBaixa > qtdEstoqueMatriz && qtdEstoqueMatriz > 0) {
                const qtdParaCompras = qtdBaixa - qtdEstoqueMatriz;
                const mensagem =
                    `Você solicitou baixar toda a quantidade (${qtdBaixa}).\n` +
                    `Estoque disponível na matriz: ${qtdEstoqueMatriz}\n` +
                    `Quantidade para compras: ${qtdParaCompras}\n\n` +
                    `Deseja realizar COMPRA PARCIAL?\n` +
                    `- Será baixado: ${qtdEstoqueMatriz} (do estoque)\n` +
                    `- Será marcado para compras: ${qtdParaCompras}`;

                console.log('Perguntando sobre COMPRA PARCIAL:', mensagem);
                const confirmarCompraParcial = confirm(mensagem);

                if (confirmarCompraParcial) {
                    // Enviar a quantidade total digitada - o backend vai ajustar automaticamente
                    qtdBaixaFinal = qtdBaixa;
                    iniciarProcessoCompras = false; // Backend vai gerenciar automaticamente
                } else {
                    return; // Cancelar a operação
                }
            }
            // CENÁRIO ORIGINAL: quando quantidade é maior que estoque mas NÃO é igual à solicitada
            else if (qtdBaixa > qtdEstoqueMatriz) {
                const qtdRestante = qtdBaixa - qtdEstoqueMatriz;

                let mensagem = '';
                if (qtdEstoqueMatriz === 0) {
                    mensagem = `Não há estoque disponível na matriz.\n` +
                        `Deseja iniciar o processo de compras para toda a quantidade (${qtdBaixa})?`;
                } else {
                    mensagem =
                        `A quantidade solicitada (${qtdBaixa}) é maior que o estoque disponível na matriz (${qtdEstoqueMatriz}).\n` +
                        `Será baixado apenas ${qtdEstoqueMatriz} do estoque.\n` +
                        `Deseja iniciar o processo de compras para os ${qtdRestante} restantes?`;
                }

                console.log('Perguntando sobre processo de compras:', mensagem);
                const confirmarCompras = confirm(mensagem);

                console.log('Resposta do usuário:', confirmarCompras);

                if (confirmarCompras) {
                    qtdBaixaFinal = qtdEstoqueMatriz; // Baixar apenas o que tem em estoque (pode ser 0)
                    iniciarProcessoCompras = true;
                } else {
                    return; // Cancelar a operação
                }
            }

            console.log('Valores finais:', {
                qtdBaixaFinal,
                iniciarProcessoCompras
            });

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const requestData = {
                    id_produto: idProduto,
                    id_produtos_solicitacoes: idProdutoSolic,
                    quantidade_baixa: qtdBaixaFinal,
                    quantidade_original_solicitada: qtdBaixa,
                    data_baixa: dataBaixa,
                    iniciar_processo_compras: iniciarProcessoCompras
                };

                console.log('Enviando requisição:', requestData);

                const response = await fetch(`/admin/saidaprodutosestoque/processarBaixa/${idRequisicao}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(requestData)
                });

                let result = null;
                try {
                    result = await response.json();
                } catch (e) {
                    result = {};
                }

                if (response.ok) {
                    // Verificar se foi COMPRA PARCIAL
                    if (qtdBaixa == qtdSolicitada && qtdBaixa > qtdEstoqueMatriz && qtdEstoqueMatriz > 0) {
                        const qtdBaixaReal = qtdEstoqueMatriz;
                        const qtdParaCompras = qtdBaixa - qtdEstoqueMatriz;
                        alert(`COMPRA PARCIAL realizada com sucesso!\n` +
                            `Baixado do estoque: ${qtdBaixaReal}\n` +
                            `Marcado para compras: ${qtdParaCompras}\n` +
                            `Status: COMPRA PARCIAL`);
                    } else if (iniciarProcessoCompras) {
                        if (qtdBaixaFinal === 0) {
                            alert(`Estoque zerado! Processo de compras iniciado para toda a quantidade (${qtdBaixa}).`);
                        } else {
                            alert(
                                `Baixa parcial realizada (${qtdBaixaFinal}) e processo de compras iniciado para o restante.`
                            );
                        }
                    } else {
                        alert('Baixa gravada com sucesso!');
                    }
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao processar a baixa.');
                }

            } catch (err) {
                console.error(err);
                alert('Erro inesperado ao processar a baixa.');
            }
        }

        // Nova função para processar baixa com compra quando Nova QTD for menor que Quantidade Solicitada
        async function processarBaixaComCompra(idProdutoSolic, idProduto, idRequisicao, qtdBaixa, qtdSolicitada,
            dataBaixa) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const requestData = {
                    id_produto: idProduto,
                    id_produtos_solicitacoes: idProdutoSolic,
                    quantidade_baixa: qtdBaixa,
                    quantidade_original_solicitada: qtdSolicitada,
                    data_baixa: dataBaixa,
                    iniciar_processo_compras: true,
                    tipo_operacao: 'baixa_parcial_com_compra'
                };

                console.log('Enviando requisição para baixa parcial com compra:', requestData);

                const response = await fetch(`/admin/saidaprodutosestoque/processarBaixa/${idRequisicao}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(requestData)
                });

                let result = null;
                try {
                    result = await response.json();
                } catch (e) {
                    result = {};
                }

                if (response.ok) {
                    const qtdParaCompras = qtdSolicitada - qtdBaixa;
                    alert(`Operação realizada com sucesso!\n` +
                        `Baixado do estoque: ${qtdBaixa}\n` +
                        `Marcado para compras: ${qtdParaCompras}\n` +
                        `Status: BAIXA PARCIAL COM COMPRA`);
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao processar a baixa com compra.');
                }

            } catch (err) {
                console.error(err);
                alert('Erro inesperado ao processar a baixa com compra.');
            }
        }
    </script>
@endverbatim
