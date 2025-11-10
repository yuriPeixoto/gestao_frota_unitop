<!-- Tabela de Cotações -->
<div class="overflow-hidden rounded-lg bg-white shadow-md">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Ordem Serviço</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Comprador</x-tables.head-cell>
            <x-tables.head-cell>Tipo Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($cotacoes as $index => $cotacao)
                <x-tables.row :index="$index" :class="$cotacao->is_adiado ? 'bg-orange-50 border-l-4 border-orange-400' : ''">
                    <x-tables.cell class="font-medium text-gray-900">
                        <div class="flex items-center">
                            {{ $cotacao->id_solicitacoes_compras }}
                            @if ($cotacao->is_adiado)
                                <span
                                    class="ml-2 inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Adiado
                                </span>
                            @endif
                            @if ($cotacao->solicitacoesCompra->is_unificado)
                                <span
                                    class="ml-2 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">
                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                    </svg>
                                    Unificado
                                </span>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->id_ordem_servico ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->id_veiculo ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        <span
                            class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                            {{ $cotacao->situacao_compra }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->data_inclusao ? $cotacao->data_inclusao->format('d/m/Y H:i') : 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->descricao_departamento ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->solicitante ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        {{ $cotacao->comprador ?? 'N/A' }}
                    </x-tables.cell>
                    <x-tables.cell>
                        @if ($cotacao->solicitacoesCompra->tipo_solicitacao == 1)
                            Solicitação de Produto
                        @elseif ($cotacao->solicitacoesCompra->tipo_solicitacao == 2)
                            Solicitação de Serviço
                        @endif
                    </x-tables.cell>

                    <x-tables.cell>
                        <div class="flex justify-end space-x-2">
                            <!-- Visualizar -->
                            <a href="{{ route('admin.compras.cotacoes.show', $cotacao->id_solicitacoes_compras) }}"
                                title="Visualizar"
                                class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <x-icons.eye class="h-3 w-3" />
                            </a>

                            <!-- Editar -->
                            @if (
                                $cotacao->situacao_compra !== 'AGUARDANDO INÍCIO DE COMPRAS' &&
                                    $cotacao->situacao_compra !== 'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO' &&
                                    $cotacao->situacao_compra !== 'AGUARDANDO VALIDAÇÃO DO SOLICITANTE')
                                <a href="{{ route('admin.compras.cotacoes.edit', $cotacao->id_solicitacoes_compras) }}"
                                    title="Editar"
                                    class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            @endif

                            <!-- Assumir -->
                            @if ($cotacao->situacao_compra == 'AGUARDANDO INÍCIO DE COMPRAS')
                                <button
                                    onclick="if(typeof assumirSolicitacao === 'function') { assumirSolicitacao({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função assumirSolicitacao não encontrada!'); }"
                                    title="Assumir Solicitação"
                                    class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <x-icons.check class="h-3 w-3" />
                                </button>
                            @endif

                            <!-- Trocar de comprador -->
                            @if (auth()->user()->is_superuser && $cotacao->comprador != null)
                                <button
                                    onclick="if(typeof trocarComprador === 'function') { trocarComprador({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função trocarComprador não encontrada!'); }"
                                    title="Trocar de comprador"
                                    class="inline-flex items-center rounded-full border border-transparent bg-yellow-600 p-1 text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    <x-icons.refresh class="h-3 w-3" />
                                </button>
                            @endif

                            <!-- Voltar -->
                            @if ($cotacao->situacao_compra == 'AGUARDANDO INÍCIO DE COMPRAS')
                                <button
                                    onclick="if(typeof devolverSolicitacao === 'function') { devolverSolicitacao({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função devolverSolicitacao não encontrada!'); }"
                                    title="Voltar a Solicitação"
                                    class="inline-flex items-center rounded-full border border-transparent bg-gray-600 p-1 text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <x-icons.arrow-back class="h-3 w-3" />
                                </button>
                            @endif

                            <!-- Adiar/Remover Adiamento -->
                            @if (
                                $cotacao->situacao_compra !== 'AGUARDANDO INÍCIO DE COMPRAS' &&
                                    $cotacao->situacao_compra !== 'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO' &&
                                    $cotacao->situacao_compra !== 'AGUARDANDO VALIDAÇÃO DO SOLICITANTE')
                                @if ($cotacao->is_adiado)
                                    <button
                                        onclick="if(typeof removerAdiamento === 'function') { removerAdiamento({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função removerAdiamento não encontrada!'); }"
                                        title="Remover Adiamento"
                                        class="inline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @else
                                    <button
                                        onclick="if(typeof adiarSolicitacao === 'function') { adiarSolicitacao({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função adiarSolicitacao não encontrada!'); }"
                                        title="Adiar Solicitação"
                                        class="inline-flex items-center rounded-full border border-transparent bg-orange-600 p-1 text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                            @endif

                            <!-- Desmembrar Cotação Unificada -->
                            @if ($cotacao->solicitacoesCompra->is_unificado)
                                @can('juntar', App\Models\SolicitacaoCompra::class)
                                    <button
                                        onclick="if(typeof desmembrarCotacao === 'function') { desmembrarCotacao({{ $cotacao->id_solicitacoes_compras }}); } else { alert('Função desmembrarCotacao não encontrada!'); }"
                                        title="Desmembrar Cotação Unificada"
                                        class="inline-flex items-center rounded-full border border-transparent bg-purple-600 p-1 text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                        </svg>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="10" message="Nenhuma cotação encontrada" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <!-- Paginação -->
    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        {{ $cotacoes->links() }}
    </div>

    <!-- Botões de ação em massa e exportação -->
    <div class="flex justify-between border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6">
        <div>
            <!-- Botão para selecionar itens quando implementar ações em massa -->
        </div>
        <div class="flex space-x-2">
            <!-- Adicione botões de exportação se necessário -->
        </div>
    </div>
</div>

<!-- Modal para trocar comprador -->
<div id="modalTrocarComprador" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block transform overflow-hidden rounded-xl border border-gray-200 bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:align-middle">
            <form id="formTrocarComprador" method="POST">
                @csrf
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="text-center">

                        <h3 class="mb-6 text-xl font-semibold leading-6 text-gray-900" id="modal-title">
                            Trocar Comprador
                        </h3>

                        <div class="space-y-6">
                            <div>
                                <label class="mb-3 block text-center text-sm font-medium text-gray-700">
                                    Comprador Atual
                                </label>
                                <div id="compradorAtual"
                                    class="mx-auto max-w-md rounded-lg border bg-gray-50 p-3 text-sm font-medium text-gray-700">
                                    Carregando...
                                </div>
                            </div>

                            <div id="smartSelectContainer" class="mx-auto max-w-md">
                                <x-forms.smart-select name="id_comprador" label="Novo Comprador"
                                    placeholder="Selecione um novo comprador..." :options="[]" asyncSearch="false"
                                    required="true" valueField="id" textField="name" includeEmptyOption="true"
                                    emptyOptionText="Selecione um comprador..." />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 text-center">
                    <div class="flex justify-center space-x-3">
                        <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-6 py-2 text-base font-medium text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Trocar Comprador
                        </button>
                        <button type="button" onclick="fecharModalTrocarComprador()"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-6 py-2 text-base font-medium text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para adiar solicitação -->
<div id="modalAdiarSolicitacao" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title-adiar"
    role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block transform overflow-hidden rounded-xl border border-gray-200 bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <form id="formAdiarSolicitacao" method="POST">
                @csrf
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <h3 class="mb-6 mt-4 text-xl font-semibold leading-6 text-gray-900" id="modal-title-adiar">
                            Adiar Solicitação
                        </h3>

                        <div class="space-y-6">
                            <div class="text-left">
                                <label for="data_adiado" class="block text-sm font-medium text-gray-700">
                                    Data de Adiamento <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="data_adiado" name="data_adiado" required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div class="text-left">
                                <label for="justificativa_adiado" class="block text-sm font-medium text-gray-700">
                                    Justificativa <span class="text-red-500">*</span>
                                </label>
                                <textarea id="justificativa_adiado" name="justificativa_adiado" rows="4" required
                                    placeholder="Descreva o motivo do adiamento..."
                                    class="mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"></textarea>
                            </div>

                            <!-- Campo oculto para id_user_adiado será preenchido via JavaScript -->
                            <input type="hidden" id="id_user_adiado" name="id_user_adiado"
                                value="{{ auth()->id() }}">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 text-center">
                    <div class="flex justify-center space-x-3">
                        <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-orange-600 px-6 py-2 text-base font-medium text-white shadow-sm transition-colors duration-200 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Adiar Solicitação
                        </button>
                        <button type="button" onclick="fecharModalAdiarSolicitacao()"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-6 py-2 text-base font-medium text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        /**
         * Função para assumir uma solicitação de compra
         * @param {number} idSolicitacao - ID da solicitação de compra
         */
        function assumirSolicitacao(idSolicitacao) {
            // Validar parâmetro
            if (!idSolicitacao) {
                alert('ID da solicitação é obrigatório');
                return;
            }

            // Confirmar ação com o usuário
            if (!confirm("Deseja assumir esta solicitação de compra?")) {
                return;
            }

            // Mostrar loading usando SweetAlert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Assumindo solicitação, aguarde.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            if (!csrfToken) {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                alert('Token CSRF não encontrado. Recarregue a página.');
                return;
            }

            // Fazer requisição para assumir a solicitação
            fetch("/admin/compras/cotacoes/assumir", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        id: idSolicitacao,
                    }),
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Sucesso - mostrar mensagem usando Utils.showNotification
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Sucesso', data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Sucesso',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Sucesso'}: ${data.message}`);
                        }

                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao assumir solicitação:", error);

                    const errorMessage = "Erro ao assumir solicitação. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        }

        // Expor função no escopo global
        window.assumirSolicitacao = assumirSolicitacao;

        // Também declarar globalmente (sem window)
        if (typeof globalThis !== 'undefined') {
            globalThis.assumirSolicitacao = assumirSolicitacao;
        }


        function devolverSolicitacao(idSolicitacao) {
            // Validar parâmetro
            if (!idSolicitacao) {
                alert('ID da solicitação é obrigatório');
                return;
            }

            // Confirmar ação com o usuário
            if (!confirm("Deseja devolver esta solicitação de compra?")) {
                return;
            }

            // Enviar apenas o idSolicitacao para o backend
            const id_solicitacoes_compras = idSolicitacao;

            // Mostrar loading usando SweetAlert
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Devolvendo solicitação, aguarde.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            if (!csrfToken) {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                alert('Token CSRF não encontrado. Recarregue a página.');
                return;
            }

            // Fazer requisição para devolver a solicitação
            fetch("/admin/compras/cotacoes/ondevolver", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        id: id_solicitacoes_compras,
                    }),
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Sucesso - mostrar mensagem
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Sucesso', data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Sucesso',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Sucesso'}: ${data.message}`);
                        }

                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao devolver solicitação:", error);

                    const errorMessage = "Erro ao devolver solicitação. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        }

        // Expor função no escopo global
        window.devolverSolicitacao = devolverSolicitacao;

        // Também declarar globalmente (sem window)
        if (typeof globalThis !== 'undefined') {
            globalThis.devolverSolicitacao = devolverSolicitacao;
        }

        function trocarComprador(idSolicitacao) {
            // Validar parâmetro
            if (!idSolicitacao) {
                alert('ID da solicitação é obrigatório');
                return;
            }

            // Buscar dados da solicitação
            buscarDadosSolicitacao(idSolicitacao);
        }

        /**
         * Busca dados da solicitação e abre o modal
         */
        function buscarDadosSolicitacao(idSolicitacao) {
            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Carregando...',
                    text: 'Buscando dados da solicitação...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
            if (!csrfToken) {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                alert('Token CSRF não encontrado. Recarregue a página.');
                return;
            }

            // Fazer requisição para buscar dados
            fetch(`/admin/compras/cotacoes/getSolicitacao/${idSolicitacao}`, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    }
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Preencher modal com os dados
                        preencherModalTrocarComprador(data.solicitacao, data.usuarios, idSolicitacao);
                        // Abrir modal
                        abrirModalTrocarComprador();
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao buscar dados da solicitação:", error);

                    const errorMessage = "Erro ao buscar dados da solicitação. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        }

        /**
         * Preenche o modal com os dados da solicitação
         */
        function preencherModalTrocarComprador(solicitacao, usuarios, idSolicitacao) {
            // Preencher comprador atual
            const compradorAtualDiv = document.getElementById('compradorAtual');
            compradorAtualDiv.textContent =
                `Comprador atual: ${solicitacao.comprador || 'Nenhum comprador definido'}`;

            // Preparar dados para o SmartSelect
            const usuariosFormatados = usuarios.map(usuario => ({
                id: usuario.id,
                name: `${usuario.name} (${usuario.email})`
            }));

            // Aguardar um pouco para o modal ser renderizado completamente
            setTimeout(() => {
                // Buscar o componente SmartSelect dentro do modal
                const modalContainer = document.getElementById('modalTrocarComprador');
                const smartSelectContainer = modalContainer.querySelector('[x-data*="asyncSearchableSelect"]');

                if (smartSelectContainer && smartSelectContainer._x_dataStack && smartSelectContainer._x_dataStack[
                        0]) {
                    const alpineData = smartSelectContainer._x_dataStack[0];

                    // Atualizar as opções do SmartSelect
                    alpineData.options = usuariosFormatados;
                    alpineData.selectedValues = [];
                    alpineData.selectedLabels = [];
                    alpineData.selectedObjects = [];

                    // Forçar atualização do Alpine.js
                    if (window.Alpine) {
                        alpineData.$nextTick(() => {
                            console.log('SmartSelect atualizado com sucesso');
                        });
                    }
                } else {
                    console.error('SmartSelect não encontrado no modal');
                }
            }, 100);

            // Configurar form action
            const form = document.getElementById('formTrocarComprador');
            form.action = `/admin/compras/cotacoes/trocarComprador/${idSolicitacao}`;
        }

        /**
         * Abre o modal de trocar comprador
         */
        function abrirModalTrocarComprador() {
            const modal = document.getElementById('modalTrocarComprador');
            modal.classList.remove('hidden');

            // Debug: verificar se o SmartSelect foi inicializado
            setTimeout(() => {
                const smartSelectContainer = modal.querySelector('[x-data*="asyncSearchableSelect"]');
            }, 100);
        }

        /**
         * Fecha o modal de trocar comprador
         */
        function fecharModalTrocarComprador() {
            const modal = document.getElementById('modalTrocarComprador');
            modal.classList.add('hidden');

            // Limpar form
            const form = document.getElementById('formTrocarComprador');
            form.reset();

            // Limpar SmartSelect
            setTimeout(() => {
                const smartSelectContainer = modal.querySelector('[x-data*="asyncSearchableSelect"]');
                if (smartSelectContainer && smartSelectContainer._x_dataStack && smartSelectContainer._x_dataStack[
                        0]) {
                    const alpineData = smartSelectContainer._x_dataStack[0];
                    alpineData.selectedValues = [];
                    alpineData.selectedLabels = [];
                    alpineData.selectedObjects = [];
                    alpineData.selectedObjectsJson = '';
                    alpineData.options = [];
                }
            }, 50);
        }

        /**
         * Processa o envio do formulário de trocar comprador
         */
        document.getElementById('formTrocarComprador').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const novoCompradorId = formData.get('id_comprador');

            if (!novoCompradorId) {
                alert('Selecione um novo comprador');
                return;
            }

            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Trocando comprador, aguarde.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Fazer requisição
            fetch(this.action, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        id_comprador: novoCompradorId,
                    }),
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Fechar modal
                        fecharModalTrocarComprador();

                        // Sucesso - mostrar mensagem
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Sucesso', data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Sucesso',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Sucesso'}: ${data.message}`);
                        }

                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao trocar comprador:", error);

                    const errorMessage = "Erro ao trocar comprador. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        });

        // Expor função no escopo global
        window.trocarComprador = trocarComprador;

        // Também declarar globalmente (sem window)
        if (typeof globalThis !== 'undefined') {
            globalThis.trocarComprador = trocarComprador;
        }

        /**
         * Função para adiar uma solicitação de compra
         * @param {number} idSolicitacao - ID da solicitação de compra
         */
        function adiarSolicitacao(idSolicitacao) {
            // Validar parâmetro
            if (!idSolicitacao) {
                alert('ID da solicitação é obrigatório');
                return;
            }

            // Configurar form action
            const form = document.getElementById('formAdiarSolicitacao');
            form.action = `/admin/compras/cotacoes/adiar/${idSolicitacao}`;

            // Definir data atual como padrão
            const dataAdiado = document.getElementById('data_adiado');
            const hoje = new Date().toISOString().split('T')[0];
            dataAdiado.value = hoje;

            // Limpar justificativa
            document.getElementById('justificativa_adiado').value = '';

            // Abrir modal
            abrirModalAdiarSolicitacao();
        }

        /**
         * Abre o modal de adiar solicitação
         */
        function abrirModalAdiarSolicitacao() {
            const modal = document.getElementById('modalAdiarSolicitacao');
            modal.classList.remove('hidden');

            // Focar no campo de justificativa
            setTimeout(() => {
                document.getElementById('justificativa_adiado').focus();
            }, 100);
        }

        /**
         * Fecha o modal de adiar solicitação
         */
        function fecharModalAdiarSolicitacao() {
            const modal = document.getElementById('modalAdiarSolicitacao');
            modal.classList.add('hidden');

            // Limpar form
            const form = document.getElementById('formAdiarSolicitacao');
            form.reset();

            // Redefinir data atual
            const dataAdiado = document.getElementById('data_adiado');
            const hoje = new Date().toISOString().split('T')[0];
            dataAdiado.value = hoje;
        }

        /**
         * Processa o envio do formulário de adiar solicitação
         */
        document.getElementById('formAdiarSolicitacao').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const dataAdiado = formData.get('data_adiado');
            const justificativa = formData.get('justificativa_adiado');

            // Validações
            if (!dataAdiado) {
                alert('Data de adiamento é obrigatória');
                return;
            }

            if (!justificativa || justificativa.trim().length < 3) {
                alert('Justificativa é obrigatória e deve ter pelo menos 10 caracteres');
                return;
            }

            // Confirmar ação
            if (!confirm('Deseja realmente adiar esta solicitação?')) {
                return;
            }

            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Adiando solicitação, aguarde.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            // Fazer requisição
            fetch(this.action, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({
                        data_adiado: dataAdiado,
                        justificativa_adiado: justificativa,
                        id_user_adiado: formData.get('id_user_adiado'),
                    }),
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Fechar modal
                        fecharModalAdiarSolicitacao();

                        // Sucesso - mostrar mensagem
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Sucesso', data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Sucesso',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Sucesso'}: ${data.message}`);
                        }

                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao adiar solicitação:", error);

                    const errorMessage = "Erro ao adiar solicitação. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        });

        // Expor função no escopo global
        window.adiarSolicitacao = adiarSolicitacao;

        // Também declarar globalmente (sem window)
        if (typeof globalThis !== 'undefined') {
            globalThis.adiarSolicitacao = adiarSolicitacao;
        }

        /**
         * Função para remover adiamento de uma solicitação de compra
         * @param {number} idSolicitacao - ID da solicitação de compra
         */
        function removerAdiamento(idSolicitacao) {
            // Validar parâmetro
            if (!idSolicitacao) {
                alert('ID da solicitação é obrigatório');
                return;
            }

            // Confirmar ação
            if (!confirm('Deseja realmente remover o adiamento desta solicitação?')) {
                return;
            }

            // Mostrar loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processando...',
                    text: 'Removendo adiamento, aguarde.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });
            }

            // Obter CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

            if (!csrfToken) {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                alert('Token CSRF não encontrado. Recarregue a página.');
                return;
            }

            // Fazer requisição
            fetch(`/admin/compras/cotacoes/remover-adiamento/${idSolicitacao}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: JSON.stringify({})
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    if (data.success) {
                        // Sucesso - mostrar mensagem
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Sucesso', data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Sucesso',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Sucesso'}: ${data.message}`);
                        }

                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Erro - mostrar mensagem de erro
                        if (typeof Utils !== 'undefined' && Utils.showNotification) {
                            Utils.showNotification(data.title || 'Erro', data.message, 'error');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: data.title || 'Erro',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(`${data.title || 'Erro'}: ${data.message}`);
                        }
                    }
                })
                .catch((error) => {
                    // Fechar loading
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    console.error("Erro ao remover adiamento:", error);

                    const errorMessage = "Erro ao remover adiamento. Tente novamente.";
                    if (typeof Utils !== 'undefined' && Utils.showNotification) {
                        Utils.showNotification("Erro", errorMessage, 'error');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Erro',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(`Erro: ${errorMessage}`);
                    }
                });
        }

        // Expor função no escopo global
        window.removerAdiamento = removerAdiamento;

        // Também declarar globalmente (sem window)
        if (typeof globalThis !== 'undefined') {
            globalThis.removerAdiamento = removerAdiamento;
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('modalTrocarComprador').addEventListener('click', function(event) {
            if (event.target === this) {
                fecharModalTrocarComprador();
            }
        });

        // Fechar modal de adiar ao clicar fora dele
        document.getElementById('modalAdiarSolicitacao').addEventListener('click', function(event) {
            if (event.target === this) {
                fecharModalAdiarSolicitacao();
            }
        });

        // Fechar modal com tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modalTrocar = document.getElementById('modalTrocarComprador');
                const modalAdiar = document.getElementById('modalAdiarSolicitacao');

                if (!modalTrocar.classList.contains('hidden')) {
                    fecharModalTrocarComprador();
                } else if (!modalAdiar.classList.contains('hidden')) {
                    fecharModalAdiarSolicitacao();
                }
            }
        });
    </script>
@endpush
