<div class="space-y-6">
    <!-- Informações do Fornecedor -->
    <div class="rounded-lg bg-gray-50 p-4">
        <h3 class="mb-4 text-lg font-medium text-gray-900">Cadastro de Fornecedores</h3>

        <!-- Tabs Header -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" id="tab-button-dados_fornecedor"
                    class="tab-button whitespace-nowrap border-b-2 border-indigo-500 px-1 py-4 text-sm font-medium text-indigo-600"
                    onclick="showTab('dados_fornecedor')">
                    Dados Fornecedor
                </button>
                <button type="button" id="tab-button-dados_telefone"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('dados_telefone')">
                    Telefone
                </button>
                <button type="button" id="tab-button-contrato_modelo"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('contrato_modelo')">
                    Contrato x Modelo
                </button>
                <button type="button" id="tab-button-servicos"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('servicos')">
                    Serviços
                </button>
                <button type="button" id="tab-button-pecas"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('pecas')">
                    Peças
                </button>
                <button type="button" id="tab-button-mecanico"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('mecanico')">
                    Mecânico
                </button>
                <button type="button" id="tab-button-endereco"
                    class="tab-button whitespace-nowrap border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    onclick="showTab('endereco')">
                    Endereço
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="mt-4">
            <!-- Dados Fornecedor Tab -->
            <div id="dados_fornecedor" class="tab-content block">
                @include('admin.fornecedores._dados_fornecedor')
            </div>

            <!-- Dados Telefone Tab -->
            <div id="dados_telefone" class="tab-content hidden">
                @include('admin.fornecedores._dados_telefone')
            </div>

            <!-- Contrato x Modelo Tab -->
            <div id="contrato_modelo" class="tab-content hidden">
                @include('admin.fornecedores._contrato_modelo')
            </div>

            <!-- Serviços Tab -->
            <div id="servicos" class="tab-content hidden">
                @include('admin.fornecedores._servicos')
            </div>

            <!-- Peças Tab -->
            <div id="pecas" class="tab-content hidden">
                @include('admin.fornecedores._pecas')
            </div>

            <!-- Mecânico Tab -->
            <div id="mecanico" class="tab-content hidden">
                @include('admin.fornecedores._mecanico')
            </div>

            <div id="endereco" class="tab-content hidden">
                @include('admin.fornecedores._endereco')
            </div>

            <!-- Botões -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.fornecedores.index') }}"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    {{ isset($fornecedor) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Script simplificado de abas -->
    <script>
        // Função global simples para alternar entre abas
        function showTab(tabId) {
            // Esconder todos os conteúdos
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(function(content) {
                content.classList.add('hidden');
                content.classList.remove('block');
            });

            // Desativar todos os botões
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(function(button) {
                button.classList.remove('border-indigo-500', 'text-indigo-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Mostrar o conteúdo selecionado
            const selectedContent = document.getElementById(tabId);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
                selectedContent.classList.add('block');
            }

            // Ativar o botão selecionado
            const selectedButton = document.getElementById('tab-button-' + tabId);
            if (selectedButton) {
                selectedButton.classList.remove('border-transparent', 'text-gray-500');
                selectedButton.classList.add('border-indigo-500', 'text-indigo-600');
            }
        }
    </script>
