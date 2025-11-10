<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Administração de Permissões') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Permissões"
                    content="Esta tela permite gerenciar permissões para usuários e cargos de forma intuitiva. Selecione um usuário ou cargo e configure suas permissões usando os templates predefinidos ou personalizado." />
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                <div class="p-6">

                    <!-- Alertas -->
                    @if (session('notification'))
                        <div
                            class="{{ session('notification.type') === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} mb-6 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    @if (session('notification.type') === 'success')
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h3
                                        class="{{ session('notification.type') === 'success' ? 'text-green-800' : 'text-red-800' }} text-sm font-medium">
                                        {{ session('notification.title') }}
                                    </h3>
                                    <div
                                        class="{{ session('notification.type') === 'success' ? 'text-green-700' : 'text-red-700' }} mt-1 text-sm">
                                        {{ session('notification.message') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Seleção de Usuário/Cargo -->
                    <div class="mb-8 rounded-lg bg-gray-50 p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">1. Selecione o Usuário ou Cargo</h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Seleção de Tipo -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Tipo de Atribuição
                                </label>
                                <select id="permission-type"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                    <option value="">Selecione o tipo...</option>
                                    <option value="user">👤 Usuário Individual</option>
                                    <option value="role">👥 Cargo/Função</option>
                                </select>
                            </div>

                            <!-- Seleção de Alvo -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Selecionar
                                </label>
                                <select id="permission-target"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                    disabled>
                                    <option value="">Primeiro selecione o tipo...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Informações do selecionado -->
                        <div id="target-info" class="mt-4 hidden">
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-2 h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-900">Selecionado:</h4>
                                        <p id="target-display" class="text-sm text-blue-700"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Templates de Permissões -->
                    <div class="mb-8 rounded-lg bg-gray-50 p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">2. Escolha um Template de Permissões</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Template Administrador -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="admin">
                                <div class="mb-2 flex items-center">
                                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                                        <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M9.243 3.03a1 1 0 01.727 1.213L9.53 6h2.94l.56-2.243a1 1 0 111.94.486L14.53 6H16a1 1 0 110 2h-1.53l-1 4H15a1 1 0 110 2h-1.53l-.56 2.243a1 1 0 11-1.94-.486L11.47 14H8.53l-.56 2.243a1 1 0 11-1.94-.486L6.47 14H5a1 1 0 110-2h1.47l1-4H6a1 1 0 110-2h1.47l.56-2.243a1 1 0 011.213-.727zM9.53 8l-1 4h2.94l1-4H9.53z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Administrador</h4>
                                        <p class="text-sm text-gray-500">Acesso completo ao sistema</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Todas as permissões de administração, configuração e
                                    relatórios.</p>
                            </div>

                            <!-- Template Compras -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="compras">
                                <div class="mb-2 flex items-center">
                                    <div
                                        class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Módulo Compras</h4>
                                        <p class="text-sm text-gray-500">Gestão completa de compras</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Solicitações, pedidos, orçamentos, fornecedores e
                                    relatórios de compras.</p>
                            </div>

                            <!-- Template Solicitações -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="solicitacoes">
                                <div class="mb-2 flex items-center">
                                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Solicitações</h4>
                                        <p class="text-sm text-gray-500">Apenas solicitações de compra</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Criar, visualizar e editar solicitações de compra.</p>
                            </div>

                            <!-- Template Aprovador -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="aprovador">
                                <div class="mb-2 flex items-center">
                                    <div
                                        class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100">
                                        <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Aprovador</h4>
                                        <p class="text-sm text-gray-500">Aprovação de processos</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Visualizar e aprovar/rejeitar solicitações e pedidos.
                                </p>
                            </div>

                            <!-- Template Consulta -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="consulta">
                                <div class="mb-2 flex items-center">
                                    <div
                                        class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100">
                                        <svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Apenas Consulta</h4>
                                        <p class="text-sm text-gray-500">Somente visualização</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Apenas visualizar solicitações e relatórios básicos.
                                </p>
                            </div>

                            <!-- Template Personalizado -->
                            <div class="permission-template cursor-pointer rounded-lg border border-gray-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50"
                                data-template="custom">
                                <div class="mb-2 flex items-center">
                                    <div
                                        class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                                        <svg class="h-5 w-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Personalizado</h4>
                                        <p class="text-sm text-gray-500">Configuração manual</p>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600">Selecionar permissões manualmente uma a uma.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário de Permissões -->
                    <form id="permissions-form" action="{{ route('admin.configuracoes.permissoes.assign') }}"
                        method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" name="type" id="form-type">
                        <input type="hidden" name="target_id" id="form-target-id">

                        <!-- Área de permissões personalizadas -->
                        <div id="custom-permissions" class="mb-8 rounded-lg bg-gray-50 p-6" style="display: none;">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">3. Configuração Manual de Permissões
                            </h3>

                            <!-- Busca de permissões -->
                            <div class="mb-4">
                                <div class="relative">
                                    <input type="text" id="permission-search" placeholder="Buscar permissões..."
                                        class="w-full rounded-md border border-gray-300 py-2 pl-10 pr-4 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissões do módulo de compras -->
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- Solicitações de Compra -->
                                <div class="permission-group rounded-lg border bg-white p-4">
                                    <h4 class="text-md mb-3 flex items-center font-semibold text-gray-900">
                                        <svg class="mr-2 h-5 w-5 text-blue-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Solicitações de Compra
                                    </h4>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="visualizar_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">👁️ Visualizar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="criar_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">➕ Criar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="editar_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">✏️ Editar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="excluir_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">🗑️ Excluir</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="aprovar_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">✅ Aprovar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="rejeitar_solicitacao_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">❌ Rejeitar</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Pedidos de Compra -->
                                <div class="permission-group rounded-lg border bg-white p-4">
                                    <h4 class="text-md mb-3 flex items-center font-semibold text-gray-900">
                                        <svg class="mr-2 h-5 w-5 text-green-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd"
                                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Pedidos de Compra
                                    </h4>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="visualizar_pedido_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">👁️ Visualizar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="criar_pedido_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">➕ Criar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="editar_pedido_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">✏️ Editar</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="aprovar_pedido_compra"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">✅ Aprovar</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Outras permissões de compras -->
                                <div class="permission-group rounded-lg border bg-white p-4">
                                    <h4 class="text-md mb-3 flex items-center font-semibold text-gray-900">
                                        <svg class="mr-2 h-5 w-5 text-purple-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2h1v-2h-1zm-2-2H7v4h6v-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Outros Módulos
                                    </h4>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="visualizar_fornecedor"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">🏭 Fornecedores</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="visualizar_relatorios_compras"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">📊 Relatórios Compras</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                value="exportar_relatorios_compras"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">📥 Exportar Relatórios</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Acesso geral -->
                                <div class="permission-group rounded-lg border bg-white p-4">
                                    <h4 class="text-md mb-3 flex items-center font-semibold text-gray-900">
                                        <svg class="mr-2 h-5 w-5 text-gray-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Acesso Geral
                                    </h4>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="acessar_dashboard"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">🏠 Dashboard</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="acessar_configuracoes"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">⚙️ Configurações</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                            <button type="button" id="clear-permissions"
                                class="rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                🧹 Remover Todas Permissões
                            </button>

                            <div class="flex space-x-3">
                                <button type="button" id="cancel-btn"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="rounded-md border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    💾 Salvar Permissões
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const permissionType = document.getElementById('permission-type');
            const permissionTarget = document.getElementById('permission-target');
            const targetInfo = document.getElementById('target-info');
            const targetDisplay = document.getElementById('target-display');
            const permissionsForm = document.getElementById('permissions-form');
            const customPermissions = document.getElementById('custom-permissions');
            const templates = document.querySelectorAll('.permission-template');

            // Templates de permissões
            const permissionTemplates = {
                admin: ['visualizar_solicitacao_compra', 'criar_solicitacao_compra',
                    'editar_solicitacao_compra', 'excluir_solicitacao_compra', 'aprovar_solicitacao_compra',
                    'rejeitar_solicitacao_compra', 'visualizar_pedido_compra', 'criar_pedido_compra',
                    'editar_pedido_compra', 'aprovar_pedido_compra', 'visualizar_fornecedor',
                    'visualizar_relatorios_compras', 'exportar_relatorios_compras', 'acessar_dashboard',
                    'acessar_configuracoes'
                ],
                compras: ['visualizar_solicitacao_compra', 'criar_solicitacao_compra',
                    'editar_solicitacao_compra', 'visualizar_pedido_compra', 'criar_pedido_compra',
                    'editar_pedido_compra', 'visualizar_fornecedor', 'visualizar_relatorios_compras',
                    'exportar_relatorios_compras', 'acessar_dashboard'
                ],
                solicitacoes: ['visualizar_solicitacao_compra', 'criar_solicitacao_compra',
                    'editar_solicitacao_compra', 'acessar_dashboard'
                ],
                aprovador: ['visualizar_solicitacao_compra', 'aprovar_solicitacao_compra',
                    'rejeitar_solicitacao_compra', 'visualizar_pedido_compra', 'aprovar_pedido_compra',
                    'visualizar_relatorios_compras', 'acessar_dashboard'
                ],
                consulta: ['visualizar_solicitacao_compra', 'visualizar_pedido_compra', 'visualizar_fornecedor',
                    'visualizar_relatorios_compras', 'acessar_dashboard'
                ],
                custom: []
            };

            // Dados dos usuários e cargos
            const users = @json($users);
            const cargos = @json($cargos);

            // Carregar alvos baseado no tipo
            permissionType.addEventListener('change', function() {
                const type = this.value;
                permissionTarget.innerHTML = '<option value="">Carregando...</option>';
                permissionTarget.disabled = true;

                if (type === 'user') {
                    permissionTarget.innerHTML = '<option value="">Selecione um usuário...</option>';
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.name} (${user.email})`;
                        permissionTarget.appendChild(option);
                    });
                } else if (type === 'role') {
                    permissionTarget.innerHTML = '<option value="">Selecione um cargo...</option>';
                    cargos.forEach(cargo => {
                        const option = document.createElement('option');
                        option.value = cargo.id_tipo_pessoal;
                        option.textContent = cargo.descricao_tipo;
                        permissionTarget.appendChild(option);
                    });
                }

                permissionTarget.disabled = false;
                hideTargetInfo();
            });

            // Mostrar informações do alvo selecionado
            permissionTarget.addEventListener('change', function() {
                if (this.value) {
                    showTargetInfo();
                } else {
                    hideTargetInfo();
                }
            });

            // Manipular clique nos templates
            templates.forEach(template => {
                template.addEventListener('click', function() {
                    if (!permissionType.value || !permissionTarget.value) {
                        alert('Por favor, selecione primeiro o tipo e o alvo das permissões.');
                        return;
                    }

                    // Remover seleção anterior
                    templates.forEach(t => t.classList.remove('border-indigo-500', 'bg-indigo-50'));

                    // Marcar template selecionado
                    this.classList.add('border-indigo-500', 'bg-indigo-50');

                    const templateType = this.dataset.template;

                    if (templateType === 'custom') {
                        showCustomPermissions();
                    } else {
                        applyTemplate(templateType);
                    }
                });
            });

            function showTargetInfo() {
                const type = permissionType.value;
                const targetId = permissionTarget.value;
                let displayText = '';

                if (type === 'user') {
                    const user = users.find(u => u.id == targetId);
                    displayText = `👤 ${user.name} (${user.email})`;
                } else if (type === 'role') {
                    const cargo = cargos.find(c => c.id_tipo_pessoal == targetId);
                    displayText = `👥 ${cargo.descricao_tipo}`;
                }

                targetDisplay.textContent = displayText;
                targetInfo.classList.remove('hidden');
            }

            function hideTargetInfo() {
                targetInfo.classList.add('hidden');
                permissionsForm.style.display = 'none';
                customPermissions.style.display = 'none';
                templates.forEach(t => t.classList.remove('border-indigo-500', 'bg-indigo-50'));
            }

            function showCustomPermissions() {
                document.getElementById('form-type').value = permissionType.value;
                document.getElementById('form-target-id').value = permissionTarget.value;

                permissionsForm.style.display = 'block';
                customPermissions.style.display = 'block';

                // Carregar permissões existentes
                loadExistingPermissions();
            }

            function applyTemplate(templateType) {
                document.getElementById('form-type').value = permissionType.value;
                document.getElementById('form-target-id').value = permissionTarget.value;

                permissionsForm.style.display = 'block';
                customPermissions.style.display = 'block';

                // Limpar todas as checkboxes
                const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                checkboxes.forEach(cb => cb.checked = false);

                // Marcar permissões do template
                const templatePermissions = permissionTemplates[templateType];
                templatePermissions.forEach(permission => {
                    const checkbox = document.querySelector(`input[value="${permission}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }

            function loadExistingPermissions() {
                const type = permissionType.value;
                const targetId = permissionTarget.value;

                fetch(`{{ route('admin.configuracoes.permissoes.get-permissions', ['type' => ':type', 'id' => ':id']) }}`
                        .replace(':type', type)
                        .replace(':id', targetId))
                    .then(response => response.json())
                    .then(data => {
                        // Limpar checkboxes
                        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                        checkboxes.forEach(cb => cb.checked = false);

                        // Marcar permissões existentes
                        data.permissions.forEach(permission => {
                            const checkbox = document.querySelector(`input[value="${permission}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao carregar permissões:', error);
                    });
            }

            // Botão de limpar todas permissões
            document.getElementById('clear-permissions').addEventListener('click', function() {
                if (confirm(
                        'Tem certeza que deseja remover TODAS as permissões? Esta ação não pode ser desfeita.'
                        )) {
                    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
                    checkboxes.forEach(cb => cb.checked = false);
                }
            });

            // Botão de cancelar
            document.getElementById('cancel-btn').addEventListener('click', function() {
                permissionsForm.style.display = 'none';
                customPermissions.style.display = 'none';
                templates.forEach(t => t.classList.remove('border-indigo-500', 'bg-indigo-50'));
            });

            // Busca de permissões
            document.getElementById('permission-search').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const groups = document.querySelectorAll('.permission-group');

                groups.forEach(group => {
                    const labels = group.querySelectorAll('label');
                    let hasVisibleItems = false;

                    labels.forEach(label => {
                        const text = label.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            label.style.display = 'flex';
                            hasVisibleItems = true;
                        } else {
                            label.style.display = 'none';
                        }
                    });

                    group.style.display = hasVisibleItems ? 'block' : 'none';
                });
            });
        });
    </script>
</x-app-layout>
