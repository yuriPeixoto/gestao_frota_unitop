{{-- Definir valores padrão para todas as variáveis que possam não existir --}}
@php
// Definir variáveis principais com valores padrão
$componenteId = $componenteId ?? 'modal-confirmacao-' . uniqid();
$titulo = $titulo ?? 'Confirmação';
$mensagem = $mensagem ?? 'Tem certeza que deseja realizar esta ação?';
$textoBotaoConfirmar = $textoBotaoConfirmar ?? 'Confirmar';
$textoBotaoCancelar = $textoBotaoCancelar ?? 'Cancelar';
$tipo = $tipo ?? 'confirmar';
$formId = $formId ?? null;
$metodo = $metodo ?? 'POST';
$urlConfirmacao = $urlConfirmacao ?? null;
$exibeObservacao = $exibeObservacao ?? false;
$campoObservacao = $campoObservacao ?? 'observacao';
$observacaoObrigatoria = $observacaoObrigatoria ?? false;
$confirmacaoDigitada = $confirmacaoDigitada ?? false;
$textoConfirmacao = $textoConfirmacao ?? 'confirmar';
$aberto = $aberto ?? false;
$classesCss = $classesCss ?? '';

// Funções auxiliares caso os métodos da classe não estejam disponíveis
if (!isset($classesBotaoConfirmar)) {
$classesBotaoConfirmar = match ($tipo) {
'perigo', 'excluir', 'deletar' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
'info', 'informacao' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
'primario' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
'sucesso' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
'alerta', 'aviso' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
default => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
};
}

if (!isset($classeIcone)) {
$classeIcone = match ($tipo) {
'perigo', 'excluir', 'deletar' => 'text-red-600 bg-red-100',
'info', 'informacao' => 'text-blue-600 bg-blue-100',
'primario' => 'text-indigo-600 bg-indigo-100',
'sucesso' => 'text-green-600 bg-green-100',
'alerta', 'aviso' => 'text-yellow-600 bg-yellow-100',
default => 'text-blue-600 bg-blue-100',
};
}

if (!isset($icone)) {
$icone = match ($tipo) {
'perigo', 'excluir', 'deletar' => 'exclamation',
'info', 'informacao' => 'information-circle',
'sucesso' => 'check',
'alerta', 'aviso' => 'exclamation-circle',
default => 'question-mark-circle',
};
}
@endphp

<div id="{{ $componenteId }}" class="fixed inset-0 overflow-y-auto {{ $classesCss }}" x-data="modalConfirmacao({
        titulo: '{{ $titulo }}',
        mensagem: '{{ $mensagem }}',
        textoBotaoConfirmar: '{{ $textoBotaoConfirmar }}',
        textoBotaoCancelar: '{{ $textoBotaoCancelar }}',
        tipo: '{{ $tipo }}',
        formId: '{{ $formId }}',
        metodo: '{{ $metodo }}',
        urlConfirmacao: '{{ $urlConfirmacao }}',
        exibeObservacao: {{ $exibeObservacao ? 'true' : 'false' }},
        campoObservacao: '{{ $campoObservacao }}',
        observacaoObrigatoria: {{ $observacaoObrigatoria ? 'true' : 'false' }},
        confirmacaoDigitada: {{ $confirmacaoDigitada ? 'true' : 'false' }},
        textoConfirmacao: '{{ $textoConfirmacao }}',
        aberto: {{ $aberto ? 'true' : 'false' }}
    })" x-show="aberto" x-cloak style="z-index: 50;" @keydown.escape.window="fechar()">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay para escurecer o fundo -->
        <div x-show="aberto" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity" aria-hidden="true" @click="fechar()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Truque para centralizar verticalmente o modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal -->
        <div x-show="aberto" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            @click.away="fechar()" role="dialog" aria-modal="true" aria-labelledby="{{ $componenteId }}-titulo">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Ícone do modal conforme o tipo -->
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 {{ $classeIcone }}">
                        @if($icone === 'exclamation')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        @elseif($icone === 'information-circle')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @elseif($icone === 'check')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        @elseif($icone === 'exclamation-circle')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @else
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @endif
                    </div>

                    <!-- Conteúdo do modal -->
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="{{ $componenteId }}-titulo">
                            {{ $titulo }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 whitespace-pre-line">
                                {{ $mensagem }}
                            </p>
                        </div>

                        <!-- Formulário para ações que necessitem de dados adicionais -->
                        <div x-show="exibeObservacao || confirmacaoDigitada" class="mt-4">
                            <form id="{{ $componenteId }}-form" x-ref="form" @submit.prevent="confirmar()">
                                <!-- Campo de observação -->
                                <div x-show="exibeObservacao" class="mb-4">
                                    <label for="{{ $componenteId }}-observacao"
                                        class="block text-sm font-medium text-gray-700">
                                        Observação
                                        <span x-show="observacaoObrigatoria" class="text-red-600">*</span>
                                    </label>
                                    <div class="mt-1">
                                        <textarea id="{{ $componenteId }}-observacao" name="{{ $campoObservacao }}"
                                            x-model="observacao" :required="observacaoObrigatoria" rows="3"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Informe o motivo..."></textarea>
                                    </div>
                                </div>

                                <!-- Campo de confirmação digitada -->
                                <div x-show="confirmacaoDigitada" class="mb-4">
                                    <label for="{{ $componenteId }}-confirmacao"
                                        class="block text-sm font-medium text-gray-700">
                                        Digite <span class="font-bold">{{ $textoConfirmacao }}</span> para confirmar:
                                    </label>
                                    <div class="mt-1">
                                        <input type="text" id="{{ $componenteId }}-confirmacao"
                                            x-model="confirmacaoTexto"
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                            required>
                                    </div>
                                    <p x-show="confirmacaoDigitada && confirmacaoTexto && confirmacaoTexto !== textoConfirmacao"
                                        class="mt-1 text-sm text-red-600">
                                        O texto não corresponde.
                                    </p>
                                </div>

                                <!-- Inputs ocultos para quando usar URL -->
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input x-show="metodo !== 'GET' && metodo !== 'POST'" type="hidden" name="_method"
                                    :value="metodo">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rodapé com botões -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm {{ $classesBotaoConfirmar }}"
                    :disabled="confirmacaoDigitada && confirmacaoTexto !== textoConfirmacao" @click="confirmar()">
                    <span x-show="processando" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                    {{ $textoBotaoConfirmar }}
                </button>
                <button type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="fechar()" :disabled="processando">
                    {{ $textoBotaoCancelar }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('modalConfirmacao', (config) => ({
            titulo: config.titulo,
            mensagem: config.mensagem,
            textoBotaoConfirmar: config.textoBotaoConfirmar,
            textoBotaoCancelar: config.textoBotaoCancelar,
            tipo: config.tipo,
            formId: config.formId,
            metodo: config.metodo,
            urlConfirmacao: config.urlConfirmacao,
            exibeObservacao: config.exibeObservacao,
            campoObservacao: config.campoObservacao,
            observacaoObrigatoria: config.observacaoObrigatoria,
            confirmacaoDigitada: config.confirmacaoDigitada,
            textoConfirmacao: config.textoConfirmacao,
            
            // Estado interno
            aberto: config.aberto,
            processando: false,
            observacao: '',
            confirmacaoTexto: '',
            
            // Abrir o modal
            abrir() {
                this.aberto = true;
                this.observacao = '';
                this.confirmacaoTexto = '';
                this.processando = false;
                
                // Foco no primeiro campo quando o modal abre
                this.$nextTick(() => {
                    if (this.exibeObservacao) {
                        document.getElementById('{{ $componenteId }}-observacao')?.focus();
                    } else if (this.confirmacaoDigitada) {
                        document.getElementById('{{ $componenteId }}-confirmacao')?.focus();
                    }
                });
                
                // Disparar evento de abertura
                this.$dispatch('modal-aberto', { id: '{{ $componenteId }}' });
            },
            
            // Fechar o modal
            fechar() {
                if (this.processando) return;
                
                this.aberto = false;
                
                // Disparar evento de fechamento
                this.$dispatch('modal-fechado', { id: '{{ $componenteId }}' });
            },
            
            // Confirmar a ação
            confirmar() {
                // Validações
                if (this.confirmacaoDigitada && this.confirmacaoTexto !== this.textoConfirmacao) {
                    return;
                }
                
                if (this.exibeObservacao && this.observacaoObrigatoria && !this.observacao.trim()) {
                    return;
                }
                
                this.processando = true;
                
                // Se tiver um formulário externo, submit nele
                if (this.formId) {
                    const form = document.getElementById(this.formId);
                    
                    if (form) {
                        // Adicionar campo de observação se necessário
                        if (this.exibeObservacao && this.observacao) {
                            let input = form.querySelector(`[name="${this.campoObservacao}"]`);
                            
                            if (!input) {
                                input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = this.campoObservacao;
                                form.appendChild(input);
                            }
                            
                            input.value = this.observacao;
                        }
                        
                        // Submit no formulário
                        form.submit();
                        return;
                    }
                }
                
                // Se tiver URL de confirmação, enviar para ela
                if (this.urlConfirmacao) {
                    const formData = new FormData();
                    
                    // Adicionar observação se necessário
                    if (this.exibeObservacao && this.observacao) {
                        formData.append(this.campoObservacao, this.observacao);
                    }
                    
                    // Adicionar token CSRF
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    
                    // Adicionar método HTTP para métodos diferentes de GET e POST
                    if (this.metodo !== 'GET' && this.metodo !== 'POST') {
                        formData.append('_method', this.metodo);
                    }
                    
                    // Enviar requisição
                    fetch(this.urlConfirmacao, {
                        method: this.metodo === 'GET' ? 'GET' : 'POST',
                        body: this.metodo === 'GET' ? null : formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.redirect) {
                            window.location.href = data.redirect;
                        } else if (data && data.success) {
                            this.fechar();
                            
                            // Disparar evento de sucesso
                            this.$dispatch('confirmacao-sucesso', { id: '{{ $componenteId }}', data });
                            
                            // Exibir mensagem de sucesso se fornecida
                            if (data.message) {
                                this.mostrarNotificacao(data.message, 'success');
                            }
                        } else {
                            this.processando = false;
                            
                            // Disparar evento de erro
                            this.$dispatch('confirmacao-erro', { id: '{{ $componenteId }}', data });
                            
                            // Exibir mensagem de erro se fornecida
                            if (data && data.message) {
                                this.mostrarNotificacao(data.message, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao confirmar ação:', error);
                        this.processando = false;
                        
                        // Disparar evento de erro
                        this.$dispatch('confirmacao-erro', { id: '{{ $componenteId }}', error });
                        
                        // Exibir mensagem de erro
                        this.mostrarNotificacao('Ocorreu um erro ao processar a ação. Tente novamente.', 'error');
                    });
                } else {
                    // Se não tiver nem form nem URL, apenas fechar e disparar evento de confirmação
                    this.fechar();
                    
                    // Disparar evento de confirmação
                    this.$dispatch('confirmacao', { 
                        id: '{{ $componenteId }}',
                        observacao: this.exibeObservacao ? this.observacao : null
                    });
                }
            },
            
            // Método auxiliar para exibir notificações
            mostrarNotificacao(mensagem, tipo) {
                if (window.toast) {
                    window.toast(mensagem, tipo);
                } else if (window.Livewire) {
                    Livewire.emit('notify', tipo, mensagem);
                } else {
                    alert(mensagem);
                }
            }
        }));
    });
</script>
@endpush