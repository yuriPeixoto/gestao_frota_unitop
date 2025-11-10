@props([
    'id' => 'confirmation-modal',
    'title' => 'Confirmação',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmButtonClass' => 'bg-indigo-600 hover:bg-indigo-700',
    'cancelButtonClass' => 'bg-gray-500 hover:bg-gray-600',
    'icon' => null,
    'iconClass' => 'text-yellow-400',
    'width' => 'max-w-md'
])

<div 
    x-data="confirmationModal()" 
    x-init="init()"
    @open-confirmation-modal.window="open($event.detail)"
    @close-confirmation-modal.window="close()"
    @keydown.escape.window="close()"
    id="{{ $id }}" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    x-show="isOpen"
    x-cloak
    role="dialog" 
    aria-modal="true"
>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div 
            x-show="isOpen" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-200" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            @click="close()" 
            aria-hidden="true"
        ></div>

        <!-- Modal panel -->
        <div 
            x-show="isOpen" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
            x-transition:leave="ease-in duration-200" 
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle {{ $width }} sm:w-full"
        >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Icon (if provided) -->
                    @if ($icon)
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <span class="{{ $iconClass }} text-xl">
                            {{ $icon }}
                        </span>
                    </div>
                    @endif

                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="title || '{{ $title }}'"></h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" x-html="message"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button 
                    type="button" 
                    x-ref="confirmButton"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white {{ $confirmButtonClass }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" 
                    @click="confirm()"
                    x-text="confirmText || '{{ $confirmText }}'"
                ></button>
                <button 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" 
                    @click="cancel()"
                    x-text="cancelText || '{{ $cancelText }}'"
                ></button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmationModal() {
    return {
        isOpen: false,
        title: null,
        message: '',
        confirmText: null,
        cancelText: null,
        confirmRoute: null,
        confirmParams: {},
        confirmMethod: 'POST',
        confirmCallback: null,
        cancelCallback: null,
        
        init() {
            this.isOpen = false;
        },
        
        open(options) {
            this.title = options.title || null;
            this.message = options.message || '';
            this.confirmText = options.confirmText || null;
            this.cancelText = options.cancelText || null;
            this.confirmRoute = options.confirmRoute || null;
            this.confirmParams = options.params || {};
            this.confirmMethod = options.method || 'POST';
            this.confirmCallback = options.onConfirm || null;
            this.cancelCallback = options.onCancel || null;
            
            this.isOpen = true;
            
            // Focus no botão de confirmar após um breve delay
            setTimeout(() => {
                if (this.$refs.confirmButton) {
                    this.$refs.confirmButton.focus();
                }
            }, 100);
        },
        
        close() {
            this.isOpen = false;
        },
        
        confirm() {
            if (this.confirmCallback && typeof this.confirmCallback === 'function') {
                this.confirmCallback();
            } else if (this.confirmRoute) {
                // Executa uma requisição HTTP para a rota especificada
                this.submitForm();
            }
            
            this.close();
        },
        
        cancel() {
            if (this.cancelCallback && typeof this.cancelCallback === 'function') {
                this.cancelCallback();
            }
            
            this.close();
        },
        
        submitForm() {
            // Criar um formulário dinâmico para submeter a requisição
            const form = document.createElement('form');
            form.method = this.confirmMethod;
            form.action = this.confirmRoute;
            form.style.display = 'none';
            
            // Adicionar o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
            
            // Se o método não for GET ou POST, utilizar o campo _method para spoofing
            if (['GET', 'POST'].indexOf(this.confirmMethod.toUpperCase()) === -1) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = this.confirmMethod;
                form.appendChild(methodInput);
            }
            
            // Adicionar os parâmetros ao formulário
            for (const key in this.confirmParams) {
                if (Object.prototype.hasOwnProperty.call(this.confirmParams, key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = this.confirmParams[key];
                    form.appendChild(input);
                }
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    };
}
</script>

<style>
    /* Esconde elementos quando x-cloak está presente */
    [x-cloak] { display: none !important; }
</style>