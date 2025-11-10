<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Modelos de Veículos') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.modeloveiculos.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Novo Modelo de Veículo
                </x-button-link>
                <x-help-icon
                    title="Ajuda - Gerenciamento de Modelos de Veículos"
                    content="Nesta tela você pode visualizar todos os modelos de veículos cadastrados. Utilize o botão 'Novo Modelo de Veículo' para adicionar um novo registro. Você pode editar ou excluir modelos de veículos existentes utilizando as ações disponíveis em cada linha da tabela."
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
                        "icon:eye | tip:Visualizar | color:green | click:viewModeloVeiculo({id})",
                        "icon:pencil | tip:Editar | click:editModeloVeiculo({id})",
                        "icon:trash | tip:Excluir | color:red | click:destroyModeloVeiculo({id}, '{descricao}')"
                    ];
                @endphp

                        <x-bladewind::table
                            searchable="true"
                            search_placeholder="Buscar usuários..."
                            divider="thin"
                            sortable="true"
                            paginated="true"
                            page_size="10"
                            total_label="Mostrando :a - :b de :c registros"
                            :action_icons="$actionIcons"
                            :data="$modeloveiculosData"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-bladewind.modal
        name="delete-modeloVeiculo"
        cancel_button_label="Cancelar"
        ok_button_label=""
        type="error" title="Confirmar exclusão">
        Tem certeza que deseja excluir o Modelo de veículo <b class="title"></b>?
        Esta ação não pode ser desfeita.
        <x-bladewind::button
            name="botao-delete"
            type="button"
            color="red"
            onclick="confirmDeleteModeloVeiculo()"
            class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
        <script>
            let modeloVeiculoId = null;

            function viewModeloVeiculo(id) {
                window.location.href = `{{ route('admin.modeloveiculos.show', ':id') }}`.replace(':id', id)
            }

            function editModeloVeiculo(id) {
                window.location.href = `{{ route('admin.modeloveiculos.edit', ':id') }}`.replace(':id', id)
            }

            function destroyModeloVeiculo(id, nome) {
                showModal('delete-modeloVeiculo');
                modeloVeiculoId = id;
                domEl('.bw-delete-modeloVeiculo .title').innerText = nome;
            }

            function confirmDeleteModeloVeiculo() {
                fetch(`{{ route('admin.modeloveiculos.destroy', ':id') }}`.replace(':id', modeloVeiculoId), {
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
                        'Não foi possível excluir o modelo de veículo',
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
