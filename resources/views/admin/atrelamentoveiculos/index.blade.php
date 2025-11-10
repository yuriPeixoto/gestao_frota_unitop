<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Atrelamento de Veículos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link  href="{{ route('admin.atrelamentoveiculos.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Atrelar Veículo
                </x-button-link>
                <x-help-icon title="Ajuda - Gerenciamento de Atrelados"
                    content="Nesta tela você pode visualizar todos veículos atrelados. Utilize o botão 'Atrelar Veículo' para adicionar um novo registro. Você pode editar ou excluir veiculos atrelados existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-bladewind::notification />
            <div class="overflow-x-auto">
                <div class="mb-4 flex gap-4">
                    <x-bladewind::input
                        id="search-input"
                        name="search"
                        placeholder="Buscar..."
                        class="flex-1"
                        value="{{ $searchTerm ?? '' }}"
                    />
                    <x-bladewind::button
                        onclick="executeSearch()"
                        class="px-6 h-12"
                        size="small">
                        Buscar
                    </x-bladewind::button>
                    @if(request()->has('search'))
                        <x-bladewind::button
                            onclick="clearSearch()"
                            class="px-6 h-12"
                            size="small"
                            color="red">
                            Limpar
                        </x-bladewind::button>
                    @endif
                </div>

                {{-- Estatísticas de sucesso --}}
                @if (request()->has('search'))
                    <div class="mb-4 text-sm text-gray-600">
                        Encontrados {{ $totalRegistros }} resultado(s) para "{{ $searchTerm }}"
                    </div>
                @endif

                {{-- Componente de Tabela --}}
                <x-bladewind::table
                    searchable="false"
                    :sortable="true"
                    divider="thin"
                    actions_title="Ações"
                    :action_icons="$actionIcons"
                    :data="$atrelamentoVeiculoData"
                    :column_aliases="$column_aliases"
                />

                {{-- Paginação padrão Laravel --}}
                <div class="mt-4">
                    {{ $atrelamentoVeiculo->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-bladewind.modal name="delete-atrelamento" cancel_button_label="Cancelar" ok_button_label="" type="error"
        title="Confirmar exclusão">
        Tem certeza que deseja excluir o Atrelamento <b class="title"></b>?
        Esta ação não pode ser desfeita.
        <x-bladewind::button name="botao-delete" type="button" color="red" has_spinner="true"
            onclick="confirmDelete()" class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
        <script>
            let idSelecionado = null;

                function showAtrelamento(id) {
                    window.location.href = `{{ route('admin.atrelamentoveiculos.show', ':id') }}`.replace(':id', id);
                }

                function editAtrelamento(id) {
                    window.location.href = `{{ route('admin.atrelamentoveiculos.edit', ':id') }}`.replace(':id', id);
                }

                function destroyAtrelamento(id, nome) {
                    showModal('delete-atrelamento');
                    idSelecionado = id;
                    domEl('.bw-delete-atrelamento .title').innerText = nome;
                }

                function confirmDelete() {
                    // showButtonSpinner('.botao-delete');
                    
                    fetch(`{{ route('admin.atrelamentoveiculos.destroy', ':id') }}`.replace(':id', idSelecionado), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        if (!response.ok) {
                            return response.text().then(errorText => {
                                console.error('Error response text:', errorText);
                                throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                            });
                        }
                        return response.json();
                    }).then(data => {
                        if (data.notification) {
                            showNotification(
                                data.notification.title,
                                data.notification.message,
                                data.notification.type
                            );

                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    }).catch(error => {
                        console.error('Full error:', error);

                        showNotification(
                            'Erro',
                            error.message,
                            'error'
                        );
                    });
                }

                @if(session('notification') && is_array(session('notification')))
                    showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
                @endif

                function executeSearch() {
                const searchTerm = document.getElementById('search-input').value;
                const currentUrl = new URL(window.location.href);

                if (searchTerm) {
                    currentUrl.searchParams.set('search', searchTerm);
                } else {
                    currentUrl.searchParams.delete('search');
                }

                window.location.href = currentUrl.toString();
            }

            function clearSearch() {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('search');
                window.location.href = currentUrl.toString();
            }

            document.getElementById('search-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executeSearch();
                }
            });
        </script>
    @endpush
</x-app-layout>

                    