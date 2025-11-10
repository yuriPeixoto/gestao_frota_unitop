<x-app-layout>
    <x-slot name="header">
        @if (session('notification'))
        <x-notification :notification="session('notification')" />
        @endif
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerenciar Usuários') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.usuarios.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Usuário
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Gerenciamento de
                                    Usuários</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar todos os usuários cadastrados. Utilize o botão 'Novo
                                    Usuário' para adicionar um novo registro. Você pode editar ou excluir usuários
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
            <!-- Formulário de Busca -->
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="space-y-4 mb-6"
                hx-get="{{ route('admin.usuarios.index') }}" hx-target="#results-table" hx-select="#results-table"
                hx-trigger="change delay:500ms, search">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-forms.input name="search" label="Buscar usuário" value="{{ request('search') }}"
                            placeholder="Nome, email, CPF ou matrícula" />
                    </div>
                    <div>
                        <x-forms.smart-select name="filial_id" label="Filial" placeholder="Todas as filiais"
                            :options="$filiais ?? []" :selected="request('filial_id')" asyncSearch="false" />
                    </div>
                    <div>
                        <x-forms.select name="order_by" label="Ordenar por" :options="[
                            'name' => 'Nome',
                            'email' => 'Email',
                            'matricula' => 'Matrícula',
                            'created_at' => 'Data de criação',
                            'last_login_at' => 'Último acesso',
                        ]" :selected="request('order_by', 'name')" />
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.usuarios.index') }}"
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
                <div id="table-loading"
                    class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-10"
                    style="display: none;">
                    <x-ui.loading message="Carregando dados..." color="primary" size="lg" />
                </div>

                <!-- Actual results -->
                <div id="results-table">
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell>ID</x-tables.head-cell>
                            <x-tables.head-cell>Nome</x-tables.head-cell>
                            <x-tables.head-cell>Email</x-tables.head-cell>
                            <x-tables.head-cell>Matrícula</x-tables.head-cell>
                            <x-tables.head-cell>Filiais</x-tables.head-cell>
                            <x-tables.head-cell>Departamento</x-tables.head-cell>
                            <x-tables.head-cell>Último Acesso</x-tables.head-cell>
                            <x-tables.head-cell>Ações</x-tables.head-cell>
                        </x-tables.header>

                        <x-tables.body>
                            @forelse ($users as $index => $user)
                            <x-tables.row :index="$index">
                                <x-tables.cell>{{ $user->id }}</x-tables.cell>
                                <x-tables.cell>{{ $user->name }}</x-tables.cell>
                                <x-tables.cell>{{ $user->email }}</x-tables.cell>
                                <x-tables.cell>
                                    @if($user->matricula)
                                    <span class="font-mono text-sm">{{ $user->matricula }}</span>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </x-tables.cell>
                                <x-tables.cell>
                                    @if ($user->filiais->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($user->filiais as $filial)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $filial->id == $user->filial_id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $filial->name }}
                                            @if ($filial->id == $user->filial_id)
                                            <span class="ml-1 text-xs text-blue-600">(Principal)</span>
                                            @endif
                                        </span>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-gray-500">Não atribuída</span>
                                    @endif
                                </x-tables.cell>
                                <x-tables.cell>{{ $user->departamento->descricao_departamento ?? 'Não atribuído' }}
                                </x-tables.cell>
                                <x-tables.cell nowrap>{{ $user->last_login_at
                                    ? $user->last_login_at->format('d/m/Y
                                    H:i')
                                    : 'Nunca' }}</x-tables.cell>
                                <x-tables.cell>
                                    <div class="flex items-center space-x-2">
                                        <x-tooltip content="Visualizar" placement="bottom">
                                            <a href="{{ route('admin.usuarios.show', $user->id) }}"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <x-icons.eye class="h-3 w-3" />
                                            </a>
                                        </x-tooltip>

                                        <x-tooltip content="Editar" placement="top">
                                            <a href="{{ route('admin.usuarios.edit', $user->id) }}"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <x-icons.pencil class="h-3 w-3" />
                                            </a>
                                        </x-tooltip>
                                        <x-tooltip content="Clonar Usuário" placement="top">
                                            <a href="#" onclick="cloneUser({{ $user->id }})"
                                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <x-icons.clone-user class="h-3 w-3" />
                                            </a>
                                        </x-tooltip>

                                        {{-- @if (auth()->id() != $user->id)
                                        <button type="button"
                                            onclick="confirmarExclusao({{ $user->id }}, '{{ $user->name }}')"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <x-icons.trash class="h-3 w-3" />
                                        </button>
                                        @endif --}}
                                    </div>
                                </x-tables.cell>
                            </x-tables.row>
                            @empty
                            <x-tables.empty cols="8" message="Nenhum usuário encontrado" />
                            @endforelse
                        </x-tables.body>
                    </x-tables.table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="modal-exclusao" class="fixed inset-0 z-50 hidden overflow-y-auto overflow-x-hidden" aria-modal="true"
        role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="fecharModal()"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Confirmar exclusão</h3>
                </div>

                <div class="p-4">
                    <p class="text-gray-700">Tem certeza que deseja excluir o usuário <span id="nome-usuario"
                            class="font-semibold"></span>?</p>
                    <p class="text-sm text-gray-500 mt-2">Esta ação não pode ser desfeita.</p>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md"
                        onclick="fecharModal()">
                        Cancelar
                    </button>
                    <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md" onclick="excluirUsuario()">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @include('admin.usuarios._scripts')
    @endpush
</x-app-layout>