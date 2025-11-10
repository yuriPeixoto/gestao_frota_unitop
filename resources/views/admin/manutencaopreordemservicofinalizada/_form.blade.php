<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Abas -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <!-- Aba Cadastro Pré-O.S (ativa por padrão) -->
                        <button onclick="openTab(event, 'cadastro')" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                            Cadastro Pré-O.S
                        </button>
                        <!-- Aba Serviços de Pré-O.S -->
                        <button onclick="openTab(event, 'servicos')" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Serviços de Pré-O.S
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Conteúdo das Abas -->
            <div>
                <!-- Conteúdo da Aba Cadastro Pré-O.S -->
                <div id="cadastro" class="tab-content">
                    <form method="POST" action="{{ $action }}" class="space-y-4">
                        @csrf
                        @if ($method === 'PUT')
                            @method('PUT')
                        @endif

                        <!-- Cabeçalho -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <div>
                                    <label for="id_pre_os" class="block text-sm font-medium text-gray-700">Código</label>
                                    <input type="number" id="id_pre_os" name="id_pre_os" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_pre_os', $preOrdemFinalizada->id_pre_os ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_motorista" class="block text-sm font-medium text-gray-700">Motorista</label>
                                    <input type="number" id="id_motorista" name="id_motorista" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_motorista', $preOrdemFinalizada->id_motorista ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="telefone_motorista" class="block text-sm font-medium text-gray-700">Telefone/Celular do Motorista</label>
                                    <input type="number" id="telefone_motorista" name="telefone_motorista" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('telefone_motorista', $preOrdemFinalizada->telefone_motorista ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_tipostatus_pre_os" class="block text-sm font-medium text-gray-700">Status</label>
                                    <input type="number" id="id_tipostatus_pre_os" name="id_tipostatus_pre_os" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_tipostatus_pre_os', $preOrdemFinalizada->id_tipostatus_pre_os ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
                                    <input type="number" id="id_filial" name="id_filial" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_filial', $preOrdemFinalizada->id_filial ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_veiculo" class="block text-sm font-medium text-gray-700">Placa</label>
                                    <input type="number" id="id_veiculo" name="id_veiculo" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_veiculo', $preOrdemFinalizada->id_veiculo ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                                    <input type="number" id="id_departamento" name="id_departamento" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_departamento', $preOrdemFinalizada->id_departamento ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_grupo_resolvedor" class="block text-sm font-medium text-gray-700">Grupo Resolvedor</label>
                                    <input type="number" id="id_grupo_resolvedor" name="id_grupo_resolvedor" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_grupo_resolvedor', $preOrdemFinalizada->id_grupo_resolvedor ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_recepcionista" class="block text-sm font-medium text-gray-700">Recepcionista</label>
                                    <input type="number" id="id_recepcionista" name="id_recepcionista" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_recepcionista', $preOrdemFinalizada->id_recepcionista ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>
                                    <input type="number" id="id_usuario" name="id_usuario" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('id_usuario', $preOrdemFinalizada->id_usuario ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="local_execucao" class="block text-sm font-medium text-gray-700">Local Execução</label>
                                    <input type="number" id="local_execucao" name="local_execucao" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('local_execucao', $preOrdemFinalizada->local_execucao ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="km_realizacao" class="block text-sm font-medium text-gray-700">Km Realização</label>
                                    <input type="number" id="km_realizacao" name="km_realizacao" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('km_realizacao', $preOrdemFinalizada->km_realizacao ?? '') }}" readonly>
                                </div>

                                <div>
                                    <label for="horimetro_tk" class="block text-sm font-medium text-gray-700">Horímetro</label>
                                    <input type="number" id="horimetro_tk" name="horimetro_tk" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required
                                        value="{{ old('horimetro_tk', $preOrdemFinalizada->horimetro_tk ?? '') }}" readonly>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="descricao_reclamacao" class="block text-sm font-medium text-gray-700">Descrição Reclamação</label>
                                    <textarea id="descricao_reclamacao" name="descricao_reclamacao" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required readonly>
                                        {{ old('descricao_reclamacao', $preOrdemFinalizada->descricao_reclamacao ?? '') }}
                                    </textarea>
                                </div>

                                <div>
                                    <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                                    <textarea id="observacoes" name="observacoes" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required readonly>
                                        {{ old('observacoes', $preOrdemFinalizada->observacoes ?? '') }}
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.manutencaopreordemservicofinalizada.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                        </div>
                    </form>
                </div>

                <!-- Conteúdo da Aba Serviços de Pré-O.S -->
                <div id="servicos" class="tab-content" style="display: none;">
                    <form method="POST" action="{{ $action }}" class="space-y-4">
                        @csrf
                        @if ($method === 'PUT')
                            @method('PUT')
                        @endif

                        <!-- Conteúdo da Aba Serviços de Pré-O.S -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Campos para Serviços de Pré-O.S -->
                                <div>
                                    <label for="id_servico" class="block text-sm font-medium text-gray-700">Cód. Serviço</label>
                                    <input type="text" id="id_servico" name="id_servico"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required readonly
                                        value="{{ old('id_servico', $preOrdemServicosFinalizadas->id_servico ?? '') }}">
                                </div>

                                <div>
                                    <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                                    <input type="text" id="observacao" name="observacao"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required readonly
                                        value="{{ old('observacao', $preOrdemServicosFinalizadas->observacao ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.manutencaopreordemservicofinalizada.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para controle das abas -->
<script>
    function openTab(event, tabName) {
        // Oculta todos os conteúdos das abas
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });

        // Remove a classe ativa de todas as abas
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.classList.remove('border-indigo-500', 'text-indigo-600');
            link.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        // Exibe o conteúdo da aba clicada
        document.getElementById(tabName).style.display = 'block';

        // Adiciona a classe ativa à aba clicada
        event.currentTarget.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        event.currentTarget.classList.add('border-indigo-500', 'text-indigo-600');
    }

    // Ativa a aba "Cadastro Pré-O.S" ao carregar a página
    document.addEventListener('DOMContentLoaded', function () {
        const defaultTab = document.querySelector('.tab-link'); // Seleciona a primeira aba
        const defaultTabContent = document.getElementById('cadastro'); // Seleciona o conteúdo da primeira aba

        if (defaultTab && defaultTabContent) {
            defaultTab.classList.add('border-indigo-500', 'text-indigo-600');
            defaultTabContent.style.display = 'block';
        }
    });
</script>