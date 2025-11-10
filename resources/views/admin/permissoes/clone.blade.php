<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.permissoes.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🔄 Clonar Permissões de Usuário
                </h2>
            </div>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Clonar Permissões"
                    content="Esta tela permite copiar todas as permissões e roles de um usuário para um ou mais usuários de destino. Útil para padronizar permissões de equipes." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form action="{{ route('admin.permissoes.clone.execute') }}" method="POST" id="clone-form">
                @csrf

                <div class="space-y-6">
                    {{-- Alerta Informativo --}}
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Como funciona:</strong> Selecione um usuário de origem e um ou mais usuários de destino.
                                    Todas as permissões diretas e roles do usuário de origem serão copiadas para os usuários de destino.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Usuário de Origem --}}
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                            Usuário de Origem (Copiar DE)
                        </h3>

                        <div>
                            <label for="source_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione o usuário cujas permissões serão copiadas
                            </label>
                            <select name="source_user_id" id="source_user_id" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Selecione um usuário...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Preview de Permissões do Usuário de Origem --}}
                        <div id="source-preview" class="hidden mt-4 p-4 bg-white rounded border border-gray-200">
                            <h4 class="font-semibold text-sm text-gray-700 mb-2">Permissões que serão copiadas:</h4>
                            <div id="source-permissions-list" class="text-sm text-gray-600">
                                <div class="flex items-center justify-center py-4">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="ml-2">Carregando...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Usuários de Destino --}}
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-users mr-2 text-green-600"></i>
                            Usuários de Destino (Copiar PARA)
                        </h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selecione os usuários que receberão as permissões
                            </label>

                            {{-- Barra de Busca --}}
                            <div class="mb-4">
                                <div class="relative">
                                    <input type="text" id="user-search" placeholder="Buscar usuário..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 pl-10">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>

                            {{-- Ações em Massa --}}
                            <div class="flex gap-2 mb-4">
                                <button type="button" onclick="selectAllUsers()" class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                    <i class="fas fa-check-square mr-1"></i>
                                    Selecionar Todos
                                </button>
                                <button type="button" onclick="deselectAllUsers()" class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                    <i class="fas fa-square mr-1"></i>
                                    Desmarcar Todos
                                </button>
                            </div>

                            {{-- Lista de Usuários com Checkboxes --}}
                            <div id="users-list" class="max-h-96 overflow-y-auto border border-gray-300 rounded-md bg-white">
                                @foreach($users as $user)
                                    <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 user-item">
                                        <input type="checkbox" name="target_user_ids[]" value="{{ $user->id }}"
                                            class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500 target-user-checkbox"
                                            data-username="{{ strtolower($user->name) }}">
                                        <span class="ml-3 text-sm text-gray-700 user-name">{{ $user->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-2 text-sm text-gray-600">
                                <span id="selected-count">0</span> usuário(s) selecionado(s)
                            </div>
                        </div>
                    </div>

                    {{-- Alerta de Confirmação --}}
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Atenção:</strong> Esta ação irá <strong>substituir</strong> todas as permissões atuais dos usuários de destino
                                    pelas permissões do usuário de origem. Esta operação não pode ser desfeita automaticamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.permissoes.index') }}" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar
                        </a>

                        <button type="submit" id="submit-btn"
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-copy mr-2"></i>
                            Clonar Permissões
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sourceUserSelect = document.getElementById('source_user_id');
            const sourcePreview = document.getElementById('source-preview');
            const sourcePermissionsList = document.getElementById('source-permissions-list');
            const targetCheckboxes = document.querySelectorAll('.target-user-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const submitBtn = document.getElementById('submit-btn');
            const userSearch = document.getElementById('user-search');

            // Carregar preview de permissões do usuário de origem
            sourceUserSelect.addEventListener('change', async function() {
                const userId = this.value;

                if (!userId) {
                    sourcePreview.classList.add('hidden');
                    return;
                }

                sourcePreview.classList.remove('hidden');
                sourcePermissionsList.innerHTML = '<div class="flex items-center justify-center py-4"><svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="ml-2">Carregando...</span></div>';

                try {
                    const response = await fetch(`/admin/permissoes/get-permissions/user/${userId}`);
                    const data = await response.json();

                    if (data.permissions && data.permissions.length > 0) {
                        const permissionsHtml = data.permissions.map(p =>
                            `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">${p}</span>`
                        ).join('');
                        sourcePermissionsList.innerHTML = `
                            <div class="mb-2"><strong>${data.permissions.length}</strong> permissão(ões) direta(s):</div>
                            <div class="flex flex-wrap">${permissionsHtml}</div>
                        `;
                    } else {
                        sourcePermissionsList.innerHTML = '<p class="text-gray-500 italic">Nenhuma permissão direta encontrada para este usuário.</p>';
                    }
                } catch (error) {
                    sourcePermissionsList.innerHTML = '<p class="text-red-600">Erro ao carregar permissões.</p>';
                }

                // Desabilitar checkbox do usuário de origem na lista de destino
                disableSourceUserInTargets();
            });

            // Desabilitar usuário de origem na lista de destino
            function disableSourceUserInTargets() {
                const sourceUserId = sourceUserSelect.value;
                targetCheckboxes.forEach(checkbox => {
                    if (checkbox.value === sourceUserId) {
                        checkbox.disabled = true;
                        checkbox.checked = false;
                        checkbox.closest('.user-item').classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        checkbox.disabled = false;
                        checkbox.closest('.user-item').classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                });
                updateSelectedCount();
            }

            // Atualizar contador de selecionados
            function updateSelectedCount() {
                const count = Array.from(targetCheckboxes).filter(cb => cb.checked && !cb.disabled).length;
                selectedCount.textContent = count;

                // Habilitar/desabilitar botão de submit
                const hasSource = sourceUserSelect.value !== '';
                submitBtn.disabled = !hasSource || count === 0;
            }

            targetCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Busca de usuários
            userSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const userItems = document.querySelectorAll('.user-item');

                userItems.forEach(item => {
                    const checkbox = item.querySelector('.target-user-checkbox');
                    const username = checkbox.dataset.username;

                    if (username.includes(searchTerm)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Validação antes de submeter
            document.getElementById('clone-form').addEventListener('submit', function(e) {
                const sourceUserId = sourceUserSelect.value;
                const selectedTargets = Array.from(targetCheckboxes).filter(cb => cb.checked && !cb.disabled);

                if (!sourceUserId) {
                    e.preventDefault();
                    alert('Por favor, selecione um usuário de origem.');
                    return;
                }

                if (selectedTargets.length === 0) {
                    e.preventDefault();
                    alert('Por favor, selecione pelo menos um usuário de destino.');
                    return;
                }

                const confirmed = confirm(
                    `Você está prestes a clonar as permissões para ${selectedTargets.length} usuário(s).\n\n` +
                    `Isso irá SUBSTITUIR todas as permissões atuais desses usuários.\n\n` +
                    `Deseja continuar?`
                );

                if (!confirmed) {
                    e.preventDefault();
                }
            });

            // Inicializar estado do botão
            updateSelectedCount();
        });

        function selectAllUsers() {
            const checkboxes = document.querySelectorAll('.target-user-checkbox:not([disabled])');
            checkboxes.forEach(cb => cb.checked = true);
            document.getElementById('selected-count').textContent = checkboxes.length;
            document.getElementById('submit-btn').disabled = document.getElementById('source_user_id').value === '' || checkboxes.length === 0;
        }

        function deselectAllUsers() {
            const checkboxes = document.querySelectorAll('.target-user-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            document.getElementById('selected-count').textContent = '0';
            document.getElementById('submit-btn').disabled = true;
        }
    </script>
    @endpush
</x-app-layout>
