<div 
    id="{{ $componenteId() }}" 
    class="relative {{ $classesCss }}"
    x-data="timelineProcesso({
        etapas: {{ json_encode($etapas) }},
        historico: {{ json_encode($historico) }},
        statusAtual: '{{ $statusAtual }}',
        etapasPrincipais: {{ $etapasPrincipais ? 'true' : 'false' }},
        exibeTimestamps: {{ $exibeTimestamps ? 'true' : 'false' }},
        exibeUsuarios: {{ $exibeUsuarios ? 'true' : 'false' }},
        exibeObservacoes: {{ $exibeObservacoes ? 'true' : 'false' }},
        expandidoInicial: {{ $expandidoInicial ? 'true' : 'false' }},
        orientacao: '{{ $orientacao }}'
    })"
>
    <!-- Cabeçalho com opção de expandir/recolher detalhes -->
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-700">Timeline do Processo</h3>
        
        <button 
            @click="expandido = !expandido"
            type="button"
            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg 
                :class="{'rotate-180': expandido}"
                class="mr-1 h-4 w-4 transform transition-transform duration-200" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            <span x-text="expandido ? 'Recolher Detalhes' : 'Mostrar Detalhes'"></span>
        </button>
    </div>

    <!-- Timeline de etapas - Versão Vertical (padrão) -->
    <div x-show="orientacao === 'vertical'" class="relative">
        <div class="absolute top-0 left-6 h-full w-0.5 bg-gray-200"></div>

        <div class="space-y-8">
            <template x-for="(etapa, index) in etapasFiltradas" :key="index">
                <div class="relative">
                    <!-- Marcador de status -->
                    <div 
                        class="absolute left-0 w-12 h-12 flex items-center justify-center rounded-full border-4"
                        :class="{
                            'bg-blue-100 border-blue-500 text-blue-500': etapa.cor === 'blue' && statusConcluido(etapa),
                            'bg-green-100 border-green-500 text-green-500': etapa.cor === 'green' && statusConcluido(etapa),
                            'bg-yellow-100 border-yellow-500 text-yellow-500': etapa.cor === 'yellow' && statusConcluido(etapa),
                            'bg-red-100 border-red-500 text-red-500': etapa.cor === 'red' && statusConcluido(etapa),
                            'bg-gray-100 border-gray-500 text-gray-500': etapa.cor === 'gray' && statusConcluido(etapa),
                            'bg-indigo-100 border-indigo-500 text-indigo-500': etapa.cor === 'indigo' && statusConcluido(etapa),
                            'bg-purple-100 border-purple-500 text-purple-500': etapa.cor === 'purple' && statusConcluido(etapa),
                            'bg-teal-100 border-teal-500 text-teal-500': etapa.cor === 'teal' && statusConcluido(etapa),
                            'bg-orange-100 border-orange-500 text-orange-500': etapa.cor === 'orange' && statusConcluido(etapa),
                            'bg-white border-gray-300 text-gray-400': !statusConcluido(etapa)
                        }"
                    >
                        <!-- Indicador de estado atual -->
                        <div 
                            x-show="etapa.valor === statusAtual"
                            class="absolute -top-1 -right-1 w-4 h-4 rounded-full animate-pulse"
                            :class="{
                                'bg-blue-500': etapa.cor === 'blue',
                                'bg-green-500': etapa.cor === 'green',
                                'bg-yellow-500': etapa.cor === 'yellow',
                                'bg-red-500': etapa.cor === 'red',
                                'bg-gray-500': etapa.cor === 'gray',
                                'bg-indigo-500': etapa.cor === 'indigo',
                                'bg-purple-500': etapa.cor === 'purple',
                                'bg-teal-500': etapa.cor === 'teal',
                                'bg-orange-500': etapa.cor === 'orange'
                            }"
                        ></div>
                        
                        <!-- Ícones da etapa -->
                        <template x-if="etapa.icone === 'document-add'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'document-search'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'check-circle'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'check-badge'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'x-circle'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'trash'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'document'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'clock'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'paper-airplane'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </template>
                        
                        <template x-if="etapa.icone === 'truck'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            </svg>
                        </template>
                        
                        <!-- Ícone padrão para outros tipos -->
                        <template x-if="!['document-add', 'document-search', 'check-circle', 'check-badge', 'x-circle', 'trash', 'document', 'clock', 'paper-airplane', 'truck'].includes(etapa.icone)">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </template>
                    </div>

                    <!-- Conteúdo da etapa -->
                    <div class="ml-16">
                        <h4 class="text-lg font-medium" :class="etapa.valor === statusAtual ? 'text-gray-900' : 'text-gray-700'">
                            <span x-text="etapa.label"></span>
                            
                            <!-- Badge de status atual -->
                            <span 
                                x-show="etapa.valor === statusAtual"
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                Atual
                            </span>
                        </h4>
                        
                        <p class="text-sm text-gray-500 mt-1" x-text="etapa.descricao"></p>
                        
                        <!-- Evento relacionado a esta etapa (se houver e estiver expandido) -->
                        <template x-if="expandido && statusConcluido(etapa)">
                            <div class="mt-2 bg-gray-50 rounded-md p-3">
                                <div class="flex justify-between items-start">
                                    <!-- Informações do usuário -->
                                    <div x-show="exibeUsuarios && eventoEtapa(etapa) && eventoEtapa(etapa).usuario_nome" class="text-sm">
                                        <span class="font-medium" x-text="eventoEtapa(etapa).usuario_nome || 'Sistema'"></span>
                                        <span class="text-gray-500" x-show="eventoEtapa(etapa).cargo">
                                            (<span x-text="eventoEtapa(etapa).cargo"></span>)
                                        </span>
                                    </div>
                                    
                                    <!-- Timestamp -->
                                    <div x-show="exibeTimestamps && eventoEtapa(etapa) && eventoEtapa(etapa).data" class="text-xs text-gray-500">
                                        <span x-text="formatarData(eventoEtapa(etapa).data)"></span>
                                    </div>
                                </div>
                                
                                <!-- Observação -->
                                <div 
                                    x-show="exibeObservacoes && eventoEtapa(etapa) && eventoEtapa(etapa).observacao" 
                                    class="mt-2 text-sm text-gray-700 bg-white p-2 rounded border border-gray-200"
                                >
                                    <span x-text="eventoEtapa(etapa).observacao"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Timeline de etapas - Versão Horizontal -->
    <div x-show="orientacao === 'horizontal'" class="relative">
        <div class="w-full h-0.5 bg-gray-200 absolute top-6 left-0 right-0"></div>
        
        <div class="relative flex justify-between">
            <template x-for="(etapa, index) in etapasFiltradas" :key="index">
                <div class="flex flex-col items-center relative">
                    <!-- Marcador de status -->
                    <div 
                        class="w-12 h-12 flex items-center justify-center rounded-full border-4 z-10"
                        :class="{
                            'bg-blue-100 border-blue-500 text-blue-500': etapa.cor === 'blue' && statusConcluido(etapa),
                            'bg-green-100 border-green-500 text-green-500': etapa.cor === 'green' && statusConcluido(etapa),
                            'bg-yellow-100 border-yellow-500 text-yellow-500': etapa.cor === 'yellow' && statusConcluido(etapa),
                            'bg-red-100 border-red-500 text-red-500': etapa.cor === 'red' && statusConcluido(etapa),
                            'bg-gray-100 border-gray-500 text-gray-500': etapa.cor === 'gray' && statusConcluido(etapa),
                            'bg-indigo-100 border-indigo-500 text-indigo-500': etapa.cor === 'indigo' && statusConcluido(etapa),
                            'bg-purple-100 border-purple-500 text-purple-500': etapa.cor === 'purple' && statusConcluido(etapa),
                            'bg-teal-100 border-teal-500 text-teal-500': etapa.cor === 'teal' && statusConcluido(etapa),
                            'bg-orange-100 border-orange-500 text-orange-500': etapa.cor === 'orange' && statusConcluido(etapa),
                            'bg-white border-gray-300 text-gray-400': !statusConcluido(etapa)
                        }"
                    >
                        <!-- Indicador de estado atual -->
                        <div 
                            x-show="etapa.valor === statusAtual"
                            class="absolute -top-1 -right-1 w-4 h-4 rounded-full animate-pulse"
                            :class="{
                                'bg-blue-500': etapa.cor === 'blue',
                                'bg-green-500': etapa.cor === 'green',
                                'bg-yellow-500': etapa.cor === 'yellow',
                                'bg-red-500': etapa.cor === 'red',
                                'bg-gray-500': etapa.cor === 'gray',
                                'bg-indigo-500': etapa.cor === 'indigo',
                                'bg-purple-500': etapa.cor === 'purple',
                                'bg-teal-500': etapa.cor === 'teal',
                                'bg-orange-500': etapa.cor === 'orange'
                            }"
                        ></div>
                        
                        <!-- Ícone simplificado para visualização horizontal -->
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>

                    <!-- Label da etapa -->
                    <div class="mt-2 text-center max-w-xs px-2">
                        <h4 class="text-sm font-medium" :class="etapa.valor === statusAtual ? 'text-gray-900' : 'text-gray-700'">
                            <span x-text="etapa.label"></span>
                        </h4>
                        
                        <!-- Data se expandido -->
                        <p 
                            x-show="expandido && exibeTimestamps && eventoEtapa(etapa) && eventoEtapa(etapa).data" 
                            class="text-xs text-gray-500 mt-1"
                            x-text="formatarData(eventoEtapa(etapa).data, true)"
                        ></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('timelineProcesso', (config) => ({
            etapas: config.etapas || [],
            historico: config.historico || [],
            statusAtual: config.statusAtual || '',
            etapasPrincipais: config.etapasPrincipais,
            exibeTimestamps: config.exibeTimestamps,
            exibeUsuarios: config.exibeUsuarios,
            exibeObservacoes: config.exibeObservacoes,
            expandido: config.expandidoInicial,
            orientacao: config.orientacao || 'vertical',
            
            // Obter apenas as etapas principais (se configurado)
            get etapasFiltradas() {
                if (!this.etapasPrincipais) {
                    return this.etapas;
                }
                
                return this.etapas.filter(etapa => etapa.principal);
            },
            
            // Verificar se um status está concluído
            statusConcluido(etapa) {
                if (!this.statusAtual) {
                    return false;
                }
                
                // Verificar no histórico se existe evento para esta etapa
                if (this.historico.length > 0) {
                    const evento = this.eventoEtapa(etapa);
                    if (evento) {
                        return true;
                    }
                }
                
                // Caso não encontre no histórico, verifica a posição da etapa
                const etapaAtualIndex = this.etapas.findIndex(e => e.valor === this.statusAtual);
                const etapaIndex = this.etapas.findIndex(e => e.valor === etapa.valor);
                
                return etapaIndex <= etapaAtualIndex;
            },
            
            // Obter evento do histórico para uma etapa
            eventoEtapa(etapa) {
                if (!etapa || !this.historico || this.historico.length === 0) {
                    return null;
                }
                
                // Buscar evento que corresponde ao status/ação da etapa
                return this.historico.find(evento => {
                    return (evento.status === etapa.valor) || 
                           (evento.acao === etapa.valor) || 
                           (evento.valor === etapa.valor);
                });
            },
            
            // Formatar data
            formatarData(data, curto = false) {
                if (!data) return '';
                
                try {
                    const dataObj = new Date(data);
                    
                    if (curto) {
                        return dataObj.toLocaleDateString('pt-BR');
                    }
                    
                    return dataObj.toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric', 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                } catch (e) {
                    return data;
                }
            }
        }));
    });
</script>
@endpush