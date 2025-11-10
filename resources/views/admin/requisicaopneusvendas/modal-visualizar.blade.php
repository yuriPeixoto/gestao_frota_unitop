<!-- Modal (sem x-data próprio, usa o contexto do pai) -->
<div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Backdrop -->
    <div @click="closeModal()" class="fixed inset-0 bg-gray-600 bg-opacity-40 backdrop-blur-sm"></div>

    <!-- Modal content -->
    <div class="flex items-start justify-center min-h-screen p-2 sm:p-4">
        <div class="bg-white rounded-lg w-full max-w-xs sm:max-w-sm md:max-w-4xl lg:max-w-6xl xl:max-w-7xl relative mt-4 sm:mt-8"
            @click.stop>
            <!-- Header -->
            <div class="flex justify-between items-center p-3 sm:p-4 border-b">
                <h3 class="text-base sm:text-lg font-semibold">Detalhes da Requisição</h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Loading -->
            <div x-show="loading" class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Carregando dados...</p>
            </div>

            <!-- Body -->
            <div x-show="!loading && dados" class="p-3 sm:p-6 max-h-[80vh] overflow-y-auto">
                <!-- Informações básicas -->
                <div class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-xs text-gray-600">Código</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.id_requisicao_pneu || ''" readonly>
                        </div>
                        <div class="sm:col-span-1 lg:col-span-2">
                            <label class="block text-xs text-gray-600">Usuário Solicitante:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.usuario_solicitante || ''" readonly>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs text-gray-600">Filial:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.filial || ''" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4">
                        <div>
                            <label class="block text-xs text-gray-600">Situação:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.situacao || ''" readonly>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Usuário Vendas:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.usuario_vendas || ''" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4">
                        <div>
                            <label class="block text-xs text-gray-600">Data Inclusão:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.data_inclusao || ''" readonly>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Data Alteração:</label>
                            <input type="text" class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100"
                                :value="dados?.requisicao?.data_alteracao || ''" readonly>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div x-show="dados?.requisicao?.observacao || dados?.requisicao?.observacao_solicitante">
                        <h3 class="text-sm sm:text-base font-semibold mt-3 sm:mt-4 mb-2">Observações</h3>
                        <div class="grid grid-cols-1 gap-2 sm:gap-4">
                            <div x-show="dados?.requisicao?.observacao">
                                <label class="block text-xs text-gray-600">Observação:</label>
                                <textarea class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100" rows="2"
                                    :value="dados?.requisicao?.observacao || ''" readonly></textarea>
                            </div>
                            <div x-show="dados?.requisicao?.observacao_solicitante">
                                <label class="block text-xs text-gray-600">Observação do Solicitante:</label>
                                <textarea class="w-full border rounded px-2 py-1 text-xs sm:text-sm bg-gray-100" rows="2"
                                    :value="dados?.requisicao?.observacao_solicitante || ''" readonly></textarea>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-sm sm:text-base font-semibold mt-3 sm:mt-4 mb-2">Itens da Requisição</h3>

                    <!-- Lista de Itens com Pneus -->
                    <div class="space-y-4">
                        <template x-for="(item, index) in dados?.itens" :key="item.id_requisicao_pneu_modelos">
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <table class="min-w-full text-xs border bg-white">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="text-sm text-gray-600 font-medium">Código Item</th>
                                            <th class="text-sm text-gray-600 font-medium">Modelo do Pneu</th>
                                            <th class="text-sm text-gray-600 font-medium">Data Inclusão</th>
                                            <th class="text-sm text-gray-600 font-medium">Quantidade</th>
                                            <th class="text-sm text-gray-600 font-medium">Quantidade Baixa</th>
                                            <th class="text-sm text-gray-600 font-medium">Data Baixa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hover:bg-gray-50">
                                            <td class="text-sm text-gray-600 text-center"
                                                x-text="item.id_requisicao_pneu_modelos">
                                            </td>
                                            <td class="text-sm text-gray-600 text-center" x-text="item.modelo_pneu">
                                            </td>
                                            <td class="text-sm text-gray-600 text-center" x-text="item.data_inclusao">
                                            </td>
                                            <td class="text-sm text-gray-600 text-center" x-text="item.quantidade"></td>
                                            <td class="text-sm text-gray-600 text-center"
                                                x-text="item.quantidade_baixa"></td>
                                            <td class="text-sm text-gray-600 text-center" x-text="item.data_baixa"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Pneus vinculados a este item -->
                                <div x-show="item.pneus && item.pneus.length > 0">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Pneus Vinculados:</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-xs border bg-white">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="px-2 py-1 border text-left">Número de Fogo</th>
                                                    <th class="px-2 py-1 border text-left">Modelo</th>
                                                    <th class="px-2 py-1 border text-left">Status</th>
                                                    <th class="px-2 py-1 border text-left">Vida</th>
                                                    <th class="px-2 py-1 border text-left">Valor Venda</th>
                                                    <th class="px-2 py-1 border text-left hidden sm:table-cell">Data
                                                        Inclusão</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(pneu, pneuIndex) in item.pneus"
                                                    :key="`${item.id_requisicao_pneu_modelos}-${pneuIndex}`">
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-2 py-1 border" x-text="pneu.id_pneu || 'N/A'">
                                                        </td>
                                                        <td class="px-2 py-1 border font-medium"
                                                            x-text="pneu.numero_fogo || 'N/A'"></td>
                                                        <td class="px-2 py-1 border">
                                                            <span class="px-2 py-1 rounded text-xs"
                                                                :class="{
                                                                    'bg-green-100 text-green-800': pneu
                                                                        .status_pneu === 'ESTOQUE',
                                                                    'bg-blue-100 text-blue-800': pneu
                                                                        .status_pneu === 'DÍSPONIVEL PARA REUSO',
                                                                    'bg-gray-100 text-gray-800': !['ESTOQUE',
                                                                        'DÍSPONIVEL PARA REUSO'
                                                                    ].includes(pneu.status_pneu)
                                                                }"
                                                                x-text="pneu.status_pneu || 'N/A'">
                                                            </span>
                                                        </td>
                                                        <td class="px-2 py-1 border" x-text="pneu.vida_pneu || 'N/A'">
                                                        </td>
                                                        <td class="px-2 py-1 border font-medium text-green-600"
                                                            x-text="pneu.valor_venda || 'R$ 0,00'"></td>
                                                        <td class="px-2 py-1 border hidden sm:table-cell"
                                                            x-text="pneu.data_inclusao_item || 'N/A'"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Caso não tenha pneus vinculados -->
                                <div x-show="!item.pneus || item.pneus.length === 0"
                                    class="text-center py-4 text-gray-500 italic">
                                    Nenhum pneu vinculado a este item
                                </div>
                            </div>
                        </template>

                        <!-- Caso não tenha itens -->
                        <div x-show="!dados?.itens?.length" class="text-center py-8 text-gray-500">
                            Nenhum item encontrado nesta requisição
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer com botões de ação - PASSANDO O ID DA REQUISIÇÃO -->
            <div class="sm:gap-4 bg-gray-50 border-t px-3 py-2 space-y-0 space-x-2"
                :data-requisicao-id="dados?.requisicao?.id_requisicao_pneu" x-init="$nextTick(() => {
                    if (dados?.requisicao?.id_requisicao_pneu) {
                        $el.dispatchEvent(new CustomEvent('set-requisicao-id', {
                            detail: { id: dados.requisicao.id_requisicao_pneu },
                            bubbles: true
                        }));
                    }
                })"
                @requisicao-atualizada="closeModal()">

                <!-- Input hidden para facilitar a captura do ID -->
                <input type="hidden" name="requisicao_id" :value="dados?.requisicao?.id_requisicao_pneu">

                @include('admin.requisicaopneusvendas._buttons')
            </div>
        </div>
    </div>
</div>
