<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerenciamento de Permissões') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.permissoes.clone') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-copy mr-2"></i>
                    Clonar Permissões
                </a>
                <x-help-icon title="Ajuda - Permissões"
                    content="Esta tela permite gerenciar permissões para usuários, cargos, departamentos e filiais. Selecione o tipo e o alvo para atribuir as permissões necessárias." />
            </div>
        </div>
    </x-slot>

    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200"">
            <form action="{{ route('admin.permissoes.assign') }}" method="POST">
                @csrf

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- Improved selectors: side by side -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Escolher Tipo de Permissão</label>
                            <select name="type" id="type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Selecionar...</option>
                                <option value="user">Usuário</option>
                                <option value="role">Cargo (TipoPessoal)</option>
                                <option value="group">Grupo (Role)</option>
                                <option value="department">Departamento</option>
                                <option value="branch">Filial</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selecionar</label>
                            <select name="target_id" id="target_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <!-- Carregar via JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Reorganized Permissions by Model Groups -->
                    <div class="mb-6 mt-6">
                        <div
                            class="flex flex-col space-y-3 md:space-y-0 md:flex-row md:items-center md:justify-between mb-4">
                            <label class="block text-lg font-medium text-gray-700">Permissões</label>

                            <!-- Campo de busca -->
                            <div class="relative w-full md:w-1/3">
                                <input type="text" id="permission-search" placeholder="Buscar módulo..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex space-x-4 mt-6">
                                <button type="button" id="select-all-permissions"
                                    class="text-sm text-indigo-600 hover:text-indigo-800">
                                    Selecionar Todas
                                </button>
                                <button type="button" id="deselect-all-permissions"
                                    class="text-sm text-gray-600 hover:text-gray-800">
                                    Limpar Todas
                                </button>
                            </div>
                        </div>

                        <!-- Permissions grouped by model/resource type -->
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-6 permissions-container">
                            @foreach ($permissions->sortKeys() as $model => $modelPermissions)
                                <div class="permission-group bg-gray-50 p-4 rounded-lg h-full">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 capitalize">
                                            {{ str_replace('_', ' ', $model) }}</h3>
                                        <div class="flex space-x-3">
                                            <button type="button"
                                                class="select-group text-sm text-indigo-600 hover:text-indigo-800"
                                                data-group="{{ $model }}">
                                                Selecionar Grupo
                                            </button>
                                            <button type="button"
                                                class="deselect-group text-sm text-gray-600 hover:text-gray-800"
                                                data-group="{{ $model }}">
                                                Limpar Grupo
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 model-permissions"
                                        data-model="{{ $model }}">
                                        @php
                                            // Definir ordem desejada das ações
                                            $actionOrder = [
                                                'ver' => 1,
                                                'listar' => 2,
                                                'criar' => 3,
                                                'editar' => 4,
                                                'excluir' => 5,
                                                'deletar' => 6,
                                            ];

                                            // Usar o sortBy do Laravel para ordenar as permissões por ação
                                            $sortedPermissions = $modelPermissions->sortBy(function ($permission) use (
                                                $actionOrder,
                                            ) {
                                                $action = explode('_', $permission->name)[0];
                                                return $actionOrder[$action] ?? 999;
                                            });
                                        @endphp

                                        @foreach ($sortedPermissions as $permission)
                                            <div class="flex items-center permission-item">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded permission-checkbox"
                                                    data-model="{{ $model }}">
                                                <label for="perm_{{ $permission->id }}"
                                                    class="ml-2 block text-sm text-gray-900 capitalize">
                                                    {{ explode('_', $permission->name)[0] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botão flutuante de salvar -->
                    <div class="fixed bottom-6 right-6 z-50">
                        <button type="submit"
                            class="inline-flex justify-center py-3 px-6 border border-transparent shadow-lg text-sm font-medium rounded-full text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Salvar as Permissões
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Type selector change handler
                const typeSelect = document.getElementById('type');
                const targetSelect = document.getElementById('target_id');
                const permissionSearch = document.getElementById('permission-search');
                const permissionGroups = document.querySelectorAll('.permission-group');
                const submitButton = document.querySelector('button[type="submit"]');
                const form = document.querySelector('form');

                // Log do que está sendo enviado no submit
                form.addEventListener('submit', function(e) {
                    const checkedPermissions = Array.from(document.querySelectorAll(
                            '.permission-checkbox:checked'))
                        .map(cb => cb.value);

                    console.log('=== SUBMIT DO FORMULÁRIO ===');
                    console.log('Tipo:', typeSelect.value);
                    console.log('Target ID:', targetSelect.value);
                    console.log('Total de permissões marcadas:', checkedPermissions.length);
                    console.log('Primeiras 20 permissões:', checkedPermissions.slice(0, 20));
                    console.log('Últimas 20 permissões:', checkedPermissions.slice(-20));

                    // ALERTA se a quantidade for diferente de 1728
                    if (checkedPermissions.length !== 1728) {
                        console.warn(
                            `⚠️ ATENÇÃO: Esperava 1728 permissões, mas só ${checkedPermissions.length} estão marcadas!`
                        );
                    }

                    // Não prevenir o submit, deixar enviar
                    // e.preventDefault(); 
                });

                // Função para filtrar os grupos de permissão
                function filterPermissionGroups(searchTerm) {
                    const normalizedSearchTerm = searchTerm.toLowerCase().trim();
                    let visibleCount = 0;

                    permissionGroups.forEach(group => {
                        const groupTitle = group.querySelector('h3').textContent.toLowerCase();
                        const isVisible = normalizedSearchTerm === '' || groupTitle.includes(
                            normalizedSearchTerm);

                        group.style.display = isVisible ? '' : 'none';
                        if (isVisible) visibleCount++;
                    });

                    // Se nenhum resultado for encontrado, mostrar mensagem
                    const noResultsEl = document.getElementById('no-search-results');
                    if (normalizedSearchTerm !== '' && visibleCount === 0) {
                        if (!noResultsEl) {
                            const message = document.createElement('div');
                            message.id = 'no-search-results';
                            message.className = 'py-8 text-center text-gray-500';
                            message.innerHTML =
                                `Nenhum módulo encontrado para "<span class="font-medium">${searchTerm}</span>"`;
                            document.querySelector('.permissions-container').appendChild(message);
                        }
                    } else if (noResultsEl) {
                        noResultsEl.remove();
                    }

                    // Atualizar URL com parâmetro de busca sem recarregar a página
                    if (normalizedSearchTerm) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', normalizedSearchTerm);
                        window.history.replaceState({}, '', url);
                    } else {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('search');
                        window.history.replaceState({}, '', url);
                    }
                }

                // Evento de busca ao digitar
                permissionSearch.addEventListener('input', function(e) {
                    filterPermissionGroups(this.value);
                });

                // Inicializar com busca da URL se existir
                const urlParams = new URLSearchParams(window.location.search);
                const initialSearch = urlParams.get('search');
                if (initialSearch) {
                    permissionSearch.value = initialSearch;
                    filterPermissionGroups(initialSearch);
                }

                // Permitir limpar busca com Escape
                permissionSearch.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        this.value = '';
                        filterPermissionGroups('');
                    }
                });

                // Type selector change handler
                typeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    if (!selectedType) {
                        targetSelect.innerHTML = '';
                        return;
                    }

                    // Load targets based on type
                    fetch(`/admin/permissoes/targets/${selectedType}`)
                        .then(response => response.json())
                        .then(data => {
                            targetSelect.innerHTML = '<option value="">Selecione...</option>';

                            data.forEach(item => {
                                const id = item.id || item.id_departamento;
                                const name = item.name || item.descricao_departamento;

                                const option = document.createElement('option');
                                option.value = id;
                                option.textContent = name;
                                targetSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Erro ao carregar alvos:', error));
                });

                // Select/Deselect All Permissions
                document.getElementById('select-all-permissions').addEventListener('click', function() {
                    // Selecionar TODOS os checkboxes (incluindo os ocultos por filtro)
                    const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                    allCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });

                    console.log(`✅ ${allCheckboxes.length} permissões selecionadas (incluindo ocultas)`);
                });

                document.getElementById('deselect-all-permissions').addEventListener('click', function() {
                    // Deselecionar TODOS os checkboxes (incluindo os ocultos por filtro)
                    const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                    allCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    console.log(`❌ ${allCheckboxes.length} permissões desmarcadas`);
                });

                // Select/Deselect Group Permissions
                document.querySelectorAll('.select-group').forEach(button => {
                    button.addEventListener('click', function() {
                        const model = this.getAttribute('data-group');
                        document.querySelectorAll(`.permission-checkbox[data-model="${model}"]`)
                            .forEach(checkbox => {
                                checkbox.checked = true;
                            });
                    });
                });

                document.querySelectorAll('.deselect-group').forEach(button => {
                    button.addEventListener('click', function() {
                        const model = this.getAttribute('data-group');
                        document.querySelectorAll(`.permission-checkbox[data-model="${model}"]`)
                            .forEach(checkbox => {
                                checkbox.checked = false;
                            });
                    });
                });

                // Adicionar atalhos de teclado para facilitar a navegação
                document.addEventListener('keydown', function(e) {
                    // Alt+S para focar na busca
                    if (e.altKey && e.key === 's') {
                        e.preventDefault();
                        permissionSearch.focus();
                    }
                });

                // Load existing permissions if type and target are already selected
                if (typeSelect.value && targetSelect.value) {
                    // This could be extended to fetch and check existing permissions
                }


                // Adicione este código dentro do seu DOMContentLoaded, após o evento de change do typeSelect

                // Evento para carregar permissões quando um target é selecionado
                targetSelect.addEventListener('change', function() {
                    const selectedType = typeSelect.value;
                    const selectedTarget = this.value;

                    if (!selectedType || !selectedTarget) return;

                    console.log('Carregando permissões para:', selectedType, selectedTarget);

                    // Mostrar indicador de loading
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Carregando permissões...';
                    }

                    // Limpar todas as permissões selecionadas primeiro
                    const startClear = performance.now();
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    const endClear = performance.now();
                    console.log(`✓ Checkboxes desmarcados em ${(endClear - startClear).toFixed(2)}ms`);

                    // Carregar permissões existentes para o target selecionado
                    const startFetch = performance.now();
                    fetch(`/admin/permissoes/get-permissions/${selectedType}/${selectedTarget}`)
                        .then(response => response.json())
                        .then(data => {
                            const endFetch = performance.now();
                            console.log(
                                `✓ Permissões recebidas em ${(endFetch - startFetch).toFixed(2)}ms`);
                            console.log(`Total de permissões a marcar: ${data.permissions.length}`);

                            const startMark = performance.now();

                            // Criar um mapa de checkboxes por valor para acesso mais rápido
                            const checkboxMap = new Map();
                            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                                const value = checkbox.value;
                                if (!checkboxMap.has(value)) {
                                    checkboxMap.set(value, []);
                                }
                                checkboxMap.get(value).push(checkbox);
                            });

                            console.log(
                                `✓ Mapa de checkboxes criado com ${checkboxMap.size} entradas únicas`);

                            // Marcar os checkboxes em lotes para melhor performance de renderização
                            let marked = 0;
                            let notFound = [];
                            const batchSize = 100; // Processar 100 por vez
                            const permissionsArray = Array.from(data.permissions);

                            function markBatch(startIndex) {
                                const endIndex = Math.min(startIndex + batchSize, permissionsArray.length);

                                for (let i = startIndex; i < endIndex; i++) {
                                    const permissionName = permissionsArray[i];
                                    const checkboxes = checkboxMap.get(permissionName);

                                    if (checkboxes && checkboxes.length > 0) {
                                        checkboxes.forEach(checkbox => {
                                            checkbox.checked = true;
                                            marked++;
                                        });
                                    } else {
                                        notFound.push(permissionName);
                                    }
                                }

                                // Se ainda há mais para processar, agendar próximo lote
                                if (endIndex < permissionsArray.length) {
                                    requestAnimationFrame(() => markBatch(endIndex));
                                } else {
                                    // Terminamos de marcar todos
                                    finalizeMark();
                                }
                            }

                            function finalizeMark() {
                                const endMark = performance.now();
                                console.log(
                                    `✓ ${marked} checkboxes marcados em ${(endMark - startMark).toFixed(2)}ms`
                                );

                                if (notFound.length > 0) {
                                    console.warn(
                                        `⚠️  ${notFound.length} permissões não encontradas na página:`);
                                    console.warn(notFound.slice(0, 20)); // Mostrar apenas as primeiras 20
                                }

                                console.log(
                                    `✓ Processo completo em ${(endMark - startClear).toFixed(2)}ms`);

                                // Verificar após um pequeno delay se os checkboxes ainda estão marcados
                                setTimeout(() => {
                                    const checkedCount = document.querySelectorAll(
                                        '.permission-checkbox:checked').length;
                                    const visibleCheckedCount = document.querySelectorAll(
                                        '.permission-group:not([style*="display: none"]) .permission-checkbox:checked'
                                    ).length;
                                    const hiddenGroups = document.querySelectorAll(
                                        '.permission-group[style*="display: none"]').length;

                                    console.log(`🔍 Verificação após 500ms:`);
                                    console.log(`  - Total marcados: ${checkedCount}`);
                                    console.log(`  - Marcados visíveis: ${visibleCheckedCount}`);
                                    console.log(
                                        `  - Marcados ocultos: ${checkedCount - visibleCheckedCount}`
                                    );
                                    console.log(`  - Grupos ocultos: ${hiddenGroups}`);

                                    if (checkedCount !== marked) {
                                        console.error(
                                            `❌ PROBLEMA: Esperava ${marked} marcados, mas há ${checkedCount}!`
                                        );
                                        console.error(
                                            'Algo está desmarcando os checkboxes após a marcação inicial!'
                                        );
                                    } else if (visibleCheckedCount === 0 && checkedCount > 0) {
                                        console.error(
                                            `❌ PROBLEMA: Todos os ${checkedCount} checkboxes marcados estão OCULTOS!`
                                        );
                                        console.error(
                                            'Verifique se há um filtro de busca ativo ou grupos colapsados.'
                                        );
                                    } else {
                                        console.log(
                                            `✅ ${visibleCheckedCount} checkboxes visíveis e marcados corretamente!`
                                        );

                                        // Mostrar um alerta sutil no topo da página
                                        const existingAlert = document.getElementById(
                                            'permissions-loaded-alert');
                                        if (existingAlert) existingAlert.remove();

                                        const alert = document.createElement('div');
                                        alert.id = 'permissions-loaded-alert';
                                        alert.className =
                                            'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2';
                                        alert.innerHTML = `
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span><strong>${visibleCheckedCount}</strong> permissões carregadas e marcadas</span>
                                    `;
                                        document.body.appendChild(alert);

                                        // Remover após 5 segundos
                                        setTimeout(() => alert.remove(), 5000);
                                    }
                                }, 500);

                                // Restaurar botão de submit
                                if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.textContent = 'Salvar as Permissões';
                                }
                            }

                            // Iniciar marcação em lotes
                            markBatch(0);
                        })
                        .catch(error => {
                            console.error('❌ Erro ao carregar permissões:', error);

                            // Restaurar botão de submit em caso de erro
                            if (submitButton) {
                                submitButton.disabled = false;
                                submitButton.textContent = 'Salvar as Permissões';
                            }
                        });
                });

                // Adicione também no final do seu script, após carregar type e target
                if (typeSelect.value && targetSelect.value) {
                    // Disparar o evento de change para carregar as permissões se já houver seleção
                    targetSelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endpush
</x-app-layout>
