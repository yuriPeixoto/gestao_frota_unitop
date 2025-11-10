<!-- Componente principal com modais -->
<div x-data="requisicaoComponent()">

    <!-- Botões de ação -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <!-- CORRIGIDO: Removido modalOpen = false e adicionado método próprio -->
        <x-forms.button @click="closeModal()" variant="outlined">
            Fechar
        </x-forms.button>
        @if (auth()->user()->hasRole('Administrador do Módulo Compras') || auth()->user()->is_superuser)
            <button @click="abrirConfirmacao('aprovar')"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Aprovar Requisição
            </button>

            <button @click="abrirConfirmacao('reprovar')"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                Reprovar Requisição
            </button>

            <!-- NOVO: Botão Valor Venda Pneu -->
            <x-forms.button @click="abrirModalValores()" variant="outlined">
                <x-icons.bank-notes class="h-4 w-4 mr-2 text-green-600" />
                Valor Venda Pneu
            </x-forms.button>
        @endif
    </div>

    <!-- Modal de Confirmação - CORRIGIDO: z-index aumentado para 60 -->
    <div x-show="modalConfirmacao.aberto" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;"
        @keydown.escape.window="fecharConfirmacao()">

        <!-- Backdrop - CORRIGIDO: backdrop mais escuro -->
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm" @click="fecharConfirmacao()"></div>

        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="modalConfirmacao.aberto" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 relative z-[70]">

                <!-- Header do Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <!-- Ícone dinâmico baseado na ação -->
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                :class="modalConfirmacao.config.corFundo">
                                <svg class="w-6 h-6" :class="modalConfirmacao.config.corIcone" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        :d="modalConfirmacao.config.icone"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900" x-text="modalConfirmacao.config.titulo">
                        </h3>
                    </div>

                    <button @click="fecharConfirmacao()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Corpo do Modal -->
                <div class="p-6">
                    <p class="text-gray-600 mb-4" x-text="modalConfirmacao.config.mensagem"></p>

                    <!-- Campo de Justificativa -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Justificativa de finalização
                            <span class="text-red-500"
                                x-show="modalConfirmacao.config.justificativaObrigatoria">*</span>
                        </label>
                        <textarea x-model="modalConfirmacao.justificativa"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            rows="3" :placeholder="modalConfirmacao.config.placeholderJustificativa"></textarea>

                        <!-- Erro de validação -->
                        <p x-show="modalConfirmacao.erro" x-text="modalConfirmacao.erro"
                            class="text-red-600 text-sm mt-1"></p>
                    </div>
                </div>

                <!-- Footer do Modal -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                    <button @click="fecharConfirmacao()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>

                    <button @click="confirmarAcao()" :disabled="modalConfirmacao.processando"
                        class="px-4 py-2 rounded-md text-white transition-colors flex items-center"
                        :class="modalConfirmacao.config.corBotao">

                        <!-- Loading spinner -->
                        <svg x-show="modalConfirmacao.processando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        <span
                            x-text="modalConfirmacao.processando ? 'Processando...' : modalConfirmacao.config.textoBotao"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- NOVO: Modal de Valores dos Pneus com Edição -->
    <div x-show="modalValores.aberto" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;"
        @keydown.escape.window="fecharModalValores()">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm" @click="fecharModalValores()"></div>

        <!-- Modal Container -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="modalValores.aberto" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 relative z-[70] max-h-[90vh] overflow-hidden">

                <!-- Header do Modal -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                <span x-show="!modalValores.modoEdicao">Valores de Venda dos Pneus</span>
                                <span x-show="modalValores.modoEdicao">Editar Valores de Venda dos Pneus</span>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1" x-show="modalValores.modoEdicao">
                                Clique nos valores para editar. Use Tab para navegar entre os campos.
                            </p>
                        </div>
                    </div>

                    <!-- Toggle de Modo de Edição -->
                    <div class="flex items-center space-x-3">
                        <label class="flex items-center cursor-pointer">
                            <span class="text-sm text-gray-700 mr-2">Modo Edição</span>
                            <div class="relative">
                                <input type="checkbox" x-model="modalValores.modoEdicao" class="sr-only">
                                <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors"
                                    :class="modalValores.modoEdicao ? 'bg-blue-600' : 'bg-gray-200'"></div>
                                <div class="absolute inset-y-0 left-0 w-4 h-4 m-1 bg-white rounded-full shadow transition-transform"
                                    :class="modalValores.modoEdicao ? 'transform translate-x-4' : ''"></div>
                            </div>
                        </label>

                        <button @click="fecharModalValores()"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Corpo do Modal com Scroll -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <!-- Loading State -->
                    <div x-show="modalValores.carregando" class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="ml-2 text-gray-600">Carregando valores...</span>
                    </div>

                    <!-- Erro State -->
                    <div x-show="modalValores.erro && !modalValores.carregando" class="text-center py-8">
                        <div class="text-red-600 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-red-800 font-medium mb-2">Erro ao carregar valores</p>
                        <p class="text-red-600 mb-4" x-text="modalValores.erro"></p>
                        <button @click="carregarValoresPneus()"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Tentar Novamente
                        </button>
                    </div>

                    <!-- Content State -->
                    <div x-show="!modalValores.carregando && !modalValores.erro">
                        <!-- Barra de Ações (visível apenas no modo edição) -->
                        <div x-show="modalValores.modoEdicao"
                            class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm text-yellow-800">
                                        <span x-text="Object.keys(modalValores.valoresEditados).length"></span>
                                        valor(es) modificado(s)
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click="aplicarValorEmLote()"
                                        class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                                        Aplicar em Lote
                                    </button>
                                    <button @click="resetarValores()"
                                        class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                        Resetar Tudo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800">Total de Pneus</h4>
                                <p class="text-2xl font-bold text-blue-900" x-text="modalValores.resumo.totalPneus">
                                </p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-800">Valor Total</h4>
                                <p class="text-2xl font-bold text-green-900" x-text="modalValores.resumo.valorTotal">
                                </p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-purple-800">Valor Médio</h4>
                                <p class="text-2xl font-bold text-purple-900" x-text="modalValores.resumo.valorMedio">
                                </p>
                            </div>
                        </div>

                        <!-- Tabela de Pneus -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID/Número Fogo
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Modelo/Medida
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Condição/Vida
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valor Venda
                                            <span x-show="modalValores.modoEdicao"
                                                class="text-blue-600">(Editável)</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="(pneu, index) in modalValores.pneus" :key="pneu.id">
                                        <tr class="hover:bg-gray-50"
                                            :class="modalValores.valoresEditados[pneu.id] ?
                                                'bg-yellow-50 border-l-4 border-l-yellow-400' : ''">

                                            <!-- ID/Número Fogo -->
                                            <td class="px-4 py-3 text-sm">
                                                <div>
                                                    <div class="font-medium text-gray-900" x-text="pneu.codigo"></div>
                                                </div>
                        </div>
                        </td>

                        <!-- Modelo/Medida -->
                        <td class="px-4 py-3 text-sm">
                            <div>
                                <div class="text-gray-900 font-medium" x-text="pneu.modelo"></div>
                            </div>
                        </td>

                        <!-- Condição -->
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                x-text="pneu.condicao">
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                :class="pneu.status === 'AGUARDANDO APROVAÇÃO' ?
                                    'bg-blue-100 text-blue-800' :
                                    pneu.status === 'APROVADO' ? 'bg-green-100 text-green-800' :
                                    pneu.status === 'REPROVADO' ? 'bg-red-100 text-red-800' :
                                    'bg-gray-100 text-gray-800'"
                                x-text="pneu.status">
                            </span>
                        </td>

                        <!-- Valor Venda (Editável) -->
                        <td class="px-4 py-3 text-sm text-right">
                            <!-- Modo Visualização -->
                            <div x-show="!modalValores.modoEdicao">
                                <span class="font-medium text-gray-900" x-text="pneu.valorVenda"></span>
                            </div>

                            <!-- Modo Edição -->
                            <div x-show="modalValores.modoEdicao" class="relative">
                                <div class="flex items-center justify-end">
                                    <span class="text-gray-500 mr-1">R$</span>
                                    <input type="text" :value="obterValorEdicao(pneu.id, pneu.valorVendaNumerico)"
                                        @input="atualizarValorPneu(pneu.id, $event.target.value)"
                                        @blur="validarValorPneu(pneu.id, $event.target.value)"
                                        @keydown.enter="$event.target.blur()"
                                        @keydown.escape="cancelarEdicaoValor(pneu.id)"
                                        class="w-24 px-2 py-1 text-right border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="modalValores.valoresEditados[pneu.id] ?
                                            'border-yellow-400 bg-yellow-50' : ''"
                                        placeholder="0,00">
                                </div>

                                <!-- Indicador de alteração -->
                                <div x-show="modalValores.valoresEditados[pneu.id]" class="absolute -top-1 -right-1">
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full border-2 border-white">
                                    </div>
                                </div>
                            </div>
                        </td>
                        </tr>
                        </template>
                        </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div x-show="modalValores.pneus.length === 0" class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p class="text-gray-500">Nenhum pneu encontrado nesta requisição</p>
                    </div>
                </div>

                <!-- Modal de Aplicação em Lote -->
                <div x-show="modalValores.modalLote.aberto"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[80]">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Aplicar Valor em Lote</h4>
                        <p class="text-sm text-gray-600 mb-4">
                            Este valor será aplicado a todos os pneus selecionados ou filtrados.
                        </p>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Valor a ser aplicado
                            </label>
                            <div class="flex items-center">
                                <span class="text-gray-500 mr-2">R$</span>
                                <input type="text" x-model="modalValores.modalLote.valor"
                                    @input="formatarValorLote($event.target.value)"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="0,00">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button @click="modalValores.modalLote.aberto = false"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button @click="confirmarAplicacaoLote()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="flex justify-between items-center p-6 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <span x-show="!modalValores.modoEdicao">Modo visualização ativo</span>
                    <span x-show="modalValores.modoEdicao">
                        <span x-text="Object.keys(modalValores.valoresEditados).length"></span> alteração(ões)
                        pendente(s)
                    </span>
                </div>

                <div class="flex space-x-3">
                    <button @click="fecharModalValores()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        <span
                            x-show="!modalValores.modoEdicao || Object.keys(modalValores.valoresEditados).length === 0">Fechar</span>
                        <span
                            x-show="modalValores.modoEdicao && Object.keys(modalValores.valoresEditados).length > 0">Cancelar</span>
                    </button>

                    <button @click="exportarValores()" :disabled="modalValores.carregando"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors disabled:bg-gray-400">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Exportar Excel
                    </button>

                    <!-- Botão Salvar (apenas no modo edição) -->
                    <button x-show="modalValores.modoEdicao && Object.keys(modalValores.valoresEditados).length > 0"
                        @click="salvarValoresEditados()" :disabled="modalValores.salvando"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-green-400 flex items-center">

                        <svg x-show="modalValores.salvando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        <svg x-show="!modalValores.salvando" class="w-4 h-4 inline mr-2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>

                        <span x-text="modalValores.salvando ? 'Salvando...' : 'Salvar Alterações'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Área de feedback (simulação) -->
<div x-show="feedback.mostrar" x-transition class="mt-4 p-4 rounded-md"
    :class="feedback.tipo === 'sucesso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
    style="display: none;">
    <p x-text="feedback.mensagem"></p>
</div>
</div>
@push('scripts')
    @include('admin.requisicaopneusvendas._scripts')
@endpush
