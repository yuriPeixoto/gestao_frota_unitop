    <!-- CSS para o toggle customizado -->
    <style>
        .toggle-bg {
            background-color: #e5e7eb;
            /* gray-200 */
            border: 2px solid #e5e7eb;
            height: 1.5rem;
            /* 24px */
            width: 2.75rem;
            /* 44px */
            border-radius: 9999px;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
            cursor: pointer;
            position: relative;
        }

        .toggle-bg.checked {
            background-color: #10B981;
            /* green-500 */
            border-color: #10B981;
        }

        .toggle-dot {
            position: absolute;
            left: 0;
            top: 0;
            background-color: white;
            width: 1.5rem;
            /* 24px */
            height: 1.5rem;
            /* 24px */
            border-radius: 9999px;
            transition: transform 0.2s ease-in-out;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .toggle-dot.checked {
            transform: translateX(1.25rem);
            /* 20px */
        }

        .toggle-container {
            user-select: none;
        }
    </style>
    <div class="w-full space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- CRUD - Cadastro --}}
        <div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                {{-- Código da Transferência (apenas visualização) --}}
                <x-forms.input name="id_transferencia_display" label="Cód. Transferência"
                    value="{{ $transferencia->id_tranferencia ?? '' }}" readonly />

                {{-- Campo hidden para enviar o id_transferencia --}}
                <input type="hidden" name="id_tranferencia" value="{{ $transferencia->id_tranferencia ?? '' }}">

                {{-- Data Inclusao --}}
                <x-forms.input name="data_inclusao" label="Data Inclusão"
                    value="{{ old('data_inclusao', $transferencia->data_inclusao ?? '') }}" readonly />

                {{-- Filial --}}
                <x-forms.input name="filial" label="Filial" value="{{ auth()->user()->filial->name ?? '' }}"
                    disabled />

                {{-- Usuário --}}
                <x-forms.input name="usuario" label="Usuário" value="{{ auth()->user()->name }}" disabled />

                {{-- Departamento --}}
                <x-forms.input name="id_departamento" label="Departamento"
                    value="{{ auth()->user()->departamento->descricao_departamento ?? '' }}" readonly />
                {{-- Usuário Solicitante --}}
                <x-forms.input name="" label="Usuário Solicitante" value="{{ $transferencia->name ?? '' }}"
                    disabled />

                {{-- Filial Envio --}}
                <x-forms.input name="" label="Filial Envio" value="{{ $transferencia->filialBaixa->name ?? '' }}"
                    disabled />

                {{-- Observação 1 --}}
                <x-forms.input name="" label="Observação" value="" disabled />

                {{-- Observação 2 --}}
                <x-forms.input name="" label="Observação Aprovação" value="" disabled />

                {{-- Observação 3 --}}
                <x-forms.input name="" label="Observação Solicitação" value="" disabled />

                {{-- Situação --}}
                <x-forms.input name="situacao" label="Situação" value="{{ $transferencia->situacao ?? 'AGUARDANDO' }}"
                    readonly />

            </div>

            {{-- Observação de Inconsistência --}}
            @if (!empty($transferencia->observacao_inconsistencia))
                <div class="mt-4 rounded-md border border-yellow-200 bg-yellow-50 p-4">
                    <h4 class="mb-2 text-sm font-medium text-yellow-800">Observação de Inconsistência:</h4>
                    <p class="text-sm text-yellow-700">{{ $transferencia->observacao_inconsistencia }}</p>
                </div>
            @endif

        </div>
        <!-- Toggle customizado para Recebido -->
        <div class="flex w-4/12 items-center gap-2">
            <div class="toggle-container flex items-center">
                <input type="checkbox" name="recebido" value="1" id="checkbox-recebido"
                    {{ old('recebido', $transferencia->recebido ?? 0) == 1 ? 'checked' : '' }} class="sr-only">

                <!-- Toggle Switch Customizado -->
                <div class="relative">
                    <div class="toggle-bg" onclick="toggleSwitch()"></div>
                    <div class="toggle-dot" onclick="toggleSwitch()"></div>
                </div>

                <span class="ml-3 text-sm font-medium text-gray-700">Recebido</span>
            </div>
            <!-- Campo hidden para garantir que sempre seja enviado -->
            <input type="hidden" name="recebido_hidden" value="0">
        </div>

        <h2 class="text-2xl font-bold text-black">Produtos</h2>

        <div>
            <x-tables.table>
                <x-tables.header>
                    <x-tables.head-cell>Produto</x-tables.head-cell>
                    <x-tables.head-cell>Descrição</x-tables.head-cell>
                    <x-tables.head-cell>Quantidade</x-tables.head-cell>
                    <x-tables.head-cell>Quantidade Recebida</x-tables.head-cell>
                    <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                    <x-tables.head-cell>Data Alteração</x-tables.head-cell>
                </x-tables.header>

                <x-tables.body id="tbody-produtos">
                    @forelse ($transferenciaItens as $result)
                        <x-tables.row>
                            {{-- Produto --}}
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-700">
                                {{ $result->id_produto }}
                            </td>

                            <x-tables.cell>
                                {{ $result->produto->descricao_produto }}
                            </x-tables.cell>

                            <x-tables.cell>
                                {{ $result->quantidade }}
                            </x-tables.cell>

                            <x-tables.cell>
                                <input type="number" name="quantidade_baixa[{{ $result->id_transferencia_itens }}]"
                                    value="{{ $result->quantidade_baixa }}" max="{{ $result->quantidade }}"
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm">
                            </x-tables.cell>

                            <x-tables.cell>
                                {{ format_date($result->data_inclusao) }}
                            </x-tables.cell>

                            <x-tables.cell>
                                {{ format_date($result->data_alteracao) }}
                            </x-tables.cell>
                        </x-tables.row>
                    @endforeach
                </x-tables.body>
            </x-tables.table>

        </div>

        <!-- Modal de Justificação de Inconsistência -->
        <div id="modal-inconsistencia"
            class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
            <div class="relative top-20 mx-auto w-96 rounded-md border bg-white p-5 shadow-lg">
                <div class="mt-3 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">Inconsistência Detectada</h3>
                    <p class="mb-4 text-sm text-gray-500">
                        A quantidade recebida é menor que a quantidade solicitada. Por favor, forneça uma justificativa
                        para esta inconsistência:
                    </p>
                    <textarea id="justificativa-inconsistencia" name="justificativa-inconsistencia" rows="4"
                        class="w-full rounded-lg border px-3 py-2 text-gray-700 focus:border-blue-500 focus:outline-none"
                        placeholder="Digite a justificativa para a inconsistência..."></textarea>
                    <div id="error-justificativa" class="mt-2 hidden text-sm text-red-500">
                        A justificativa é obrigatória para salvar.
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <div class="flex justify-end space-x-3">
                        <button id="btn-cancelar-modal"
                            class="rounded-md bg-gray-300 px-4 py-2 text-base font-medium text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button id="btn-confirmar-modal"
                            class="rounded-md bg-yellow-600 px-4 py-2 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let isToggling = false; // Previne cliques duplos

            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('submit-form');
                const label = document.getElementById('submit-label');
                const modal = document.getElementById('modal-inconsistencia');
                const justificativaTextarea = document.getElementById('justificativa-inconsistencia');
                const btnCancelarModal = document.getElementById('btn-cancelar-modal');
                const btnConfirmarModal = document.getElementById('btn-confirmar-modal');
                const errorJustificativa = document.getElementById('error-justificativa');
                const form = document.getElementById('form');
                const checkbox = document.getElementById('checkbox-recebido');
                const toggleBg = document.querySelector('.toggle-bg');
                const toggleDot = document.querySelector('.toggle-dot');

                let formSubmissionPending = false;

                // Inicializa o toggle
                if (checkbox && toggleBg && toggleDot) {
                    console.log('Toggle encontrado. Estado inicial:', checkbox.checked);
                    updateToggleAppearance(checkbox.checked);
                    checkbox.addEventListener('change', handleCheckboxChange);
                }

                function handleCheckboxChange() {
                    if (!isToggling) {
                        console.log('Checkbox alterado para:', this.checked);
                        updateToggleAppearance(this.checked);
                    }
                }

                function updateToggleAppearance(isChecked) {
                    if (isChecked) {
                        toggleBg.classList.add('checked');
                        toggleDot.classList.add('checked');
                    } else {
                        toggleBg.classList.remove('checked');
                        toggleDot.classList.remove('checked');
                    }
                }

                // Função global para toggle via clique
                window.toggleSwitch = function() {
                    if (isToggling) return;
                    isToggling = true;

                    const newState = !checkbox.checked;
                    checkbox.checked = newState;
                    console.log('Toggle clicado. Novo estado:', newState);
                    updateToggleAppearance(newState);

                    setTimeout(() => {
                        isToggling = false;
                    }, 300);
                };

                function atualizarBotaoFinalizar() {
                    let deveFinalizar = true;

                    document.querySelectorAll('input[name^="quantidade_baixa"]').forEach(input => {
                        const row = input.closest('tr');
                        const quantidadeSolicitada = parseFloat(row.children[2].textContent.trim()) || 0;
                        const quantidadeRecebida = parseFloat(input.value) || 0;

                        if (quantidadeRecebida !== quantidadeSolicitada) {
                            deveFinalizar = false;
                        }
                    });

                    if (deveFinalizar) {
                        btn.value = 'finalizar';
                        label.textContent = 'Finalizar';
                    } else {
                        btn.value = 'salvar';
                        label.textContent = 'Salvar';
                    }
                }

                function verificarInconsistencias() {
                    const inconsistencias = [];

                    document.querySelectorAll('input[name^="quantidade_baixa"]').forEach(input => {
                        const row = input.closest('tr');
                        const quantidadeSolicitada = parseFloat(row.children[2].textContent.trim()) || 0;
                        const quantidadeRecebida = parseFloat(input.value) || 0;
                        const descricaoProduto = row.children[1].textContent.trim();

                        if (quantidadeRecebida < quantidadeSolicitada && quantidadeRecebida > 0) {
                            inconsistencias.push({
                                produto: descricaoProduto,
                                solicitada: quantidadeSolicitada,
                                recebida: quantidadeRecebida
                            });
                        }
                    });

                    return inconsistencias;
                }

                function mostrarModal() {
                    console.log('Mostrando modal de inconsistência');
                    modal.classList.remove('hidden');
                    if (justificativaTextarea) {
                        justificativaTextarea.focus();
                    }
                }

                function esconderModal() {
                    console.log('Escondendo modal de inconsistência');
                    modal.classList.add('hidden');
                    if (justificativaTextarea) {
                        justificativaTextarea.value = '';
                    }
                    if (errorJustificativa) {
                        errorJustificativa.classList.add('hidden');
                    }
                }

                function adicionarJustificativaAoFormulario(justificativa) {
                    // Remove input anterior se existir
                    const inputAnterior = document.querySelector('input[name="observacao_inconsistencia"]');
                    if (inputAnterior) {
                        inputAnterior.remove();
                    }

                    // Adiciona novo input com a justificativa
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'observacao_inconsistencia';
                    input.value = justificativa;
                    form.appendChild(input);
                }

                // Event listener para o formulário
                if (form) {
                    form.addEventListener('submit', function(e) {
                        console.log('Submit do formulário detectado');

                        if (formSubmissionPending) {
                            console.log('Submissão já pendente, permitindo');
                            // Permite o submit normal
                            return true;
                        }

                        const inconsistencias = verificarInconsistencias();
                        console.log('Inconsistências detectadas:', inconsistencias.length);

                        if (inconsistencias.length > 0) {
                            // Verifica se já tem justificativa
                            const justificativaExistente = document.querySelector(
                                'input[name="observacao_inconsistencia"]');
                            if (!justificativaExistente || !justificativaExistente.value) {
                                console.log('Nenhuma justificativa encontrada, mostrando modal');
                                e.preventDefault();
                                mostrarModal();
                                return false;
                            }
                            console.log('Justificativa já existe, prosseguindo com submit');
                        }

                        console.log('Enviando formulário - Recebido:', checkbox ? checkbox.checked :
                            'não encontrado');
                        // Permite o submit normal sem interceptar
                        return true;
                    });
                }

                // Event listeners do modal
                if (btnCancelarModal) {
                    btnCancelarModal.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Botão cancelar clicado');
                        esconderModal();
                    });
                }

                if (btnConfirmarModal) {
                    btnConfirmarModal.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Botão confirmar clicado');

                        const justificativa = justificativaTextarea.value.trim();

                        if (!justificativa) {
                            errorJustificativa.classList.remove('hidden');
                            justificativaTextarea.focus();
                            return;
                        }

                        errorJustificativa.classList.add('hidden');
                        adicionarJustificativaAoFormulario(justificativa);
                        esconderModal();

                        // Submete o formulário usando o botão submit original
                        console.log('Justificativa adicionada, submetendo formulário');
                        formSubmissionPending = true;

                        // Dispara o submit do formulário de forma nativa
                        if (btn) {
                            btn.click();
                        } else if (form) {
                            const submitEvent = new Event('submit', {
                                bubbles: true,
                                cancelable: true
                            });
                            form.dispatchEvent(submitEvent);
                            if (!submitEvent.defaultPrevented) {
                                form.submit();
                            }
                        }
                    });
                }

                // Fechar modal ao clicar fora dele
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            esconderModal();
                        }
                    });
                }

                // Observa todos os inputs de quantidade_baixa
                document.querySelectorAll('input[name^="quantidade_baixa"]').forEach(input => {
                    input.addEventListener('input', atualizarBotaoFinalizar);
                });

                // Executa a primeira verificação ao carregar
                atualizarBotaoFinalizar();
            });
        </script>

    </div>
