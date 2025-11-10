<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manutenção Imobilizados') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.tipomanutencaoimobilizados.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Tipo
                </x-button-link>
                <x-help-icon title="Ajuda - Tipo Manutenção Imobilizado"
                    content="Nesta tela você pode visualizar todas as manutenções cadastradas para imobilizados. Utilize o botão 'Novo Tipo' para adicionar um novo registro. Você pode editar ou excluir manutenções existentes utilizando as ações disponíveis em cada linha da tabela." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-bladewind::notification />

            <div class="overflow-x-auto">
                @php
                $actionIcons = [
                "icon:pencil | tip:Editar | click:editUsuario({id})",
                "icon:trash | tip:Excluir | color:red | click:destroyUsuario({id},
                '{descricao}')"
                ];

                $column_aliases = [
                'id' => 'Código',
                'descricao' => 'Descrição'
                ];

                @endphp

                <x-bladewind::table searchable="true" page_size="10" sortable="true"
                    search_placeholder="Buscar usuários..." divider="thin" paginated="true"
                    total_label="Mostrando :a - :b de :c registros" :action_icons="$actionIcons"
                    :column_aliases="$column_aliases" :data="$tipoManutencaoImobilizados" />
            </div>
        </div>
    </div>
    </div>
    </div>

    <x-bladewind.modal name="delete-modal" cancel_button_label="Cancelar" ok_button_label="" type="error"
        title="Confirmar exclusão">
        Tem certeza que deseja excluir a manutenção <b class="title"></b>?
        </br>
        Esta ação não pode ser desfeita.
        </br>
        <x-bladewind::button has_spinner="true" name="botao-delete" type="button" color="red"
            onclick="confirmDeleteUsuario()" class="mt-3 text-white">
            Excluir
        </x-bladewind::button>
    </x-bladewind.modal>

    @push('scripts')
    <script>
        let manutencaoId = null;

            function editUsuario(id) {
                window.location.href = `{{ route('admin.tipomanutencaoimobilizados.edit', ':id') }}`.replace(':id', id)
            }

            function destroyUsuario(id, nome) {
                showModal('delete-modal');
                manutencaoId = id;
                domEl('.bw-delete-modal .title').innerText = nome;
            }

            async function confirmDeleteUsuario() {

                    try {
                        showButtonSpinner('.botao-delete');

                        const response = await fetch(
                            `{{ route('admin.tipomanutencaoimobilizados.destroy', ':id') }}`.replace(':id', manutencaoId),
                            {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }
                        );

                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Error response text:', errorText);
                            throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                        }

                        const data = await response.json();

                        if (data.notification) {
                            showNotification(
                                data.notification.title,
                                data.notification.message,
                                data.notification.type
                            );

                            setTimeout(() => {
                                window.location.reload();
                            }, 300);
                        }
                    } catch (error) {
                        console.error('Full error:', error);
                        showNotification(
                            'Erro',
                            'Não foi possível excluir a manutenção',
                            'error'
                        );
                    }
                }

            @if(session('notification') && is_array(session('notification')))
                showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
            @endif
    </script>
    @endpush
</x-app-layout>