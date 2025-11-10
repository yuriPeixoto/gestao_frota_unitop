<div class="relative mt-6 min-h-[400px] overflow-x-auto">
    <div id="table-loading" class="absolute inset-0 z-10 flex hidden items-center justify-center bg-white bg-opacity-80">
        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
    </div>

    <div id="results-table" class="opacity-100 transition-opacity duration-300">
        <table class="w-full min-w-[1200px] divide-y divide-gray-200 text-left text-sm text-gray-700">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-center text-xs font-medium uppercase text-gray-500">Ações</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Código Solicitação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Tipo Requisição</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Filial</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Departamento</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Estoquistas</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Usuário Solicitação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Filial Solicitante</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Transferência</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Situação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Data Aprovação</th>
                    <th class="px-3 py-2 text-xs font-medium uppercase text-gray-500">Data Inclusão</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($relacaoTransf as $key => $item)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="relative inline-block">
                                <button onclick="toggleDropdown()"
                                    class="dropdown-button flex items-center space-x-2 rounded border bg-white px-4 py-2 shadow">
                                    <x-icons.gear class="h-4 w-4" />
                                    <span>Ações</span>
                                </button>
                                <ul
                                    class="dropdown-menu absolute left-0 z-50 mt-2 hidden w-48 rounded border bg-white shadow-lg">
                                    <li>
                                        @if ($item->situacao !== 'TRANSFERIDO')
                                            <a href="{{ route('admin.saidaprodutosestoque.editTransferencia', $item->id_solicitacao_pecas) }}"
                                                class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                                <x-icons.cubes class="mr-2 h-4 w-4 text-blue-600" />
                                                Transferir Itens
                                            </a>
                                        @endif
                                    </li>
                                    <li>
                                        <a href="#" onclick="imprimirReqPecas({{ $item->id_solicitacao_pecas }})"
                                            class="block flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                            <x-icons.pdf-doc class="mr-2 h-4 w-4 text-red-600" />
                                            Imprimir
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="visualizarProdutos({{ $item->id_solicitacao_pecas }})"
                                            class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                            <x-icons.dolly-flatbed class="mr-2 h-4 w-4 text-cyan-600" />
                                            Visualizar Produtos
                                        </a>
                                    </li>
                                    <li>
                                        @if (empty($item->id_usuario_estoque))
                                            <a href="#" onclick="onAssumir({{ $item->id_solicitacao_pecas }})"
                                                class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                                <x-icons.user-check class="mr-2 h-4 w-4 text-cyan-600" />
                                                Assumir Requisição
                                            </a>
                                        @endif
                                    </li>
                                    <li>
                                        @if ($item->situacao == 'ESTORNO DE TRANSFERENCIA')
                                            <a href="#"
                                                onclick="abrirModalCancelarTransferencia({{ $item->id_solicitacao_pecas }})"
                                                class="block flex items-center px-4 py-2 hover:bg-gray-100">
                                                <x-icons.disable class="mr-2 h-4 w-4 text-red-600" />
                                                Cancelar Transferencia
                                            </a>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->id_solicitacao_pecas }}
                        </td>
                        <td class="wrap px-6 py-4 text-sm text-gray-900">
                            {{ $item->requisicao_ti ? 'Requisição TI' : 'Requisição Material' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->produtosSolicitacoes->first()?->filialTransferencia->name ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->descricao_departamento ?? $item->departamentoPecas->descricao_departamento }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->pessoalAbertura->name ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->produtosSolicitacoes->first()?->user->name ?? '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->filial->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->id_transferencia ?? ' ' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->situacao }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->data_aprovacao ? $item->data_aprovacao->format('d/m/Y') : '' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ date('d/m/Y', strtotime($item->data_inclusao)) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="px-6 py-4 text-center text-sm text-gray-500">
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $relacaoTransf->links() }}
        </div>
    </div>
</div>

<!-- Modal de Cancelar Transferência -->
<div id="modal-cancelar-transferencia"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm transition-all duration-300">
    <div class="mx-4 w-full max-w-md scale-95 transform rounded-lg bg-white p-6 shadow-2xl transition-all duration-300"
        id="modalContentCancelamento">
        <div class="mb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
                Cancelar Transferência
            </h3>
            <p class="mt-2 text-sm text-gray-600">
                Tem certeza que deseja cancelar esta transferência? Esta ação irá estornar todos os itens transferidos.
            </p>
        </div>

        <div class="mb-6">
            <label for="justificativa-cancelamento" class="mb-2 block text-sm font-medium text-gray-700">
                <span class="flex items-center">
                    <svg class="mr-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Justificativa do Cancelamento
                    <span class="ml-1 text-red-500">*</span>
                </span>
            </label>
            <textarea id="justificativa-cancelamento" name="justificativa" rows="4"
                placeholder="Digite o motivo detalhado do cancelamento..."
                class="mt-1 w-full resize-none rounded-md border-gray-300 shadow-sm transition-colors duration-200 focus:border-red-500 focus:ring-red-500 sm:text-sm"
                maxlength="500" required
                style="pointer-events: auto; user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text;"></textarea>
            <div class="mt-1 flex justify-between">
                <div id="erro-justificativa" class="flex hidden items-center text-sm text-red-600">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    A justificativa é obrigatória (mínimo 5 caracteres).
                </div>
                <div class="text-xs text-gray-500">
                    <span id="caracteresCountCancelamento">0</span>/500 caracteres
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
            <button type="button" onclick="fecharModalCancelamento()"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                Cancelar
            </button>
            <button type="button" onclick="confirmarCancelamento()"
                class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
                Confirmar Cancelamento
            </button>
        </div>
    </div>
</div>

<script>
    // Versão atualizada - Debug XMLHttpRequest v1.1
    let idTransferenciaParaCancelar = null;

    function abrirModalCancelarTransferencia(idSolicitacao) {
        idTransferenciaParaCancelar = idSolicitacao;

        // Limpar campos
        const textarea = document.getElementById('justificativa-cancelamento');
        textarea.value = '';
        textarea.disabled = false;
        textarea.readOnly = false;

        document.getElementById('caracteresCountCancelamento').textContent = '0';
        document.getElementById('erro-justificativa').classList.add('hidden');

        const modal = document.getElementById('modal-cancelar-transferencia');
        const modalContent = document.getElementById('modalContentCancelamento');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Animação de entrada
        setTimeout(() => {
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);

        // Foco no campo de justificativa com delay maior
        setTimeout(() => {
            textarea.focus();
            textarea.click();
        }, 300);
    }

    function fecharModalCancelamento() {
        const modal = document.getElementById('modal-cancelar-transferencia');
        const modalContent = document.getElementById('modalContentCancelamento');

        // Animação de saída
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            idTransferenciaParaCancelar = null;
        }, 300);
    }

    function confirmarCancelamento() {
        const justificativa = document.getElementById('justificativa-cancelamento').value.trim();
        const erroDiv = document.getElementById('erro-justificativa');

        // Validar justificativa
        if (!justificativa) {
            erroDiv.classList.remove('hidden');
            return;
        }

        if (justificativa.length < 5) {
            erroDiv.classList.remove('hidden');
            return;
        }

        if (justificativa.length > 500) {
            erroDiv.classList.remove('hidden');
            return;
        }

        // Esconder erro se validação passou
        erroDiv.classList.add('hidden');

        // Chamar função de cancelamento com justificativa
        cancelarTransferenciaNovo(idTransferenciaParaCancelar, justificativa);
    }

    // Contador de caracteres e validação em tempo real para o modal de cancelamento
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('justificativa-cancelamento');
        const contador = document.getElementById('caracteresCountCancelamento');
        const erroDiv = document.getElementById('erro-justificativa');

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
                const modal = document.getElementById('modal-cancelar-transferencia');
                if (!modal.classList.contains('hidden')) {
                    fecharModalCancelamento();
                }
            }
        });

        // Fechar modal clicando no fundo
        document.getElementById('modal-cancelar-transferencia').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalCancelamento();
            }
        });

        // Impedir que clicks no conteúdo do modal fechem o modal
        document.getElementById('modalContentCancelamento').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Garantir que o textarea seja focusável e editável
        const textareaCancelamento = document.getElementById('justificativa-cancelamento');
        if (textareaCancelamento) {
            textareaCancelamento.addEventListener('click', function(e) {
                e.stopPropagation();
                this.focus();
            });

            textareaCancelamento.addEventListener('focus', function() {
                this.style.outline = 'none';
                this.style.borderColor = '#ef4444';
                this.style.boxShadow = '0 0 0 1px #ef4444';
            });
        }
    });

    function cancelarTransferenciaNovo(idSolicitacao, justificativa) {

        if (!justificativa) {
            alert('Justificativa é obrigatória');
            return;
        }

        // Mostrar loading
        const btnConfirmar = document.querySelector(
            '#modal-cancelar-transferencia button[onclick="confirmarCancelamento()"]');
        const textoOriginal = btnConfirmar.textContent;
        btnConfirmar.textContent = 'Processando...';
        btnConfirmar.disabled = true;

        // Usar XMLHttpRequest - VERSÃO NOVA
        const xhr = new XMLHttpRequest();
        const url = `{{ url('admin/saidaprodutosestoque/cancelarTransferencia') }}/${idSolicitacao}`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {

                // Restaurar botão
                btnConfirmar.textContent = textoOriginal;
                btnConfirmar.disabled = false;

                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.status === 'success') {
                            // Fechar modal
                            fecharModalCancelamento();
                            // Mostrar mensagem de sucesso
                            alert(data.message);
                            // Recarregar a página
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao cancelar transferência');
                        }
                    } catch (e) {
                        console.error('Erro ao parsear JSON:', e);
                        alert('Erro ao processar resposta do servidor');
                    }
                } else {
                    try {
                        const errorData = JSON.parse(xhr.responseText);

                        if (xhr.status === 422) {
                            if (errorData.errors) {
                                const validationErrors = Object.values(errorData.errors).flat().join(', ');
                                alert(`Erro de validação: ${validationErrors}`);
                            } else if (errorData.message) {
                                alert(`Erro de validação: ${errorData.message}`);
                            } else {
                                alert('Erro de validação. Verifique os dados informados.');
                            }
                        } else {
                            alert(errorData.message || `Erro HTTP: ${xhr.status}`);
                        }
                    } catch (e) {
                        console.error('Erro ao parsear resposta de erro:', e);
                        alert(`Erro HTTP: ${xhr.status}. Response: ${xhr.responseText}`);
                    }
                }
            }
        };

        const params = `justificativa=${encodeURIComponent(justificativa)}&_token=${encodeURIComponent(csrfToken)}`;
        xhr.send(params);
    }
</script>
