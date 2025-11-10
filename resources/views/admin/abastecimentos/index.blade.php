<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Abastecimentos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.abastecimentos.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Novo Abastecimento
                </x-button-link>
                <x-help-icon
                    title="Ajuda - Gerenciamento de Abastecimentos"
                    content="Nesta tela você pode visualizar todos os abastecimentos cadastrados. Utilize o botão 'Novo Abastecimentos' para adicionar um novo registro. Você pode editar ou excluir abastecimento existentes utilizando as ações disponíveis em cada linha da tabela."
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
                         "icon:eye | tip:Visualizar | color:blue | click:viewAbastecimentos({id})",
                         "icon:pencil | tip:Editar | click:editAbastecimentos({id})",
                         "icon:trash | tip:Excluir | color:red | click:destroyAbastecimentos({id})"
                     ];

                    $column_aliases = [
                                        'idabastecimento'   => 'Cód. Abastecimento',
                                        'placa'             => 'Placa',                                        
                                        'datainclusao'      => 'Data Inclusão',
                                        'dataabastecimento' => 'Data Abastecimento',
                                        'numeronotafiscal'  => 'N° Nota Fiscal',
                                        'fornecedor'        => 'Fornecedor',
                                        'departamento'      => 'Departamento',
                                        'tipoequipamento'   => 'Equipamento'
                                    ];
                @endphp

                        <x-bladewind::table
                            searchable="true"
                            search_placeholder="Buscar..."
                            divider="thin"
                            sortable="true"
                            paginated="true"
                            page_size="10"
                            total_label="Mostrando :a - :b de :c registros"
                            :column_aliases="$column_aliases"
                            :action_icons="$actionIcons"
                            :data="$abastecimentos"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-bladewind.modal
        name="delete-sinistro"
        cancel_button_label="Cancelar"
        ok_button_label=""
        type="error" title="Confirmar exclusão">
        Tem certeza que deseja excluir o Sinistro selecionado <b class="title"></b>?
        Esta ação não pode ser desfeita.
        <x-bladewind::button
            name="botao-delete"
            type="button"
            color="red"
            onclick="confirmDeleteSinistro()"
            class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
        <script>
            let sinistroID = null;

            function viewAbastecimentos(id) {
                window.location.href = `{{ route('admin.abastecimentos.show', ':id') }}`.replace(':id', id)
            }

            function editAbastecimentos(id) {
                window.location.href = `{{ route('admin.abastecimentos.edit', ':id') }}`.replace(':id', id)
            }

            function destroyAbastecimentos(id) {
                showModal('delete-sinistro');
                sinistroID = id;
                domEl('.bw-delete-sinistro .title').innerText = id;
            }

            function editSinistro(id) {
                window.location.href = `{{ route('admin.sinistros.edit', ':id') }}`.replace(':id', id)
            }

            function destroySinistro(id) {
                showModal('delete-sinistro');
                sinistroID = id;
                domEl('.bw-delete-sinistro .title').innerText = id;
            }

            function confirmDeleteSinistro() {
                fetch(`{{ route('admin.abastecimentos.destroy', ':id') }}`.replace(':id', sinistroID), {
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
        </script>
    @endpush
</x-app-layout>
