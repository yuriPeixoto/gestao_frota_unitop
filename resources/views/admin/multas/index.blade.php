<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Multas') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.multas.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Multa
                </a>

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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Multas</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar todas as multas cadastradas. Utilize o botão
                                    'Cadastrar Multa' para adicionar um
                                    novo registro. Você pode buscar, editar ou excluir multas existentes utilizando
                                    as opções disponíveis.
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
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Formulário de Busca -->
                <form method="GET" action="{{ route('admin.multas.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Busca Geral</label>
                            <input type="text" id="search" name="search"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ request('search') }}" placeholder="ID, Auto Infração...">
                        </div>

                        <div>
                            <x-forms.smart-select name="id_veiculo" label="Veículo" :options="$placasData"
                                :selected="request('id_veiculo')" placeholder="Selecione um veículo"
                                :searchUrl="route('admin.api.veiculos.search')" />
                        </div>

                        <div>
                            <label for="status_multa" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status_multa" name="status_multa"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todos os status</option>
                                <option value="Em Andamento" {{ request('status_multa')=='Em Andamento' ? 'selected'
                                    : '' }}>Em Andamento</option>
                                <option value="Finalizada" {{ request('status_multa')=='Finalizada' ? 'selected' : ''
                                    }}>Finalizada</option>
                            </select>
                        </div>

                        <div>
                            <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
                            <select id="situacao" name="situacao"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todas as situações</option>
                                <option value="Financeiro" {{ request('situacao')=='Financeiro' ? 'selected' : '' }}>
                                    Financeiro</option>
                                <option value="Notificação" {{ request('situacao')=='Notificação' ? 'selected' : '' }}>
                                    Notificação</option>
                                <option value="Recurso" {{ request('situacao')=='Recurso' ? 'selected' : '' }}>Recurso
                                </option>
                                <option value="Embarcadora" {{ request('situacao')=='Embarcadora' ? 'selected' : '' }}>
                                    Embarcadora</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <div>
                            <!-- Opções de exportação podem ser adicionadas aqui posteriormente -->
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('admin.multas.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Limpar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resultados da Tabela com Indicador de Carregamento -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Indicador de carregamento -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-indigo-500"></div>
                        <span class="ml-3 text-indigo-500 font-medium">Carregando dados...</span>
                    </div>

                    <!-- Resultados da tabela -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        @if(isset($searchTerm) && $searchTerm)
                        <div class="mb-4 text-sm text-gray-600">
                            Encontrados {{ $totalRegistros ?? 0 }} resultado(s) para "{{ $searchTerm }}"
                        </div>
                        @endif

                        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">ID</th>
                                        <th scope="col" class="py-3 px-6">Auto Infração</th>
                                        <th scope="col" class="py-3 px-6">Data Inclusão</th>
                                        <th scope="col" class="py-3 px-6">Data Infração</th>
                                        <th scope="col" class="py-3 px-6">Veículo</th>
                                        <th scope="col" class="py-3 px-6">Condutor</th>
                                        <th scope="col" class="py-3 px-6">Valor</th>
                                        <th scope="col" class="py-3 px-6">Situação</th>
                                        <th scope="col" class="py-3 px-6">Status</th>
                                        <th scope="col" class="py-3 px-6">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($multas as $index => $multa)
                                    <tr data-id="{{ $multa->id_motivo_multa }}"
                                        class="bg-white border-b hover:bg-gray-50">
                                        <td class="py-3 px-6">{{ $multa->id_motivo_multa }}</td>
                                        <td class="py-3 px-6">{{ $multa->auto_infracao ?? 'N/A' }}</td>
                                        <td class="py-3 px-6">{{ $multa->data_inclusao ? date('d/m/Y',
                                            strtotime($multa->data_inclusao)) : 'N/A' }}</td>
                                        <td class="py-3 px-6">{{ $multa->data_infracao ? date('d/m/Y',
                                            strtotime($multa->data_infracao)) : 'N/A' }}</td>
                                        <td class="py-3 px-6">{{ $multa->veiculo->placa ?? 'N/A' }}</td>
                                        <td class="py-3 px-6">{{ $multa->condutor->nome ?? 'N/A' }}</td>
                                        <td class="py-3 px-6">{{ $multa->valor_multa ? 'R$ ' .
                                            number_format($multa->valor_multa, 2, ',', '.') : 'N/A' }}</td>
                                        <td class="py-3 px-6">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $multa->situacao == 'Financeiro' ? 'bg-blue-100 text-blue-800' : 
                                                   ($multa->situacao == 'Notificação' ? 'bg-yellow-100 text-yellow-800' : 
                                                   ($multa->situacao == 'Recurso' ? 'bg-purple-100 text-purple-800' : 
                                                   ($multa->situacao == 'Embarcadora' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))) }}">
                                                {{ $multa->situacao ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-6">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $multa->status_multa == 'Em Andamento' ? 'bg-orange-100 text-orange-800' : 
                                                   ($multa->status_multa == 'Finalizada' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ $multa->status_multa ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-6">
                                            <div class="flex items-center space-x-2">
                                                {{-- <a href="{{ route('admin.multas.show', $multa->id_motivo_multa) }}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <x-icons.eye class="h-3 w-3" />
                                                </a> --}}
                                                <a href="{{ route('admin.multas.edit', $multa->id_motivo_multa) }}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <x-icons.pencil class="h-3 w-3" />
                                                </a>
                                                <button type="button"
                                                    onclick="confirmarExclusao({{ $multa->id_motivo_multa }})"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <x-icons.trash class="h-3 w-3" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="10" class="py-3 px-6 text-center text-gray-500">Nenhum registro
                                            encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $multas->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="modal-excluir" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50"
            onclick="document.getElementById('modal-excluir').classList.add('hidden')"></div>
        <div class="bg-white rounded-lg max-w-md mx-auto p-6 z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Confirmar Exclusão</h3>
                <button type="button" onclick="document.getElementById('modal-excluir').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Fechar</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-2">
                <p class="text-sm text-gray-500">Tem certeza que deseja excluir esta multa? Esta ação não pode ser
                    desfeita.</p>
            </div>
            <div class="mt-4 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('modal-excluir').classList.add('hidden')"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-exclusao"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Excluir
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Inicialização da página
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar o carregamento da tabela
            initTableLoading();
            
            // Exibir notificações se existirem
            @if(session()->has('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            
            @if(session()->has('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
        });
        
        // Função para inicializar o carregamento da tabela
        function initTableLoading() {
            const loadingElement = document.getElementById('table-loading');
            const resultsElement = document.getElementById('results-table');
            
            if (loadingElement && resultsElement) {
                // Esconder o carregamento e mostrar os resultados após o carregamento da página
                setTimeout(function() {
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }, 300);
            }
        }
        
        // Funções para exclusão de Multa
        let multaIdParaExcluir = null;
        
        function confirmarExclusao(id) {
            multaIdParaExcluir = id;
            document.getElementById('modal-excluir').classList.remove('hidden');
            document.getElementById('btn-confirmar-exclusao').onclick = function() {
                excluirMulta(multaIdParaExcluir);
            };
        }
        
        function excluirMulta(id) {
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                showToast('Erro de segurança: CSRF token não encontrado.', 'error');
                return;
            }
            
            // Mostrar indicador de carregamento
            const loadingElement = document.getElementById('table-loading');
            if (loadingElement) loadingElement.style.display = 'flex';
            
            // Executar a exclusão
            fetch(`{{ route('admin.multas.destroy', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Fechar o modal
                document.getElementById('modal-excluir').classList.add('hidden');
                
                if (data.notification) {
                    // Remover a linha da tabela
                    const removido = removeRowFromTable(id);
                    
                    if (removido) {
                        showToast(data.notification.message, data.notification.type);
                        
                        // Esconder o indicador de carregamento
                        if (loadingElement) loadingElement.style.display = 'none';
                    } else {
                        // Recarregar a página se não conseguir remover a linha
                        showToast('Multa excluída com sucesso. Recarregando...', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    throw new Error('Resposta inválida do servidor');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Esconder o indicador de carregamento
                if (loadingElement) loadingElement.style.display = 'none';
                
                // Fechar o modal
                document.getElementById('modal-excluir').classList.add('hidden');
                
                // Mostrar mensagem de erro
                showToast('Erro ao excluir multa: ' + error.message, 'error');
            });
        }
        
        function removeRowFromTable(id) {
            try {
                // Tentar encontrar a linha na tabela
                const row = document.querySelector(`tr[data-id="${id}"]`);
                
                if (row) {
                    // Animar a remoção da linha
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    
                    // Remover a linha após a animação
                    setTimeout(() => {
                        row.remove();
                        
                        // Verificar se a tabela ficou vazia
                        const tbody = document.querySelector('table tbody');
                        if (tbody && tbody.children.length === 0) {
                            // Adicionar mensagem de tabela vazia
                            const colSpan = document.querySelectorAll('table thead th').length;
                            const emptyRow = document.createElement('tr');
                            emptyRow.innerHTML = `<td colspan="${colSpan}" class="py-3 px-6 text-center text-gray-500">Nenhum registro encontrado</td>`;
                            tbody.appendChild(emptyRow);
                        }
                    }, 300);
                    
                    return true;
                }
                
                return false;
            } catch (error) {
                console.error('Erro ao remover linha da tabela:', error);
                return false;
            }
        }
        
        // Função para exibir notificações toast
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed right-4 top-4 px-4 py-2 rounded shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Animate
            toast.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            }, 10);
            
            // Remove after delay
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }
    </script>
    @endpush
</x-app-layout>