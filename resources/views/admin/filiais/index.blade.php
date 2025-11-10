<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerenciar Filiais') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.filiais.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nova Filial
                </x-button-link>
                <x-help-icon
                    title="Ajuda - Gerenciamento de Filiais"
                    content="Nesta tela você pode visualizar todas as filiais cadastradas. Utilize o botão 'Nova Filial' para adicionar um novo registro. Você pode editar ou excluir filiais existentes utilizando as ações disponíveis em cada linha da tabela."
                />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-bladewind::notification />

            <div class="overflow-x-auto">
                @php
                    $actionIcons = [
                        "icon:pencil | tip:Editar | click:editFilial({id})",
                        "icon:trash | tip:Excluir | color:red | click:destroyFilial({id}, '{nome}')"
                    ];
                @endphp

                        <x-bladewind::table
                            searchable="true"
                            search_placeholder="Buscar filiais..."
                            divider="thin"
                            sortable="true"
                            paginated="true"
                            page_size="10"
                            total_label="Mostrando :a - :b de :c registros"
                            :action_icons="$actionIcons"
                            :data="$filiaisData"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-bladewind.modal
        name="delete-filial"
        cancel_button_label="Cancelar"
        ok_button_label=""
        type="error" title="Confirmar exclusão">
        Tem certeza que deseja excluir a filial <b class="title"></b>?
        Esta ação não pode ser desfeita.
        <x-bladewind::button
            name="botao-delete"
            type="button"
            color="red"
            onclick="confirmDeleteFilial()"
            class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
        <script>
            let filialId = null;

            function editFilial(id) {
                window.location.href = `{{ route('admin.filiais.edit', ':id') }}`.replace(':id', id)
            }

            function destroyFilial(id, nome) {
                showModal('delete-filial');
                filialId = id;
                domEl('.bw-delete-filial .title').innerText = nome;
            }

            function confirmDeleteFilial() {
                fetch(`{{ route('admin.filiais.destroy', ':id') }}`.replace(':id', filialId), {
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
                        'Não foi possível excluir a filial',
                        'error'
                    );
                });
            }

            @if(session('notification') && is_array(session('notification')))
                showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
            @endif
        </script>
    @endpush
</x-app-layout>
