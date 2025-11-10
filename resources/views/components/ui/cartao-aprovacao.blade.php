<div 
    id="{{ $componenteId() }}" 
    class="border border-gray-200 rounded-lg shadow-sm {{ $classesCss }}"
    x-data="cartaoAprovacao({
        entidadeTipo: '{{ $entidadeTipo }}',
        entidadeId: {{ $entidade->id ?? 'null' }},
        status: '{{ $status }}',
        etapas: {{ json_encode($etapas) }},
        urlAprovar: '{{ $urlAprovar }}',
        urlRejeitar: '{{ $urlRejeitar }}',
        urlObservacao: '{{ $urlObservacao }}',
        podeAprovar: {{ $podeAprovar ? 'true' : 'false' }},
        podeAdicionarObservacao: {{ $podeAdicionarObservacao ? 'true' : 'false' }},
        requerJustificativa: {{ $requerJustificativa ? 'true' : 'false' }},
        valor: {{ $valor }},
        exibeHistorico: {{ $exibeHistorico ? 'true' : 'false' }},
        historico: {{ json_encode($historico) }}
    })"
>
    <!-- Cabeçalho com status -->
    <div class="bg-gray-50 p-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-700">Status de Aprovação</h3>
        <span class="px-2.5 py-0.5 rounded-full text-sm font-medium {{ $classeStatus() }}">
            {{ $labelStatus() }}
        </span>
    </div>

    <!-- Conteúdo -->
    <div class="p-4 space-y-4">
        <!-- Barra de progresso/etapas -->
        <div class="relative">
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                <template x-for="(etapa, index) in etapas" :key="index">
                    <div 
                        :class="{
                            'bg-blue-500': etapa.cor === 'blue' && status === etapa.valor,
                            'bg-green-500': etapa.cor === 'green' && status === etapa.valor,
                            'bg-red-500': etapa.cor === 'red' && status === etapa.valor,
                            'bg-yellow-500': etapa.cor === 'yellow' && status === etapa.valor,
                            'bg-indigo-500': etapa.cor === 'indigo' && status === etapa.valor,
                            'bg-purple-500': etapa.cor === 'purple' && status === etapa.valor,
                            'bg-gray-500': etapa.cor === 'gray' && status === etapa.valor,
                            'bg-gray-300': status !== etapa.valor
                        }"
                        :style="'width: ' + (100 / etapas.length) + '%'"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                    ></div>
                </template>
            </div>
            
            <div class="flex justify-between">
                <template x-for="(etapa, index) in etapas" :key="index">
                    <div 
                        class="flex flex-col items-center"
                        :style="'width: ' + (100 / etapas.length) + '%'"
                    >
                        <div 
                            :class="{
                                'bg-blue-500 border-blue-500': etapa.cor === 'blue' && status === etapa.valor,
                                'bg-green-500 border-green-500': etapa.cor === 'green' && status === etapa.valor,
                                'bg-red-500 border-red-500': etapa.cor === 'red' && status === etapa.valor,
                                'bg-yellow-500 border-yellow-500': etapa.cor === 'yellow' && status === etapa.valor,
                                'bg-indigo-500 border-indigo-500': etapa.cor === 'indigo' && status === etapa.valor,
                                'bg-purple-500 border-purple-500': etapa.cor === 'purple' && status === etapa.valor,
                                'bg-gray-500 border-gray-500': etapa.cor === 'gray' && status === etapa.valor,
                                'bg-white border-gray-300': status !== etapa.valor
                            }"
                            class="w-4 h-4 rounded-full border-2 mb-1"
                        ></div>
                        <span 
                            class="text-xs font-medium"
                            :class="{
                                'text-blue-600': etapa.cor === 'blue' && status === etapa.valor,
                                'text-green-600': etapa.cor === 'green' && status === etapa.valor,
                                'text-red-600': etapa.cor === 'red' && status === etapa.valor,
                                'text-yellow-600': etapa.cor === 'yellow' && status === etapa.valor,
                                'text-indigo-600': etapa.cor === 'indigo' && status === etapa.valor,
                                'text-purple-600': etapa.cor === 'purple' && status === etapa.valor,
                                'text-gray-600': etapa.cor === 'gray' && status === etapa.valor,
                                'text-gray-500': status !== etapa.valor
                            }"
                            x-text="etapa.label"
                        ></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Botões de ação -->
        <div 
            x-show="podeAprovar && (status !== 'aprovado' && status !== 'rejeitado' && status !== 'cancelado' && status !== 'finalizado')"
            class="flex flex-wrap gap-2 justify-center sm:justify-end mt-4"
        >
            <button 
                @click="abrirModalRejeitar()"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Rejeitar
            </button>
            
            <button 
                @click="abrirModalAprovar()"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Aprovar
            </button>
        </div>

        <!-- Botão para adicionar observação -->
        <div 
            x-show="podeAdicionarObservacao && status !== 'cancelado'"
            class="mt-4"
        >
            <button 
                @click="abrirModalObservacao()"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                Adicionar Observação
            </button>
        </div>

        <!-- Histórico de aprovações -->
        <div x-show="exibeHistorico && historico.length > 0" class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Histórico de Aprovações</h4>
            
            <div class="space-y-3 max-h-60 overflow-y-auto px-1">
                <template x-for="(registro, index) in historico" :key="index">
                    <div class="p-3 bg-gray-50 rounded-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium" x-text="registro.usuario_nome"></p>
                                <p class="text-xs text-gray-500" x-text="formatarData(registro.data)"></p>
                            </div>
                            <span 
                                class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-800': registro.acao === 'aprovacao',
                                    'bg-red-100 text-red-800': registro.acao === 'rejeicao',
                                    'bg-blue-100 text-blue-800': registro.acao === 'observacao',
                                    'bg-gray-100 text-gray-800': !['aprovacao', 'rejeicao', 'observacao'].includes(registro.acao)
                                }"
                                x-text="formatarAcao(registro.acao)"
                            ></span>
                        </div>
                        <p 
                            x-show="registro.observacao"
                            class="mt-2 text-sm text-gray-600"
                            x-text="registro.observacao"
                        ></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal de aprovação -->
    <div 
        x-show="modalAprovacaoAberto" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div 
                x-show="modalAprovacaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity" 
                aria-hidden="true"
            >
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="modalAprovacaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            >
                <form @submit.prevent="aprovar">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Aprovar {{ formatarTipoEntidade() }}
                                </h3>
                                <div class="mt-4">
                                    <div class="mb-4">
                                        <label for="observacao_aprovacao" class="block text-sm font-medium text-gray-700">
                                            Observação (opcional)
                                        </label>
                                        <div class="mt-1">
                                            <textarea 
                                                id="observacao_aprovacao" 
                                                x-model="observacao" 
                                                rows="3" 
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                placeholder="Adicione uma observação (opcional)..."
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            <svg x-show="processando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirmar Aprovação
                        </button>
                        <button 
                            @click="fecharModalAprovacao"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de rejeição -->
    <div 
        x-show="modalRejeicaoAberto" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div 
                x-show="modalRejeicaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity" 
                aria-hidden="true"
            >
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="modalRejeicaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            >
                <form @submit.prevent="rejeitar">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Rejeitar {{ formatarTipoEntidade() }}
                                </h3>
                                <div class="mt-4">
                                    <div class="mb-4">
                                        <label for="justificativa_rejeicao" class="block text-sm font-medium text-gray-700">
                                            Justificativa<span x-show="requerJustificativa" class="text-red-600">*</span>
                                        </label>
                                        <div class="mt-1">
                                            <textarea 
                                                id="justificativa_rejeicao" 
                                                x-model="justificativa" 
                                                rows="3" 
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                :class="{ 'border-red-300': erroJustificativa }"
                                                placeholder="Informe o motivo da rejeição..."
                                                :required="requerJustificativa"
                                            ></textarea>
                                        </div>
                                        <p x-show="erroJustificativa" class="mt-1 text-sm text-red-600">
                                            A justificativa é obrigatória para rejeição.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            <svg x-show="processando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirmar Rejeição
                        </button>
                        <button 
                            @click="fecharModalRejeicao"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de observação -->
    <div 
        x-show="modalObservacaoAberto" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div 
                x-show="modalObservacaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity" 
                aria-hidden="true"
            >
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="modalObservacaoAberto" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            >
                <form @submit.prevent="adicionarObservacao">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Adicionar Observação
                                </h3>
                                <div class="mt-4">
                                    <div class="mb-4">
                                        <label for="texto_observacao" class="block text-sm font-medium text-gray-700">
                                            Observação <span class="text-red-600">*</span>
                                        </label>
                                        <div class="mt-1">
                                            <textarea 
                                                id="texto_observacao" 
                                                x-model="observacao" 
                                                rows="3" 
                                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                :class="{ 'border-red-300': erroObservacao }"
                                                placeholder="Digite sua observação..."
                                                required
                                            ></textarea>
                                        </div>
                                        <p x-show="erroObservacao" class="mt-1 text-sm text-red-600">
                                            A observação não pode ficar em branco.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            <svg x-show="processando" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Adicionar
                        </button>
                        <button 
                            @click="fecharModalObservacao"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            :disabled="processando"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartaoAprovacao', (config) => ({
            entidadeTipo: config.entidadeTipo,
            entidadeId: config.entidadeId,
            status: config.status,
            etapas: config.etapas,
            urlAprovar: config.urlAprovar,
            urlRejeitar: config.urlRejeitar,
            urlObservacao: config.urlObservacao,
            podeAprovar: config.podeAprovar,
            podeAdicionarObservacao: config.podeAdicionarObservacao,
            requerJustificativa: config.requerJustificativa,
            valor: config.valor,
            exibeHistorico: config.exibeHistorico,
            historico: config.historico || [],
            
            // Estado dos modais
            modalAprovacaoAberto: false,
            modalRejeicaoAberto: false,
            modalObservacaoAberto: false,
            
            // Campos dos formulários
            observacao: '',
            justificativa: '',
            
            // Estados de erro
            erroJustificativa: false,
            erroObservacao: false,
            
            // Estado de processamento
            processando: false,
            
            // Métodos para abrir/fechar modais
            abrirModalAprovar() {
                this.modalAprovacaoAberto = true;
                this.observacao = '';
            },
            
            fecharModalAprovacao() {
                this.modalAprovacaoAberto = false;
                this.observacao = '';
            },
            
            abrirModalRejeitar() {
                this.modalRejeicaoAberto = true;
                this.justificativa = '';
                this.erroJustificativa = false;
            },
            
            fecharModalRejeicao() {
                this.modalRejeicaoAberto = false;
                this.justificativa = '';
                this.erroJustificativa = false;
            },
            
            abrirModalObservacao() {
                this.modalObservacaoAberto = true;
                this.observacao = '';
                this.erroObservacao = false;
            },
            
            fecharModalObservacao() {
                this.modalObservacaoAberto = false;
                this.observacao = '';
                this.erroObservacao = false;
            },
            
            // Métodos para ações
            aprovar() {
                if (this.processando) return;
                
                this.processando = true;
                
                fetch(this.urlAprovar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        observacao: this.observacao
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar status e histórico
                        this.status = data.status || 'aprovado';
                        
                        if (data.historico) {
                            this.historico = data.historico;
                        } else if (data.registro) {
                            this.historico.unshift(data.registro);
                        }
                        
                        // Fechar modal
                        this.fecharModalAprovacao();
                        
                        // Exibir notificação de sucesso (você pode implementar isso)
                        this.notificar('Aprovado com sucesso!', 'success');
                        
                        // Opcional: recarregar a página
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else {
                        this.notificar(data.message || 'Erro ao aprovar.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao aprovar:', error);
                    this.notificar('Erro ao processar a aprovação. Tente novamente.', 'error');
                })
                .finally(() => {
                    this.processando = false;
                });
            },
            
            rejeitar() {
                if (this.processando) return;
                
                // Validar justificativa se requerida
                if (this.requerJustificativa && !this.justificativa.trim()) {
                    this.erroJustificativa = true;
                    return;
                }
                
                this.processando = true;
                this.erroJustificativa = false;
                
                fetch(this.urlRejeitar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        justificativa: this.justificativa
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar status e histórico
                        this.status = data.status || 'rejeitado';
                        
                        if (data.historico) {
                            this.historico = data.historico;
                        } else if (data.registro) {
                            this.historico.unshift(data.registro);
                        }
                        
                        // Fechar modal
                        this.fecharModalRejeicao();
                        
                        // Exibir notificação de sucesso (você pode implementar isso)
                        this.notificar('Rejeitado com sucesso!', 'success');
                        
                        // Opcional: recarregar a página
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else {
                        this.notificar(data.message || 'Erro ao rejeitar.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao rejeitar:', error);
                    this.notificar('Erro ao processar a rejeição. Tente novamente.', 'error');
                })
                .finally(() => {
                    this.processando = false;
                });
            },
            
            adicionarObservacao() {
                if (this.processando) return;
                
                // Validar observação
                if (!this.observacao.trim()) {
                    this.erroObservacao = true;
                    return;
                }
                
                this.processando = true;
                this.erroObservacao = false;
                
                fetch(this.urlObservacao, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        observacao: this.observacao
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar histórico
                        if (data.historico) {
                            this.historico = data.historico;
                        } else if (data.registro) {
                            this.historico.unshift(data.registro);
                        }
                        
                        // Fechar modal
                        this.fecharModalObservacao();
                        
                        // Exibir notificação de sucesso (você pode implementar isso)
                        this.notificar('Observação adicionada com sucesso!', 'success');
                    } else {
                        this.notificar(data.message || 'Erro ao adicionar observação.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao adicionar observação:', error);
                    this.notificar('Erro ao processar a observação. Tente novamente.', 'error');
                })
                .finally(() => {
                    this.processando = false;
                });
            },
            
            // Helpers
            formatarTipoEntidade() {
                return match(this.entidadeTipo) {
                    'solicitacao' => 'Solicitação',
                    'pedido' => 'Pedido de Compra',
                    'orcamento' => 'Orçamento',
                    'nota_fiscal' => 'Nota Fiscal',
                    _ => 'Item'
                };
            },
            
            formatarData(data) {
                if (!data) return '';
                
                try {
                    return new Date(data).toLocaleDateString('pt-BR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return data;
                }
            },
            
            formatarAcao(acao) {
                return {
                    'aprovacao': 'Aprovação',
                    'rejeicao': 'Rejeição',
                    'observacao': 'Observação',
                    'cancelamento': 'Cancelamento',
                    'criacao': 'Criação'
                }[acao] || acao;
            },
            
            notificar(mensagem, tipo) {
                // Implementação simples de notificação
                // Você pode substituir por sua própria implementação
                if (window.toast) {
                    window.toast(mensagem, tipo);
                } else {
                    alert(mensagem);
                }
            }
        }));
    });
</script>
@endpush