<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastro de Tipo de Certificados') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.tipocertificados.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Tipo de Certificado
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Cadastro de Tipo de Certificados</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar todos os Tipos de Certificados cadastrados. Utilize o botão 'Novo Tipo de Certificado' para adicionar um novo registro. Você pode editar ou excluir tipos existentes utilizando as ações disponíveis em cada linha da tabela.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.tipocertificados.index') }}" class="space-y-4"
                    hx-get="{{ route('admin.tipocertificados.index') }}" hx-target="#results-table" hx-select="#results-table"
                    hx-trigger="change delay:500ms, search">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-forms.input name="search" label="Buscar" placeholder="Buscar por descrição, código ou órgão..." 
                                value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="flex justify-between mt-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.tipocertificados.index') }}"
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
                    <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10">
                        <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                    </div>
                    
                    <!-- Actual results -->
                    <div id="results-table" class="opacity-0 transition-opacity duration-300">
                        <x-tables.table>
                            <x-tables.header>
                                <x-tables.head-cell>Código</x-tables.head-cell>
                                <x-tables.head-cell>Descrição</x-tables.head-cell>
                                <x-tables.head-cell>Órgão Certificador</x-tables.head-cell>
                                <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                                <x-tables.head-cell>Data Alteração</x-tables.head-cell>
                                <x-tables.head-cell>Ações</x-tables.head-cell>
                            </x-tables.header>

                            <x-tables.body>
                                @forelse ($tipocertificados as $index => $certificado)
                                    <x-tables.row :index="$index">
                                        <x-tables.cell>{{ $certificado->id_tipo_certificado }}</x-tables.cell>
                                        <x-tables.cell>{{ $certificado->descricao_certificado }}</x-tables.cell>
                                        <x-tables.cell>{{ $certificado->orgao_certificado }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $certificado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                                        <x-tables.cell nowrap>{{ $certificado->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                                        <x-tables.cell>
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('admin.tipocertificados.edit', $certificado->id_tipo_certificado) }}"
                                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    <x-icons.pencil class="h-3 w-3" />
                                                </a>

                                                @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                                    <button type="button" onclick="confirmarExclusao({{ $certificado->id_tipo_certificado }})"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <x-icons.trash class="h-3 w-3" />
                                                    </button>
                                                @endif
                                            </div>
                                        </x-tables.cell>
                                    </x-tables.row>
                                @empty
                                    <x-tables.empty cols="6" message="Nenhum registro encontrado" />
                                @endforelse
                            </x-tables.body>
                        </x-tables.table>

                        <div class="mt-4">
                            {{ $tipocertificados->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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
        });

        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este tipo de certificado?')) {
                fetch(`/admin/tipocertificados/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.notification) {
                        alert(data.notification.message);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao excluir o tipo de certificado');
                });
            }
        }
    </script>
    @endpush
</x-app-layout>