<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Controle de Veículos (Lançamento de Km Manual)') }}
            </h2>
            <div class="flex items-center space-x-4">
                @can('criar_permissaokmmanual')
                <a href="{{ route('admin.permissaokmmanuals.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Cadastrar Permissão
                </a>
                @endcan

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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Controle de
                                    Veículos</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela permite gerenciar as permissões para lançamento de KM manual nos veículos.
                                    Use os filtros para encontrar registros específicos.
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
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.permissaokmmanuals.index') }}" class="space-y-4"
                    hx-get="{{ route('admin.permissaokmmanuals.index') }}" hx-target="#results-table"
                    hx-select="#results-table" hx-trigger="change delay:500ms, search">

                    {{-- Exibir mensagens de erro/confirmação --}}
                    <x-ui.export-message />

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <x-forms.input type="date" name="data_inclusao" label="Data Inclusão Inicial"
                                value="{{ request('data_inclusao') }}" />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_final" label="Data Inclusão Final"
                                value="{{ request('data_final') }}" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                                :options="$formOptions['filiais'] ?? []" :selected="request('id_filial')"
                                asyncSearch="false" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_departamento" label="Departamento"
                                placeholder="Selecione o departamento..." :options="$formOptions['departamentos'] ?? []"
                                :selected="request('id_departamento')" asyncSearch="false" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                                :options="$formOptions['veiculos'] ?? []" :selected="request('id_veiculo')"
                                asyncSearch="false" />
                        </div>

                        <div>
                            <x-forms.smart-select name="id_categoria" label="Categoria"
                                placeholder="Selecione a categoria..." :options="$formOptions['categorias'] ?? []"
                                :selected="request('id_categoria')" asyncSearch="false" />
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">

                        <div class="flex space-x-2">
                            <a href="{{ route('admin.permissaokmmanuals.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.trash class="h-4 w-4 mr-2" />
                                Limpar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    <!-- Loading indicator -->
                    <div id="table-loading"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>

                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        <div class="results-table">
                            <x-tables.table>
                                <x-tables.header>
                                    <x-tables.head-cell>Cód.</x-tables.head-cell>
                                    <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
                                    <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
                                    <x-tables.head-cell>Filial</x-tables.head-cell>
                                    <x-tables.head-cell>Placa</x-tables.head-cell>
                                    <x-tables.head-cell>Departamento</x-tables.head-cell>
                                    <x-tables.head-cell>Categoria</x-tables.head-cell>
                                    {{-- Verificar se tem alguma ação disponível --}}
                                    @if(auth()->user()->hasAnyPermission(['editar_permissaokmmanual',
                                    'excluir_permissaokmmanual']))
                                    <x-tables.head-cell>Ações</x-tables.head-cell>
                                    @endif
                                </x-tables.header>

                                <x-tables.body>
                                    @forelse ($permissaokmmanuals as $index => $permissao)
                                    <x-tables.row :index="$index" data-id="{{ $permissao['id'] }}">
                                        <x-tables.cell>{{ $permissao['id'] }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['data_inclusao'] }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['data_alteracao'] }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['id_filial'] }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['id_veiculo'] }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['id_departamento'] ?? '' }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $permissao['id_categoria'] ?? '' }}</x-tables.cell>
                                        {{-- Coluna Ações - Só aparece se usuário tem permissões --}}
                                        @if(auth()->user()->hasAnyPermission(['editar_permissaokmmanual',
                                        'excluir_permissaokmmanual']))
                                        <x-tables.cell>
                                            <div class="flex items-center space-x-2">
                                                @can('editar_permissaokmmanual')
                                                <a href="{{ route('admin.permissaokmmanuals.edit', $permissao['id']) }}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    title="Editar">
                                                    <x-icons.pencil class="h-3 w-3" />
                                                </a>
                                                @endcan

                                                @can('excluir_permissaokmmanual')
                                                <button type="button"
                                                    onclick="confirmarExclusao({{ $permissao['id'] }})"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                    title="Excluir">
                                                    <x-icons.trash class="h-3 w-3" />
                                                </button>
                                                @endcan
                                            </div>
                                        </x-tables.cell>
                                        @endif
                                    </x-tables.row>
                                    @empty
                                    @php
                                    $totalCols = 7; // Colunas básicas
                                    if(auth()->user()->hasAnyPermission(['editar_permissaokmmanual',
                                    'excluir_permissaokmmanual'])) {
                                    $totalCols = 8; // + coluna ações
                                    }
                                    @endphp
                                    <x-tables.empty cols="{{ $totalCols }}" message="Nenhum registro encontrado" />
                                    @endforelse
                                </x-tables.body>
                            </x-tables.table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado - Inicializando scripts');
            
            // Inicializar manipulação da tabela
            initTableLoading();
        });
        
        function initTableLoading() {
            const loadingElement = document.getElementById('table-loading');
            const resultsElement = document.getElementById('results-table');
            
            if (loadingElement && resultsElement) {
                console.log('Elementos de loading/resultado encontrados');
                
                // Esconder o loading e mostrar os resultados após carregamento da página
                setTimeout(function() {
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                    console.log('Tabela de resultados exibida');
                }, 300);
                
                // Lidar com eventos HTMX
                document.body.addEventListener('htmx:beforeRequest', function(event) {
                    if (event.detail.target && 
                        (event.detail.target.id === 'results-table' || 
                         event.detail.target.closest('#results-table'))) {
                        console.log('HTMX request iniciada - mostrando loading');
                        loadingElement.style.display = 'flex';
                        resultsElement.classList.add('opacity-0');
                    }
                });
                
                document.body.addEventListener('htmx:afterSwap', function(event) {
                    if (event.detail.target && 
                        (event.detail.target.id === 'results-table' || 
                         event.detail.target.closest('#results-table'))) {
                        console.log('HTMX swap concluído - escondendo loading');
                        loadingElement.style.display = 'none';
                        resultsElement.classList.remove('opacity-0');
                        resultsElement.classList.add('opacity-100');
                    }
                });
                
                // Backup em caso de falha no HTMX
                document.body.addEventListener('htmx:responseError', function(event) {
                    console.log('HTMX erro - escondendo loading');
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                });
            } else {
                console.log('Elementos de loading/resultado não encontrados');
            }
        }
        
        @can('excluir_permissaokmmanual')
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta permissão?')) {
                excluirPermissao(id);
            }
        }
        
        function excluirPermissao(id) {
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                console.error('CSRF token não encontrado');
                alert('Erro de segurança: CSRF token não encontrado.');
                return;
            }
            
            // Mostrar indicador de carregamento se disponível
            const loadingElement = document.getElementById('table-loading');
            if (loadingElement) loadingElement.style.display = 'flex';
            
            // Executar a exclusão
            fetch(`/admin/permissaokmmanuals/${id}`, {
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
                if (data.notification) {
                    // Tentar remover a linha da tabela sem recarregar a página
                    const removido = removeRowFromTable(id);
                    
                    if (removido) {
                        // Se conseguiu remover a linha, mostrar mensagem
                        showToast(data.notification.message, data.notification.type);
                        
                        // Esconder o loading se disponível
                        if (loadingElement) loadingElement.style.display = 'none';
                    } else {
                        // Se não conseguiu remover a linha, recarregar a página
                        showToast(data.notification.message + ' Recarregando...', data.notification.type);
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    throw new Error('Resposta inválida do servidor');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                
                // Esconder o loading se disponível
                if (loadingElement) loadingElement.style.display = 'none';
                
                // Mostrar mensagem de erro
                showToast('Erro ao excluir permissão: ' + error.message, 'error');
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
                            emptyRow.innerHTML = `<td colspan="${colSpan}" class="px-6 py-4 text-center text-gray-500">Nenhum registro encontrado</td>`;
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
        
        function showToast(message, type = 'info') {
            // Verificar se existe alguma biblioteca de toast
            if (typeof window.toast === 'function') {
                window.toast(message, type);
            } else if (window.Toastify) {
                // Toastify
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: type === 'success' ? "#48BB78" : type === 'error' ? "#F56565" : "#4299E1"
                }).showToast();
            } else {
                // Fallback para alert em desenvolvimento
                if (type === 'error') {
                    alert(message);
                } else {
                    // Em produção, criar um toast simples
                    const toast = document.createElement('div');
                    toast.style.position = 'fixed';
                    toast.style.right = '20px';
                    toast.style.top = '20px';
                    toast.style.padding = '12px 20px';
                    toast.style.backgroundColor = type === 'success' ? '#48BB78' : type === 'error' ? '#F56565' : '#4299E1';
                    toast.style.color = 'white';
                    toast.style.borderRadius = '4px';
                    toast.style.zIndex = '9999';
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.3s ease-in-out';
                    toast.textContent = message;
                    
                    document.body.appendChild(toast);
                    
                    // Mostrar com animação
                    setTimeout(() => { toast.style.opacity = '1'; }, 10);
                    
                    // Remover após 3 segundos
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => {
                            document.body.removeChild(toast);
                        }, 300);
                    }, 3000);
                }
            }
        }
        @endcan
    </script>
    @endpush
</x-app-layout>