<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Grupos de Serviços') }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- Botão Criar - Protegido por Permissão --}}
                @can('criar_gruposervico')
                <a href="{{ route('admin.gruposervicos.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Grupo de Serviço
                </a>
                @endcan

                {{-- Botão de Ajuda --}}
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Grupos de
                                    Serviços</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode visualizar e gerenciar todos os grupos de serviços cadastrados
                                    no sistema.
                                    Use o botão "Novo Grupo de Serviço" para adicionar novos registros.
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

            {{-- Exibir mensagens de notificação --}}
            @if(session('notification'))
            <div
                class="mb-4 p-4 rounded-md {{ session('notification.type') === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                <div class="flex">
                    <div class="flex-shrink-0">
                        @if(session('notification.type') === 'success')
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        @else
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium">{{ session('notification.title') }}</h3>
                        <p class="mt-1 text-sm">{{ session('notification.message') }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tabela de Resultados --}}
            <div class="overflow-x-auto relative">
                <x-tables.table>
                    <x-tables.header>
                        <x-tables.head-cell>Código</x-tables.head-cell>
                        <x-tables.head-cell>Descrição</x-tables.head-cell>
                        <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
                        <x-tables.head-cell>Data Alteração</x-tables.head-cell>
                        {{-- Coluna Ações --}}
                        <x-tables.head-cell>Ações</x-tables.head-cell>
                    </x-tables.header>

                    <x-tables.body>
                        @forelse ($grupoServicos as $index => $grupo)
                        <x-tables.row :index="$index" data-id="{{ $grupo->id_grupo }}">
                            <x-tables.cell>{{ $grupo->id_grupo }}</x-tables.cell>
                            <x-tables.cell>{{ $grupo->descricao_grupo }}</x-tables.cell>
                            <x-tables.cell nowrap>
                                {{ $grupo->data_inclusao ? $grupo->data_inclusao->format('d/m/Y H:i') : '' }}
                            </x-tables.cell>
                            <x-tables.cell nowrap>
                                {{ $grupo->data_alteracao ? $grupo->data_alteracao->format('d/m/Y H:i') : '' }}
                            </x-tables.cell>

                            {{-- Coluna Ações --}}
                            <x-tables.cell>
                                <div class="flex items-center space-x-2">
                                    {{-- Botão Visualizar --}}
                                    <a href="{{ route('admin.gruposervicos.show', $grupo->id_grupo) }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        title="Visualizar">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>

                                    {{-- Botão Editar --}}
                                    @can('editar_gruposervico')
                                    <a href="{{ route('admin.gruposervicos.edit', $grupo->id_grupo) }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        title="Editar">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </a>
                                    @endcan

                                    {{-- Botão Excluir --}}
                                    @can('excluir_gruposervico')
                                    <form method="POST"
                                        action="{{ route('admin.gruposervicos.destroy', $grupo->id_grupo) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir esta unidade?')">
                                            <x-icons.trash class="h-3 w-3" />
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </x-tables.cell>
                        </x-tables.row>
                        @empty
                        <x-tables.empty cols="5" message="Nenhum grupo de serviço encontrado" />
                        @endforelse
                    </x-tables.body>
                </x-tables.table>

                {{-- Paginação --}}
                <div class="mt-4">
                    {{ $grupoServicos->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação de Exclusão --}}
    {{-- <div x-data="{
        showModal: false,
        grupoId: null,
        grupoNome: '',
        async excluirGrupo() {
            try {
                const response = await fetch(`{{ route('admin.gruposervicos.destroy', ':id') }}`.replace(':id', this.grupoId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Remover linha da tabela
                    const row = document.querySelector(`tr[data-id=\'\${this.grupoId}\']`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease-out';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }

                    alert(data.notification.title + ': ' + data.notification.message);
                } else {
                    throw new Error(data.notification.message);
                }

                this.showModal = false;
            } catch (error) {
                console.error('Erro ao excluir:', error);
                alert('Erro: Não foi possível excluir o grupo de serviço.');
            }
        }
    }" @confirmar-exclusao.window="showModal = true; grupoId = $event.detail.id; grupoNome = $event.detail.nome">

        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-icons.trash class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Confirmar Exclusão</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Tem certeza que deseja excluir o grupo de serviço
                                        <strong x-text="grupoNome"></strong>?
                                        <br><br>
                                        Esta ação não pode ser desfeita.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="excluirGrupo()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Excluir
                        </button>
                        <button type="button" @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    @push('scripts')
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id, nome) {
            window.dispatchEvent(new CustomEvent('confirmar-exclusao', {
                detail: { id: id, nome: nome }
            }));
        }
    </script>
    @endpush
</x-app-layout>