<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Usuários por Departamentos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.usuarios.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Voltar para Usuários
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Usuários e Departamentos</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar e atualizar as relações entre usuários, departamentos, cargos e filiais.
                                    Clique em "Editar Relações" para modificar as atribuições de um usuário.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Filtros de busca -->
                    <form method="GET" action="{{ route('admin.usuarios.list-with-departments') }}" class="space-y-4 mb-6"
                        hx-get="{{ route('admin.usuarios.list-with-departments') }}" hx-target="#results-table" hx-select="#results-table"
                        hx-trigger="change delay:500ms, search">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-forms.input name="search" label="Buscar usuário" value="{{ request('search') }}" placeholder="Nome ou email" />
                            </div>
                            <div>
                                <x-forms.smart-select
                                    name="departamento_id"
                                    label="Departamento"
                                    placeholder="Todos os departamentos"
                                    :options="$departamentos"
                                    :selected="request('departamento_id')"
                                    asyncSearch="false"
                                />
                            </div>
                            <div>
                                <x-forms.smart-select
                                    name="filial_id"
                                    label="Filial"
                                    placeholder="Todas as filiais"
                                    :options="$filiais"
                                    :selected="request('filial_id')"
                                    asyncSearch="false"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.usuarios.list-with-departments') }}"
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
                    </form>

                    <!-- Tabela de Resultados -->
                    <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                        <!-- Loading indicator -->
                        <div id="table-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10" style="display: none;">
                            <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                        </div>
                        
                        <!-- Actual results -->
                        <div id="results-table">
                            <x-tables.table>
                                <x-tables.header>
                                    <x-tables.head-cell>Nome</x-tables.head-cell>
                                    <x-tables.head-cell>Email</x-tables.head-cell>
                                    <x-tables.head-cell>Departamento</x-tables.head-cell>
                                    <x-tables.head-cell>Cargo</x-tables.head-cell>
                                    <x-tables.head-cell>Filial</x-tables.head-cell>
                                    <x-tables.head-cell>Ações</x-tables.head-cell>
                                </x-tables.header>

                                <x-tables.body>
                                    @forelse ($users as $index => $user)
                                        <x-tables.row :index="$index">
                                            <x-tables.cell>{{ $user->name }}</x-tables.cell>
                                            <x-tables.cell>{{ $user->email }}</x-tables.cell>
                                            <x-tables.cell>{{ $user->departamento->descricao_departamento ?? 'Não atribuído' }}</x-tables.cell>
                                            <x-tables.cell>{{ $user->tipoPessoal->descricao_tipo ?? 'Não atribuído' }}</x-tables.cell>
                                            <x-tables.cell>{{ $user->filial->name ?? 'Não atribuída' }}</x-tables.cell>
                                            <x-tables.cell>
                                                <div class="flex items-center space-x-2">
                                                    <button type="button" onclick="openUserEditModal({{ $user->id }})"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <x-icons.pencil class="h-3 w-3" />
                                                    </button>
                                                </div>
                                            </x-tables.cell>
                                        </x-tables.row>
                                    @empty
                                        <x-tables.empty cols="6" message="Nenhum usuário encontrado" />
                                    @endforelse
                                </x-tables.body>
                            </x-tables.table>

                            @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <div class="mt-4">
                                    {{ $users->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Relações -->
    <div id="userEditModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-700 bg-opacity-50"
        x-data="{ 
            open: false,
            userId: null,
            departamentoId: '',
            tipoId: '',
            filialId: '',
            errors: {},
            
            init() {
                this.$watch('open', value => {
                    if (!value) {
                        document.body.classList.remove('overflow-hidden');
                    } else {
                        document.body.classList.add('overflow-hidden');
                    }
                });
            },
            
            openModal(userId, departamentoId, tipoId, filialId) {
                this.userId = userId;
                this.departamentoId = departamentoId || '';
                this.tipoId = tipoId || '';
                this.filialId = filialId || '';
                this.errors = {};
                this.open = true;
            },
            
            closeModal() {
                this.open = false;
            },
            
            submitForm() {
                this.errors = {};
                
                fetch(`/admin/usuarios/${this.userId}/relacoes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        'departamento_id': this.departamentoId,
                        'id_tipo_pessoal': this.tipoId,
                        'filial_id': this.filialId,
                        '_method': 'POST'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            if (data.errors) {
                                this.errors = data.errors;
                                throw new Error('Validation failed');
                            }
                            throw new Error(data.message || 'Erro ao atualizar relações');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    this.closeModal();
                    alert('Relações atualizadas com sucesso!');
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Erro:', error);
                    if (error.message !== 'Validation failed') {
                        alert(error.message || 'Erro ao atualizar relações');
                    }
                });
            }
        }"
        x-show="open"
        x-cloak
        @keydown.escape.window="closeModal()">
        
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4" x-show="open" x-transition>
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Editar Relações do Usuário</h2>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-4">
                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Departamento -->
                    <div>
                        <label for="departamento_id" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select id="departamento_id" x-model="departamentoId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach ($departamentos as $departamento)
                                <option value="{{ $departamento->value }}">{{ $departamento->label }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.departamento_id" x-text="errors.departamento_id" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Cargo -->
                    <div>
                        <label for="id_tipo_pessoal" class="block text-sm font-medium text-gray-700">Cargo</label>
                        <select id="id_tipo_pessoal" x-model="tipoId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach ($cargos as $cargo)
                                <option value="{{ $cargo->value }}">{{ $cargo->label }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.id_tipo_pessoal" x-text="errors.id_tipo_pessoal" class="mt-1 text-sm text-red-600"></p>
                    </div>

                    <!-- Filial -->
                    <div>
                        <label for="filial_id" class="block text-sm font-medium text-gray-700">Filial</label>
                        <select id="filial_id" x-model="filialId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Selecione...</option>
                            @foreach ($filiais as $filial)
                                <option value="{{ $filial->value }}">{{ $filial->label }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.filial_id" x-text="errors.filial_id" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </form>
            </div>
            
            <div class="px-4 py-3 bg-gray-50 text-right rounded-b-lg border-t border-gray-200">
                <button type="button" @click="closeModal()" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                    Cancelar
                </button>
                <button type="button" @click="submitForm()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Salvar
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openUserEditModal(userId) {
                const user = {!! json_encode($users->keyBy('id')) !!}[userId];
                
                if (user) {
                    const departamentoId = user.departamento_id || '';
                    const tipoId = user.pessoal_id || '';
                    const filialId = user.filial_id || '';
                    
                    // Usar o Alpine.js para abrir o modal
                    const modal = document.getElementById('userEditModal').__x.$data;
                    modal.openModal(userId, departamentoId, tipoId, filialId);
                } else {
                    console.error('Usuário não encontrado:', userId);
                    alert('Erro: Usuário não encontrado');
                }
            }

            // Gerenciar o carregamento da tabela
            document.addEventListener('DOMContentLoaded', function() {
                const searchForm = document.querySelector('form');
                const tableLoading = document.getElementById('table-loading');
                const resultsTable = document.getElementById('results-table');
                
                if (searchForm) {
                    searchForm.addEventListener('submit', function() {
                        tableLoading.style.display = 'flex';
                        resultsTable.style.opacity = '0.3';
                    });
                }
                
                // Se estiver usando HTMX, interceptar os eventos
                document.body.addEventListener('htmx:beforeRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        tableLoading.style.display = 'flex';
                        resultsTable.style.opacity = '0.3';
                    }
                });
                
                document.body.addEventListener('htmx:afterRequest', function(evt) {
                    if (evt.detail.target.id === 'results-table') {
                        setTimeout(function() {
                            tableLoading.style.display = 'none';
                            resultsTable.style.opacity = '1';
                        }, 300);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>