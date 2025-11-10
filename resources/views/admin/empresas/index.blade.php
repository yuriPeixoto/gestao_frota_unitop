<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerenciar Empresas') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.empresas.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nova Empresa
                </x-button-link>
                <x-help-icon title="Ajuda - Gerenciamento de Empresas"
                    content="Nesta tela você pode visualizar todas as empresas e filiais cadastradas. Utilize o botão 'Nova Empresa' para adicionar um novo registro. Você pode editar ou excluir empresas existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-bladewind::notification />

            <div class="overflow-x-auto">
                @php
                $actionIcons = [
                "icon:eye | tip:Visualizar | color:green | click:viewEmpresa({id})",
                "icon:pencil | tip:Editar | click:editEmpresa({id})",
                "icon:trash | tip:Excluir | color:red | click:destroyEmpresa({id}, '{razaosocial}')"
                ];
                @endphp

                <x-bladewind::table searchable="true" search_placeholder="Buscar empresas..." divider="thin"
                    sortable="true" paginated="true" page_size="10" total_label="Mostrando :a - :b de :c registros"
                    :action_icons="$actionIcons" :data="$empresasData" />
            </div>
        </div>
    </div>
    </div>
    </div>

    <x-bladewind.modal name="delete-empresa" cancel_button_label="Cancelar" ok_button_label="" type="error"
        title="Confirmar exclusão">
        Tem certeza que deseja excluir a empresa <b class="title"></b>?
        Esta ação não pode ser desfeita.
        <x-bladewind::button name="botao-delete" type="button" color="red" has_spinner="true"
            onclick="confirmDeleteEmpresa()" class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
    <script>
        let empresaId = null;

            function viewEmpresa(id) {
                window.location.href = `{{ route('admin.empresas.show', ':id') }}`.replace(':id', id)
            }

            function editEmpresa(id) {
                window.location.href = `{{ route('admin.empresas.edit', ':id') }}`.replace(':id', id)
            }

            function destroyEmpresa(id, nome) {
                showModal('delete-empresa');
                empresaId = id;
                domEl('.bw-delete-empresa .title').innerText = nome;
            }

            function confirmDeleteEmpresa() {
                showButtonSpinner('.botao-delete');
                
                fetch(`{{ route('admin.empresas.destroy', ':id') }}`.replace(':id', empresaId), {
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
                        'Não foi possível excluir a empresa',
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