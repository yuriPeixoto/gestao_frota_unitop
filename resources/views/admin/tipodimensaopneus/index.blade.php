<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tipo de Dimensão Pneu') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-button-link href="{{ route('admin.tipodimensaopneus.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition-colors duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Novo Tipo
                </x-button-link>
                <x-help-icon
                title="Ajuda - Tipo de Dimensão"
                content="Nesta tela você pode visualizar todos os tipos de dimensão de pneus cadastrados. Utilize o botão 'Novo Tipo' para adicionar um novo registro. Você pode editar ou excluir tipos de dimensão existentes utilizando as ações disponíveis em cada linha da tabela."
            />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <x-bladewind::notification />

                <!-- Search Form -->
                @include('admin.tipodimensaopneus._search-form')

                <!-- Results Table with Loading -->
                <div class="mt-6 overflow-x-auto relative min-h-[400px]">
                    @include('admin.tipodimensaopneus._table')
                </div>
        </div>
    </div>
    </div>
    </div>

    <x-bladewind.modal name="delete-modal" cancel_button_label="Cancelar" ok_button_label="" type="error"
        title="Confirmar exclusão">
        Tem certeza que deseja excluir o tipo <b class="title"></b>?
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
        let idSelecionado = null;

            function editUsuario(id) {
                window.location.href = `{{ route('admin.tipodimensaopneus.edit', ':id') }}`.replace(':id', id)
            }
    </script>

    <script>
        // exclusao da Manutenção
        function destroy(id) {
            showModal('delete-autorizacao');
            autorizacaooId = id;
            domEl('.bw-delete-autorizacao .title').innerText = id;
        }

        function confirmarExclusao(id) {
            excluirOrdemServico(id);
        }

        function excluirOrdemServico(id) {
            fetch(`{{ route('admin.tipodimensaopneus.destroy', ':id') }}`.replace(':id', autorizacaooId), {
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