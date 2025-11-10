@extends('layouts.admin')

@section('title', 'Gerenciar Cargos e Permissões')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-900">
                🏢 Gerenciar Cargos e Permissões
            </h1>
            <p class="text-gray-600">
                Gerencie os cargos da empresa e suas permissões. As permissões são sincronizadas automaticamente com todos
                os usuários do cargo.
            </p>
        </div>

        <!-- Alertas -->
        <div id="alerts"></div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Lista de Cargos -->
            <div class="lg:col-span-1">
                <div class="rounded-lg bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">📋 Cargos</h2>

                    <div class="space-y-2">
                        @foreach ($cargos as $cargo)
                            <div class="cargo-item cursor-pointer rounded-lg border p-3 transition-colors hover:bg-blue-50"
                                data-cargo-id="{{ $cargo->id }}" onclick="selectCargo({{ $cargo->id }})">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $cargo->descricao_tipo }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $cargo->permissions->count() }} permissões
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                            {{ $cargo->users->count() }} usuários
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Área Principal -->
            <div class="lg:col-span-2">
                <!-- Templates -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">🎯 Templates Rápidos</h2>
                    <p class="mb-4 text-gray-600">Aplique um conjunto predefinido de permissões ao cargo selecionado:</p>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-5">
                        <button onclick="applyTemplate('admin')"
                            class="template-btn rounded-lg bg-red-100 px-4 py-2 text-red-800 transition-colors hover:bg-red-200"
                            disabled>
                            👑 Admin
                        </button>
                        <button onclick="applyTemplate('compras')"
                            class="template-btn rounded-lg bg-green-100 px-4 py-2 text-green-800 transition-colors hover:bg-green-200"
                            disabled>
                            🛒 Compras
                        </button>
                        <button onclick="applyTemplate('solicitacoes')"
                            class="template-btn rounded-lg bg-blue-100 px-4 py-2 text-blue-800 transition-colors hover:bg-blue-200"
                            disabled>
                            📝 Solicitações
                        </button>
                        <button onclick="applyTemplate('aprovador')"
                            class="template-btn rounded-lg bg-purple-100 px-4 py-2 text-purple-800 transition-colors hover:bg-purple-200"
                            disabled>
                            ✅ Aprovador
                        </button>
                        <button onclick="applyTemplate('consulta')"
                            class="template-btn rounded-lg bg-gray-100 px-4 py-2 text-gray-800 transition-colors hover:bg-gray-200"
                            disabled>
                            👁️ Consulta
                        </button>
                    </div>
                </div>

                <!-- Permissões do Cargo Selecionado -->
                <div id="cargo-permissions" class="hidden rounded-lg bg-white p-6 shadow-md">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">🔐 Permissões do Cargo</h2>
                        <button onclick="saveCargoPermissions()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                            💾 Salvar e Sincronizar
                        </button>
                    </div>

                    <div id="permissions-content">
                        <!-- Permissões serão carregadas aqui -->
                    </div>
                </div>

                <!-- Usuários do Cargo -->
                <div id="cargo-users" class="mt-6 hidden rounded-lg bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">👥 Usuários deste Cargo</h2>
                    <div id="users-content">
                        <!-- Usuários serão carregados aqui -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Atribuir Usuário -->
        <div id="user-assignment-modal" class="fixed inset-0 z-50 hidden bg-gray-600 bg-opacity-50">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-md rounded-lg bg-white p-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Atribuir Usuário ao Cargo</h3>

                    <form id="user-assignment-form">
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Usuário</label>
                            <select name="user_id" class="w-full rounded-md border border-gray-300 px-3 py-2" required>
                                <option value="">Selecione um usuário...</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeUserModal()"
                                class="px-4 py-2 text-gray-600 transition-colors hover:text-gray-800">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                                Atribuir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let selectedCargoId = null;
            let allPermissions = @json($permissions);

            function selectCargo(cargoId) {
                selectedCargoId = cargoId;

                // Marcar cargo como selecionado
                document.querySelectorAll('.cargo-item').forEach(item => {
                    item.classList.remove('bg-blue-100', 'border-blue-300');
                    item.classList.add('border-gray-200');
                });

                const selectedItem = document.querySelector(`[data-cargo-id="${cargoId}"]`);
                selectedItem.classList.add('bg-blue-100', 'border-blue-300');
                selectedItem.classList.remove('border-gray-200');

                // Habilitar botões de template
                document.querySelectorAll('.template-btn').forEach(btn => {
                    btn.disabled = false;
                });

                // Carregar permissões do cargo
                loadCargoPermissions(cargoId);
                loadCargoUsers(cargoId);

                // Mostrar seções
                document.getElementById('cargo-permissions').classList.remove('hidden');
                document.getElementById('cargo-users').classList.remove('hidden');
            }

            function loadCargoPermissions(cargoId) {
                fetch(`/admin/configuracoes/cargos-permissoes/cargo/${cargoId}/permissions`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayPermissions(data.permissions);
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao carregar permissões do cargo');
                    });
            }

            function loadCargoUsers(cargoId) {
                fetch(`/admin/configuracoes/cargos-permissoes/cargo/${cargoId}/users`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayUsers(data.users);
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao carregar usuários do cargo');
                    });
            }

            function displayPermissions(selectedPermissions) {
                let html = '';

                Object.entries(allPermissions).forEach(([group, permissions]) => {
                    html += `
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 capitalize">${group}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        `;

                    permissions.forEach(permission => {
                        const isChecked = selectedPermissions.includes(permission.id);
                        html += `
                <label class="flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox"
                           name="permissions[]"
                           value="${permission.id}"
                           ${isChecked ? 'checked' : ''}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">${permission.name}</span>
                </label>
            `;
                    });

                    html += `
                </div>
            </div>
        `;
                });

                document.getElementById('permissions-content').innerHTML = html;
            }

            function displayUsers(users) {
                let html = '';

                if (users.length === 0) {
                    html = `
            <div class="text-center py-8">
                <p class="text-gray-500 mb-4">Nenhum usuário atribuído a este cargo</p>
                <button onclick="openUserModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ➕ Atribuir Usuário
                </button>
            </div>
        `;
                } else {
                    html = `
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm text-gray-600">${users.length} usuário(s) atribuído(s)</span>
                <button onclick="openUserModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                    ➕ Atribuir Usuário
                </button>
            </div>
            <div class="space-y-2">
        `;

                    users.forEach(user => {
                        html += `
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-900">${user.name}</span>
                        <span class="text-sm text-gray-500 ml-2">(${user.permissions.length} permissões sincronizadas)</span>
                    </div>
                    <button onclick="removeUserFromCargo(${user.id})"
                            class="text-red-600 hover:text-red-800 text-sm transition-colors">
                        🗑️ Remover
                    </button>
                </div>
            `;
                    });

                    html += `</div>`;
                }

                document.getElementById('users-content').innerHTML = html;
            }

            function saveCargoPermissions() {
                if (!selectedCargoId) {
                    showAlert('error', 'Selecione um cargo primeiro');
                    return;
                }

                const formData = new FormData();
                const checkboxes = document.querySelectorAll('input[name="permissions[]"]:checked');
                const permissions = Array.from(checkboxes).map(cb => cb.value);

                formData.append('cargo_id', selectedCargoId);
                formData.append('permissions', JSON.stringify(permissions));

                fetch('/admin/configuracoes/cargos-permissoes/sync', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cargo_id: selectedCargoId,
                            permissions: permissions
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            // Recarregar usuários para mostrar permissões atualizadas
                            loadCargoUsers(selectedCargoId);
                            // Atualizar contador de permissões na lista de cargos
                            location.reload();
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao salvar permissões');
                    });
            }

            function applyTemplate(template) {
                if (!selectedCargoId) {
                    showAlert('error', 'Selecione um cargo primeiro');
                    return;
                }

                if (!confirm(
                        `Tem certeza que deseja aplicar o template "${template}" ao cargo selecionado? Isso substituirá todas as permissões atuais.`
                        )) {
                    return;
                }

                fetch('/admin/configuracoes/cargos-permissoes/apply-template', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cargo_id: selectedCargoId,
                            template: template
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            // Recarregar permissões
                            loadCargoPermissions(selectedCargoId);
                            loadCargoUsers(selectedCargoId);
                            // Atualizar contador de permissões na lista de cargos
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao aplicar template');
                    });
            }

            function openUserModal() {
                document.getElementById('user-assignment-modal').classList.remove('hidden');
            }

            function closeUserModal() {
                document.getElementById('user-assignment-modal').classList.add('hidden');
                document.getElementById('user-assignment-form').reset();
            }

            document.getElementById('user-assignment-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const userId = formData.get('user_id');

                fetch('/admin/configuracoes/cargos-permissoes/assign-user', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            cargo_id: selectedCargoId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', data.message);
                            closeUserModal();
                            loadCargoUsers(selectedCargoId);
                            // Atualizar contador de usuários na lista de cargos
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao atribuir usuário');
                    });
            });

            function removeUserFromCargo(userId) {
                if (!confirm('Tem certeza que deseja remover este usuário do cargo?')) {
                    return;
                }

                fetch('/admin/configuracoes/cargos-permissoes/assign-user', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            cargo_id: null // Remove o cargo do usuário
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Usuário removido do cargo com sucesso');
                            loadCargoUsers(selectedCargoId);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showAlert('error', 'Erro ao remover usuário');
                    });
            }

            function showAlert(type, message) {
                const alertsContainer = document.getElementById('alerts');
                const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' :
                    'bg-red-100 border-red-400 text-red-700';

                const alertHtml = `
        <div class="${alertClass} px-4 py-3 rounded border mb-4" role="alert">
            <span class="block sm:inline">${message}</span>
        </div>
    `;

                alertsContainer.innerHTML = alertHtml;

                // Remover alerta após 5 segundos
                setTimeout(() => {
                    alertsContainer.innerHTML = '';
                }, 5000);
            }
        </script>
    @endpush
@endsection
