<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Tanque</x-tables.head-cell>
            <x-tables.head-cell>Bico 1</x-tables.head-cell>
            <x-tables.head-cell>Bico 2</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Alteração</x-tables.head-cell>
            {{-- Verificar se tem alguma ação disponível --}}
            @if (auth()->user()->hasAnyPermission(['editar_bomba', 'excluir_bomba']))
                <x-tables.head-cell>Ações</x-tables.head-cell>
            @endif
        </x-tables.header>

        <x-tables.body>
            @forelse ($bombas as $index => $bomba)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $bomba->id_bomba }}</x-tables.cell>
                    <x-tables.cell>{{ $bomba->descricao_bomba }}</x-tables.cell>
                    <x-tables.cell>{{ $bomba->filial->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $bomba->tanque->tanque ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $bomba->bomba_ctf }}</x-tables.cell>
                    <x-tables.cell>{{ $bomba->bomba_ctf_2_bico }}</x-tables.cell>
                    <x-tables.cell>
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $bomba->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $bomba->is_ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell
                        nowrap>{{ $bomba->data_inclusao ? date('d/m/Y H:i', strtotime($bomba->data_inclusao)) : '' }}</x-tables.cell>
                    <x-tables.cell
                        nowrap>{{ $bomba->data_alteracao ? date('d/m/Y H:i', strtotime($bomba->data_alteracao)) : '' }}</x-tables.cell>
                    {{-- Coluna Ações - Só aparece se usuário tem permissões --}}
                    @if (auth()->user()->hasAnyPermission(['editar_bomba', 'excluir_bomba']))
                        <x-tables.cell>
                            <div class="flex items-center space-x-2">
                                {{-- <a href="{{ route('admin.bombas.show', $bomba->id_bomba) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            title="Visualizar">
                            <x-icons.eye class="h-3 w-3" />
                        </a> --}}
                                @can('editar_bomba')
                                    <a href="{{ route('admin.bombas.edit', $bomba->id_bomba) }}"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        title="Editar">
                                        <x-icons.pencil class="h-3 w-3" />
                                    </a>
                                @endcan

                                @can('editar_bomba')
                                    <button type="button" onclick="toggleStatus({{ $bomba->id_bomba }})"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white {{ $bomba->is_ativo ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        title="{{ $bomba->is_ativo ? 'Desativar bomba' : 'Ativar bomba' }}">
                                        @if ($bomba->is_ativo)
                                            <x-icons.ban class="h-3 w-3" />
                                        @else
                                            <x-icons.check class="h-3 w-3" />
                                        @endif
                                    </button>
                                @endcan

                                @can('excluir_bomba')
                                    <button type="button" onclick="confirmarExclusao({{ $bomba->id_bomba }})"
                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        title="Excluir bomba">
                                        <x-icons.trash class="h-3 w-3" />
                                    </button>
                                @endcan
                            </div>
                        </x-tables.cell>
                    @endif
                </x-tables.row>
            @empty
                @php
                    $totalCols = 9; // Colunas básicas
                    if (
                        auth()
                            ->user()
                            ->hasAnyPermission(['editar_bomba', 'excluir_bomba'])
                    ) {
                        $totalCols = 10; // + coluna ações
                    }
                @endphp
                <x-tables.empty cols="{{ $totalCols }}" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $bombas->links() }}
    </div>
</div>

@push('scripts')
    <script>
        @can('editar_bomba')
            function toggleStatus(id) {
                if (confirm('Tem certeza que deseja alterar o status desta bomba?')) {
                    // Mostrar indicador de carregamento
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.className =
                        'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
                    loadingOverlay.id = 'loading-overlay';
                    loadingOverlay.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <div class="flex items-center space-x-3">
                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processando...</span>
                    </div>
                </div>
            `;
                    document.body.appendChild(loadingOverlay);

                    fetch(`/admin/bombas/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Remover indicador de carregamento
                            document.getElementById('loading-overlay').remove();

                            if (data.success) {
                                // Mostrar notificação de sucesso usando SweetAlert ou outro
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: data.notification.title,
                                        text: data.notification.message,
                                        icon: data.notification.type,
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert(data.notification.message);
                                }

                                // Recarregar a tabela via HTMX ou recarregar a página
                                if (typeof htmx !== 'undefined') {
                                    htmx.trigger('#results-table', 'refresh');
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                // Mostrar erro
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: data.notification.title || 'Erro',
                                        text: data.notification.message ||
                                            'Ocorreu um erro ao alterar o status da bomba',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert(data.notification.message || 'Erro ao alterar status da bomba');
                                }
                            }
                        })
                        .catch(error => {
                            // Remover indicador de carregamento
                            document.getElementById('loading-overlay').remove();

                            console.error('Error:', error);

                            // Mostrar erro
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Erro',
                                    text: 'Ocorreu um erro ao alterar o status da bomba',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Erro ao alterar status da bomba');
                            }
                        });
                }
            }
        @endcan

        @can('excluir_bomba')
            function confirmarExclusao(id) {
                if (confirm('Tem certeza que deseja excluir esta bomba?')) {
                    excluirBomba(id);
                }
            }

            function excluirBomba(id) {
                fetch(`/admin/bombas/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Bomba excluída com sucesso');
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erro ao excluir bomba');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao excluir bomba');
                    });
            }
        @endcan
    </script>
@endpush
