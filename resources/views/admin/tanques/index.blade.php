<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tanque de Combustível') }}
            </h2>
            <div class="flex items-center space-x-4">
                @can('criar_tanque')
                <x-button-link href="{{ route('admin.tanques.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Tanque
                </x-button-link>
                @endcan

                <x-help-icon title="Ajuda - Tanque de Combustível"
                    content="Nesta tela você pode visualizar os tanques cadastrados para todas as filiais. Utilize o botão 'Novo Tanque' para adicionar um novo registro. Vocês pode editar ou excluir tanques existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <!-- Substituímos o componente BladeWind notification pelo componente personalizado -->
            @if(session('notification'))
            <x-notification :notification="session('notification')" />
            @endif

            <div class="max-w-full overflow-x-auto">
                <div class="mb-4">
                    @include('admin.tanques._search-form')
                </div>

                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @include('admin.tanques._table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmação de alteração de status -->
    <div id="toggle-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay de fundo -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                id="toggle-modal-overlay"></div>

            <!-- Centralizador do conteúdo -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Conteúdo do modal -->
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Ícone de alerta -->
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="toggle-modal-title">
                                Confirmar alteração de status
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="toggle-modal-message">
                                    Tem certeza que deseja alterar o status do tanque?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <!-- Usamos DIVs em vez de buttons para maior controle sobre o layout -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="toggle-confirm-button"
                            class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span id="toggle-button-spinner" class="mr-2 hidden">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            <span id="toggle-button-text">Confirmar</span>
                        </button>
                        <button type="button" id="toggle-cancel-button"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let idSelecionado = null;

        function editTanque(id) {
            window.location.href = `{{ route('admin.tanques.edit', ':id') }}`.replace(':id', id)
        }

        // Exibe o modal de confirmação
        function toggleStatusTanque(id, nomeTanque) {
            idSelecionado = id;
            
            // Determinar se o tanque está ativo ou inativo
            const statusBadge = document.querySelector(`#status-badge-${id}`);
            if (!statusBadge) {
                console.error('Elemento de status não encontrado');
                return;
            }
            
            const isAtivo = statusBadge.textContent.trim() === 'Ativo';
            const acao = isAtivo ? 'desativar' : 'ativar';
            
            // Atualizar título e mensagem
            const modal = document.getElementById('toggle-modal');
            const message = document.getElementById('toggle-modal-message');
            const confirmButton = document.getElementById('toggle-confirm-button');
            const buttonText = document.getElementById('toggle-button-text');
            
            message.textContent = `Tem certeza que deseja ${acao} o tanque ${nomeTanque}? Esta ação pode ser revertida mais tarde.`;
            
            // Configurar o botão de confirmação
            buttonText.textContent = isAtivo ? 'Desativar' : 'Ativar';
            
            // Atualizar as cores do botão
            // Remover todas as classes de cor existentes
            confirmButton.classList.remove(
                'bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500',
                'bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500',
                'bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500'
            );
            
            // Adicionar as classes de cor apropriadas
            if (isAtivo) {
                confirmButton.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
            } else {
                confirmButton.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
            }
            
            // Exibir o modal
            modal.classList.remove('hidden');
            
            // Adicionar event listeners
            document.getElementById('toggle-modal-overlay').onclick = closeToggleModal;
            document.getElementById('toggle-cancel-button').onclick = closeToggleModal;
            document.getElementById('toggle-confirm-button').onclick = confirmToggleStatus;
            
            // Adicionar listener de tecla Escape
            document.addEventListener('keydown', escapeListener);
        }
        
        // Fecha o modal
        function closeToggleModal() {
            const modal = document.getElementById('toggle-modal');
            modal.classList.add('hidden');
            
            // Remover event listeners
            document.getElementById('toggle-modal-overlay').onclick = null;
            document.getElementById('toggle-cancel-button').onclick = null;
            document.getElementById('toggle-confirm-button').onclick = null;
            
            // Remover listener de tecla Escape
            document.removeEventListener('keydown', escapeListener);
        }
        
        // Listener para a tecla Escape
        function escapeListener(event) {
            if (event.key === 'Escape') {
                closeToggleModal();
            }
        }

        // Confirma a alteração de status
        async function confirmToggleStatus() {
            try {
                // Mostrar spinner e desabilitar botão
                const confirmButton = document.getElementById('toggle-confirm-button');
                const spinner = document.getElementById('toggle-button-spinner');
                
                if (!confirmButton || !spinner) {
                    console.error('Elementos do botão não encontrados');
                    return;
                }
                
                confirmButton.disabled = true;
                spinner.classList.remove('hidden');
                
                const response = await fetch(
                    `{{ route('admin.tanques.toggle-active', ':id') }}`.replace(':id', idSelecionado),
                    {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }
                );

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                }

                const data = await response.json();

                if (data.notification) {
                    // Em vez de usar showNotification, vamos usar o novo formato de notificação da sessão
                    // Nós vamos redirecionar e passar a notificação na sessão
                    const currentUrl = window.location.href.split('?')[0]; // URL base sem parâmetros
                    const searchParams = new URLSearchParams(window.location.search);
                    
                    // Adicionar notificação temporária na URL
                    searchParams.set('notification', 'true');
                    searchParams.set('notification_type', data.notification.type);
                    searchParams.set('notification_title', data.notification.title);
                    searchParams.set('notification_message', data.notification.message);
                    
                    // Atualiza a interface sem recarregar a página
                    updateStatusDisplay(idSelecionado, data.is_ativo);
                    
                    // Fechar o modal
                    closeToggleModal();

                    // Criar e mostrar a notificação manualmente
                    const notificationData = {
                        type: data.notification.type,
                        title: data.notification.title,
                        message: data.notification.message
                    };
                    
                    showCustomNotification(notificationData);
                }
            } catch (error) {
                console.error('Full error:', error);
                
                // Mostrar notificação de erro
                showCustomNotification({
                    type: 'error',
                    title: 'Erro',
                    message: 'Não foi possível alterar o status do tanque'
                });
            } finally {
                // Esconder spinner e reabilitar botão
                const confirmButton = document.getElementById('toggle-confirm-button');
                const spinner = document.getElementById('toggle-button-spinner');
                
                if (confirmButton && spinner) {
                    confirmButton.disabled = false;
                    spinner.classList.add('hidden');
                }
            }
        }

        function updateStatusDisplay(id, isAtivo) {
            // Atualiza o badge de status
            const statusBadge = document.querySelector(`#status-badge-${id}`);
            if (statusBadge) {
                statusBadge.textContent = isAtivo ? 'Ativo' : 'Inativo';
                
                // Atualiza as classes do badge
                if (isAtivo) {
                    statusBadge.classList.remove('bg-red-100', 'text-red-800');
                    statusBadge.classList.add('bg-green-100', 'text-green-800');
                } else {
                    statusBadge.classList.remove('bg-green-100', 'text-green-800');
                    statusBadge.classList.add('bg-red-100', 'text-red-800');
                }
            }
            
            // Atualiza o botão de toggle
            const toggleButton = document.querySelector(`#toggle-button-${id}`);
            if (toggleButton) {
                // Atualiza as classes do botão
                if (isAtivo) {
                    toggleButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
                    toggleButton.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                } else {
                    toggleButton.classList.remove('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                    toggleButton.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
                }
                
                // Atualiza o ícone dentro do botão
                const icon = toggleButton.querySelector('svg');
                if (icon) {
                    if (isAtivo) {
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />';
                    } else {
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                    }
                }
            }
        }

        function showCustomNotification(notificationData) {
            // Função para criar e mostrar notificação manualmente
            // Clonamos o template do componente de notificação
            
            const notificationContainer = document.createElement('div');
            notificationContainer.innerHTML = `
                <div class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50">
                    <div class="max-w-sm w-full bg-${notificationData.type === 'success' ? 'green' : 
                                               notificationData.type === 'error' ? 'red' : 
                                               notificationData.type === 'warning' ? 'yellow' : 'blue'}-500 shadow-lg rounded-lg pointer-events-auto">
                        <div class="rounded-lg shadow-xs overflow-hidden">
                            <div class="p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="${notificationData.type === 'success' ? 'M5 13l4 4L19 7' : 
                                                    notificationData.type === 'error' ? 'M6 18L18 6M6 6l12 12' : 
                                                    notificationData.type === 'warning' ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' : 
                                                    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 w-0 flex-1 pt-0.5">
                                        <p class="text-sm leading-5 font-medium text-white">
                                            ${notificationData.title}
                                        </p>
                                        <p class="mt-1 text-sm leading-5 text-white opacity-90">
                                            ${notificationData.message}
                                        </p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex">
                                        <button class="inline-flex text-white focus:outline-none close-notification">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Adicionar ao DOM
            document.body.appendChild(notificationContainer);
            
            // Configurar botão de fechar
            const closeButton = notificationContainer.querySelector('.close-notification');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    notificationContainer.remove();
                });
            }
            
            // Auto-remover após 5 segundos
            setTimeout(() => {
                if (document.body.contains(notificationContainer)) {
                    // Adicionar classe de transição para fade-out
                    notificationContainer.style.opacity = '0';
                    notificationContainer.style.transition = 'opacity 0.3s ease-in-out';
                    
                    // Remover após a transição
                    setTimeout(() => {
                        if (document.body.contains(notificationContainer)) {
                            notificationContainer.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tableLoading = document.getElementById('table-loading');
            const resultsTable = document.getElementById('results-table');
            
            // Função para verificar se a tabela está completamente carregada
            function checkTableReady() {
                if (document.querySelectorAll('#results-table table tbody tr').length > 0) {
                    // Esconde o loading e mostra a tabela com uma pequena transição
                    setTimeout(function() {
                        tableLoading.style.opacity = '0';
                        resultsTable.classList.remove('opacity-0');
                            
                        // Remove completamente o loading após a transição
                        setTimeout(function() {
                            tableLoading.style.display = 'none';
                        }, 300);
                    }, 300);
                } else {
                    // Tenta novamente em 100ms se ainda não estiver pronto
                    setTimeout(checkTableReady, 100);
                }
            }
            
            // Inicia a verificação
            setTimeout(checkTableReady, 500);
            
            // Mostra loading quando o formulário de busca for submetido
            const searchForm = document.querySelector('form');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    tableLoading.style.display = 'flex';
                    tableLoading.style.opacity = '1';
                    resultsTable.classList.add('opacity-0');
                });
            }
            
            // Se estiver usando HTMX, intercepta os eventos
            document.body.addEventListener('htmx:beforeRequest', function(evt) {
                if (evt.detail.target.id === 'results-table') {
                    tableLoading.style.display = 'flex';
                    tableLoading.style.opacity = '1';
                    resultsTable.classList.add('opacity-0');
                }
            });
            
            document.body.addEventListener('htmx:afterRequest', function(evt) {
                if (evt.detail.target.id === 'results-table') {
                    setTimeout(function() {
                        tableLoading.style.opacity = '0';
                        resultsTable.classList.remove('opacity-0');
                        
                        setTimeout(function() {
                            tableLoading.style.display = 'none';
                        }, 300);
                    }, 300);
                }
            });
            
            // Verificar parâmetros da URL para notificações
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('notification') === 'true') {
                const notificationType = urlParams.get('notification_type');
                const notificationTitle = urlParams.get('notification_title');
                const notificationMessage = urlParams.get('notification_message');
                
                if (notificationType && notificationTitle && notificationMessage) {
                    showCustomNotification({
                        type: notificationType,
                        title: notificationTitle,
                        message: notificationMessage
                    });
                    
                    // Limpar parâmetros da URL
                    urlParams.delete('notification');
                    urlParams.delete('notification_type');
                    urlParams.delete('notification_title');
                    urlParams.delete('notification_message');
                    
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState({}, '', newUrl);
                }
            }
        });

        @if(session('notification'))
            // Usar a notificação da sessão
            document.addEventListener('DOMContentLoaded', function() {
                const notification = @json(session('notification'));
                showCustomNotification(notification);
            });
        @endif
    </script>
    @endpush
</x-app-layout>