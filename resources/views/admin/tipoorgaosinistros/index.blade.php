<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tipo Órgão') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.tipoorgaosinistros.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Tipo
                </x-button-link>
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Tipo Órgão</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar todos os tipos de órgão cadastrados. Utilize o botão
                                    'Novo Tipo' para adicionar um novo registro. Você pode editar ou excluir tipos
                                    existentes utilizando as ações disponíveis em cada linha da tabela.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div id="notification" class="mb-4"></div>

            <div class="overflow-x-auto relative min-h-[400px]">
                <!-- Loading indicator -->
                <div id="table-loading"
                    class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10"
                    style="display: none;">
                    <div class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-gray-700">Carregando...</span>
                    </div>
                </div>

                <div class="bg-white overflow-hidden p-4 shadow-sm sm:rounded-lg">
                    @include('admin.tipoorgaosinistros._search-form')

                    <div class="mt-4">
                        <!-- Actual results -->
                        <div id="results-table">
                            <x-tables.table>
                                <x-tables.header>
                                    <x-tables.head-cell>Código</x-tables.head-cell>
                                    <x-tables.head-cell>Descrição</x-tables.head-cell>
                                    <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                                    <x-tables.head-cell>Data Alteração</x-tables.head-cell>
                                    <x-tables.head-cell>Ações</x-tables.head-cell>
                                </x-tables.header>

                                <x-tables.body>
                                    @forelse ($tipoorgaosinistros as $index => $tipo)
                                        <x-tables.row :index="$index" data-id="{{ $tipo['id'] }}">
                                            <x-tables.cell>{{ $tipo['id'] }}</x-tables.cell>
                                            <x-tables.cell>{{ $tipo['descricao'] }}</x-tables.cell>
                                            <x-tables.cell>{{ $tipo['Data Inclusão'] }}</x-tables.cell>
                                            <x-tables.cell>{{ $tipo['Data Alteração'] }}</x-tables.cell>
                                            <x-tables.cell>
                                                <div class="flex items-center space-x-2">
                                                    {{-- <button type="button" onclick="visualizarTipoOrgao({{ $tipo['id'] }})"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                    title="Visualizar">
                                                    <x-icons.eye class="h-3 w-3" />
                                                </button> --}}
                                                    <button type="button"
                                                        onclick="editarTipoOrgao({{ $tipo['id'] }})"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                        title="Editar">
                                                        <x-icons.pencil class="h-3 w-3" />
                                                    </button>
                                                    <button type="button"
                                                        onclick="confirmarExclusao({{ $tipo['id'] }}, '{{ $tipo['descricao'] }}')"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                        title="Excluir">
                                                        <x-icons.trash class="h-3 w-3" />
                                                    </button>
                                                </div>
                                            </x-tables.cell>
                                        </x-tables.row>
                                    @empty
                                        <x-tables.empty cols="5" message="Nenhum tipo de órgão encontrado" />
                                    @endforelse
                                </x-tables.body>
                            </x-tables.table>

                            <!-- Paginação -->
                            <div class="mt-4">
                                {{ $tipoorgaosinistros->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão (JavaScript puro) -->
    <div id="delete-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="modal-backdrop"></div>

            <!-- Modal content -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                Confirmar exclusão
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Tem certeza que deseja excluir o tipo <span id="tipo-descricao"
                                        class="font-semibold"></span>?
                                    <br>Esta ação não pode ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="botao-excluir"
                        class="inline-flex justify-center items-center w-auto rounded-md border border-transparent px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:text-sm">
                        <span>Excluir</span>
                        <span id="spinner-exclusao" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                    <button type="button" id="botao-cancelar"
                        class="inline-flex justify-center items-center w-auto mt-0 rounded-md border border-gray-300 px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Variáveis globais
            let idExclusao = null;
            const modal = document.getElementById('delete-modal');
            const backdrop = document.getElementById('modal-backdrop');
            const btnExcluir = document.getElementById('botao-excluir');
            const btnCancelar = document.getElementById('botao-cancelar');
            const descricaoSpan = document.getElementById('tipo-descricao');
            const spinner = document.getElementById('spinner-exclusao');

            // Função para visualizar tipo de órgão
            function visualizarTipoOrgao(id) {
                window.location.href = `{{ route('admin.tipoorgaosinistros.show', ':id') }}`.replace(':id', id);
            }

            // Função para editar tipo de órgão
            function editarTipoOrgao(id) {
                window.location.href = `{{ route('admin.tipoorgaosinistros.edit', ':id') }}`.replace(':id', id);
            }

            // Função para abrir o modal de confirmação
            function confirmarExclusao(id, descricao) {
                idExclusao = id;
                descricaoSpan.textContent = descricao;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden'); // Previne rolagem de fundo
            }

            // Função para fechar o modal
            function fecharModal() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                idExclusao = null;
            }

            // Função para executar a exclusão
            function executarExclusao() {
                if (!idExclusao) return;

                // Desabilitar botão e mostrar spinner
                btnExcluir.disabled = true;
                spinner.classList.remove('hidden');

                // Executar exclusão via AJAX
                fetch(`{{ route('admin.tipoorgaosinistros.destroy', ':id') }}`.replace(':id', idExclusao), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.notification ? data.notification.message :
                                    `Erro HTTP: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Fechar modal para que a notificação seja visível
                        fecharModal();

                        // Remover linha da tabela
                        const row = document.querySelector(`tr[data-id="${idExclusao}"]`);
                        if (row) {
                            row.remove();
                        }

                        // Mostrar notificação
                        showNotification(
                            data.notification.title,
                            data.notification.message,
                            data.notification.type
                        );

                        // Recarregar página para atualizar a paginação (opcional)
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Erro ao excluir:', error);

                        // Fechar modal para que a notificação seja visível
                        fecharModal();

                        // Notificar erro
                        showNotification(
                            'Erro',
                            error.message || 'Não foi possível excluir o tipo de órgão',
                            'error'
                        );
                    })
                    .finally(() => {
                        // Restaurar botão
                        btnExcluir.disabled = false;
                        spinner.classList.add('hidden');
                    });
            }

            // Evento para fechar modal ao clicar no backdrop
            backdrop.addEventListener('click', fecharModal);

            // Evento para o botão cancelar
            btnCancelar.addEventListener('click', fecharModal);

            // Evento para o botão excluir
            btnExcluir.addEventListener('click', executarExclusao);

            // Função para mostrar notificações
            function showNotification(title, message, type = 'success') {
                const notificationDiv = document.getElementById('notification');

                // Definir cores com base no tipo
                let bgColor, textColor, borderColor;

                switch (type) {
                    case 'success':
                        bgColor = 'bg-green-100';
                        textColor = 'text-green-800';
                        borderColor = 'border-green-400';
                        break;
                    case 'error':
                        bgColor = 'bg-red-100';
                        textColor = 'text-red-800';
                        borderColor = 'border-red-400';
                        break;
                    default:
                        bgColor = 'bg-blue-100';
                        textColor = 'text-blue-800';
                        borderColor = 'border-blue-400';
                }

                const html = `
                <div class="p-4 ${bgColor} ${textColor} border-l-4 ${borderColor} rounded-md flex justify-between items-start">
                    <div>
                        <h3 class="font-medium">${title}</h3>
                        <p>${message}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 ${bgColor} ${textColor} rounded-lg p-1.5 inline-flex h-8 w-8">
                        <span class="sr-only">Fechar</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                `;

                notificationDiv.innerHTML = html;

                // Auto-remover após 5 segundos
                setTimeout(() => {
                    const notification = notificationDiv.querySelector('div');
                    if (notification) {
                        notification.remove();
                    }
                }, 5000);
            }

            // Verificar se há notificação de sessão
            document.addEventListener('DOMContentLoaded', function() {
                @if (session('notification') && is_array(session('notification')))
                    showNotification(
                        '{{ session('notification')['title'] }}',
                        '{{ session('notification')['message'] }}',
                        '{{ session('notification')['type'] }}'
                    );
                @endif
            });
        </script>
    @endpush
</x-app-layout>
