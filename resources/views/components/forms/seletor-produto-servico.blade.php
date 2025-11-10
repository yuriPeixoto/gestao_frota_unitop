<div 
    id="{{ $componenteId() }}" 
    class="relative {{ $classesCss }}"
    x-data="seletorProdutoServico({
        tipo: '{{ $tipo }}',
        urlBusca: '{{ $urlBusca }}',
        inputId: '{{ $inputId }}',
        inputName: '{{ $inputName }}',
        placeholder: '{{ $placeholder }}',
        required: {{ $required ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        multiplo: {{ $multiplo ? 'true' : 'false' }},
        exibeDetalhes: {{ $exibeDetalhes ? 'true' : 'false' }},
        camposAdicionais: {{ json_encode($camposAdicionais) }},
        valoresSelecionados: {{ json_encode($valoresSelecionados()) }},
        textosSelecionados: {{ json_encode($textosSelecionados()) }},
        mensagemErro: '{{ $mensagemErro }}',
        onSelect: {{ $onSelect ? "'{$onSelect}'" : 'null' }}
    })"
    x-init="init()"
    @click.away="fecharListaResultados()"
>
    <label 
        :for="inputId" 
        class="block text-sm font-medium text-gray-700 mb-1"
    >
        {{ $labelTipo() }}
        <span x-show="required" class="text-red-600">*</span>
    </label>
    
    <!-- Input para busca de itens -->
    <div class="relative">
        <input 
            type="text" 
            :id="inputId + '_busca'" 
            x-model="termoBusca" 
            :placeholder="placeholder" 
            :disabled="disabled" 
            @focus="abrirListaResultados"
            @keydown.enter.prevent="buscarItens"
            @keydown.arrow-down.prevent="selecionarProximoResultado"
            @keydown.arrow-up.prevent="selecionarResultadoAnterior"
            @keydown.escape.prevent="fecharListaResultados"
            @keydown.tab="fecharListaResultados"
            @input="debounceBusca"
            class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{'border-red-300 focus:ring-red-500 focus:border-red-500': mensagemErro}"
        >
        
        <!-- Ícone de busca ou loading -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <!-- Indicador de loading -->
            <svg 
                x-show="buscando" 
                class="animate-spin h-5 w-5 text-gray-400" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            
            <!-- Ícone de busca -->
            <svg 
                x-show="!buscando" 
                class="h-5 w-5 text-gray-400" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24" 
                xmlns="http://www.w3.org/2000/svg"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Mensagem de erro -->
    <p 
        x-show="mensagemErro" 
        x-text="mensagemErro" 
        class="mt-1 text-sm text-red-600"
    ></p>
    
    <!-- Lista de itens selecionados (para seleção múltipla) -->
    <div x-show="multiplo && itensSelecionados.length > 0" class="mt-2">
        <h4 class="text-xs font-medium text-gray-500 mb-1">Itens selecionados:</h4>
        <div class="flex flex-wrap gap-2">
            <template x-for="(item, index) in itensSelecionados" :key="index">
                <div class="inline-flex items-center px-2 py-1 rounded-md text-sm bg-blue-100 text-blue-800">
                    <span x-text="item.texto"></span>
                    <button 
                        @click="removerItemSelecionado(index)"
                        type="button"
                        class="ml-1 text-blue-500 hover:text-blue-700 focus:outline-none"
                        :disabled="disabled"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Lista de resultados da busca -->
    <div 
        x-show="mostrarListaResultados" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md max-h-72 overflow-auto"
        style="display: none;"
    >
        <ul class="py-1" role="menu" aria-orientation="vertical">
            <!-- Mensagem quando não há resultados -->
            <li 
                x-show="resultados.length === 0 && !buscando && termoBusca.length > 0" 
                class="px-4 py-3 text-sm text-gray-500"
            >
                Nenhum resultado encontrado.
            </li>
            
            <!-- Mensagem de ajuda quando não há pesquisa -->
            <li 
                x-show="resultados.length === 0 && !buscando && termoBusca.length === 0" 
                class="px-4 py-3 text-sm text-gray-500"
            >
                Digite para buscar...
            </li>
            
            <!-- Mensagem de carregamento -->
            <li 
                x-show="buscando" 
                class="px-4 py-3 text-sm text-gray-500"
            >
                <div class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Buscando...
                </div>
            </li>
            
            <!-- Resultados da busca -->
            <template x-for="(item, index) in resultados" :key="item.id">
                <li>
                    <a 
                        href="#" 
                        @click.prevent="selecionarItem(item)"
                        class="block px-4 py-2 text-sm cursor-pointer hover:bg-gray-100"
                        :class="{'bg-gray-100': indexSelecionado === index}"
                        role="menuitem"
                    >
                        <div class="flex flex-col">
                            <div class="font-medium" x-text="formatarTextoItem(item)"></div>
                            
                            <!-- Detalhes adicionais (se habilitado) -->
                            <div x-show="exibeDetalhes" class="text-xs text-gray-500 mt-1">
                                <!-- Tipo do item (produto ou serviço) -->
                                <span 
                                    x-show="item.tipo" 
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    :class="{
                                        'bg-blue-100 text-blue-800': item.tipo === 'produto',
                                        'bg-green-100 text-green-800': item.tipo === 'servico'
                                    }"
                                    x-text="item.tipo === 'produto' ? 'Produto' : 'Serviço'"
                                ></span>
                                
                                <!-- Unidade -->
                                <span x-show="item.unidade" class="ml-2" x-text="'Unid: ' + item.unidade"></span>
                                
                                <!-- Campo para valor unitário (se disponível) -->
                                <span x-show="item.valor_unitario" class="ml-2" x-text="'Valor: ' + formatarMoeda(item.valor_unitario)"></span>
                                
                                <!-- Campos adicionais configurados -->
                                <template x-for="(campo, campoIndex) in camposAdicionais" :key="campoIndex">
                                    <span 
                                        x-show="item[campo.campo]" 
                                        class="ml-2"
                                        x-text="(campo.label || campo.campo) + ': ' + formatarValorCampo(item[campo.campo], campo.tipo)"
                                    ></span>
                                </template>
                            </div>
                        </div>
                    </a>
                </li>
            </template>
        </ul>
    </div>
    
    <!-- Input oculto para armazenar os valores selecionados -->
    <template x-if="!multiplo">
        <input 
            :id="inputId" 
            :name="inputName" 
            type="hidden" 
            :value="itensSelecionados.length > 0 ? itensSelecionados[0].valor : ''"
            :required="required"
        >
    </template>
    
    <!-- Inputs para seleção múltipla -->
    <template x-if="multiplo">
        <div>
            <template x-for="(item, index) in itensSelecionados" :key="index">
                <input 
                    :name="inputName + '[]'" 
                    type="hidden" 
                    :value="item.valor"
                >
            </template>
            <!-- Input para validação de required -->
            <input 
                :id="inputId" 
                type="hidden" 
                :required="required && itensSelecionados.length === 0"
            >
        </div>
    </template>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('seletorProdutoServico', (config) => ({
            tipo: config.tipo || 'ambos',
            urlBusca: config.urlBusca,
            inputId: config.inputId,
            inputName: config.inputName,
            placeholder: config.placeholder,
            required: config.required,
            disabled: config.disabled,
            multiplo: config.multiplo,
            exibeDetalhes: config.exibeDetalhes,
            camposAdicionais: config.camposAdicionais || [],
            
            // Estado interno
            termoBusca: '',
            resultados: [],
            buscando: false,
            mostrarListaResultados: false,
            indexSelecionado: -1,
            mensagemErro: config.mensagemErro || '',
            onSelect: config.onSelect,
            timeoutBusca: null,
            
            // Itens selecionados
            itensSelecionados: [],
            
            init() {
                // Inicializar itens selecionados a partir dos valores fornecidos
                this.inicializarItensSelecionados(config.valoresSelecionados, config.textosSelecionados);
            },
            
            // Inicializar os itens selecionados
            inicializarItensSelecionados(valores, textos) {
                if (!valores || valores.length === 0) {
                    this.itensSelecionados = [];
                    return;
                }
                
                this.itensSelecionados = valores.map((valor, index) => ({
                    valor: valor,
                    texto: textos[index] || `Item ${valor}`,
                    item: null // O objeto completo pode não estar disponível inicialmente
                }));
            },
            
            // Buscar itens no servidor (com debounce)
            debounceBusca() {
                if (this.timeoutBusca) {
                    clearTimeout(this.timeoutBusca);
                }
                
                this.timeoutBusca = setTimeout(() => {
                    if (this.termoBusca.trim().length > 0) {
                        this.buscarItens();
                    }
                }, 300);
            },
            
            // Buscar itens no servidor
            buscarItens() {
                if (this.termoBusca.trim().length === 0) {
                    this.resultados = [];
                    return;
                }
                
                this.buscando = true;
                
                // Construir URL com parâmetros
                const url = new URL(this.urlBusca);
                url.searchParams.append('termo', this.termoBusca);
                url.searchParams.append('tipo', this.tipo);
                
                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.resultados = data.resultados || [];
                    
                    // Mostrar a lista de resultados se houver resultados
                    if (this.resultados.length > 0) {
                        this.mostrarListaResultados = true;
                        this.indexSelecionado = 0; // Selecionar o primeiro resultado
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar itens:', error);
                    this.resultados = [];
                    this.mensagemErro = 'Erro ao buscar itens. Tente novamente.';
                })
                .finally(() => {
                    this.buscando = false;
                });
            },
            
            // Selecionar um item
            selecionarItem(item) {
                // Se não é múltiplo, substitui a seleção atual
                if (!this.multiplo) {
                    this.itensSelecionados = [{
                        valor: item.id,
                        texto: this.formatarTextoItem(item),
                        item: item
                    }];
                    
                    // Atualizar o campo de busca com o texto do item selecionado
                    this.termoBusca = this.formatarTextoItem(item);
                } else {
                    // Verificar se o item já está selecionado
                    const jaExiste = this.itensSelecionados.some(i => i.valor === item.id);
                    
                    if (!jaExiste) {
                        this.itensSelecionados.push({
                            valor: item.id,
                            texto: this.formatarTextoItem(item),
                            item: item
                        });
                    }
                    
                    // Limpar o campo de busca
                    this.termoBusca = '';
                }
                
                // Fechar a lista de resultados
                this.fecharListaResultados();
                
                // Limpar a mensagem de erro se houver
                this.mensagemErro = '';
                
                // Chamar a função de callback onSelect, se fornecida
                if (this.onSelect) {
                    const fn = window[this.onSelect];
                    if (typeof fn === 'function') {
                        fn(item, this.itensSelecionados);
                    }
                }
                
                // Disparar evento personalizado
                this.$el.dispatchEvent(new CustomEvent('item-selecionado', {
                    detail: {
                        item: item,
                        itensSelecionados: this.itensSelecionados
                    }
                }));
            },
            
            // Remover um item selecionado
            removerItemSelecionado(index) {
                if (this.disabled) return;
                
                this.itensSelecionados.splice(index, 1);
                
                // Disparar evento personalizado
                this.$el.dispatchEvent(new CustomEvent('item-removido', {
                    detail: {
                        indice: index,
                        itensSelecionados: this.itensSelecionados
                    }
                }));
            },
            
            // Abrir a lista de resultados
            abrirListaResultados() {
                if (this.disabled) return;
                
                this.mostrarListaResultados = true;
                
                // Se houver termo de busca, realizar a busca imediatamente
                if (this.termoBusca.trim().length > 0) {
                    this.buscarItens();
                }
            },
            
            // Fechar a lista de resultados
            fecharListaResultados() {
                this.mostrarListaResultados = false;
                this.indexSelecionado = -1;
            },
            
            // Selecionar o próximo resultado na lista
            selecionarProximoResultado() {
                if (this.resultados.length === 0) return;
                
                this.indexSelecionado = (this.indexSelecionado + 1) % this.resultados.length;
                this.scrollParaResultadoSelecionado();
            },
            
            // Selecionar o resultado anterior na lista
            selecionarResultadoAnterior() {
                if (this.resultados.length === 0) return;
                
                this.indexSelecionado = (this.indexSelecionado - 1 + this.resultados.length) % this.resultados.length;
                this.scrollParaResultadoSelecionado();
            },
            
            // Rolar a lista para mostrar o resultado selecionado
            scrollParaResultadoSelecionado() {
                if (this.indexSelecionado === -1) return;
                
                this.$nextTick(() => {
                    const listaResultados = this.$el.querySelector('ul');
                    const itemSelecionado = listaResultados.children[this.indexSelecionado + 3]; // +3 para pular os itens de mensagem
                    
                    if (itemSelecionado) {
                        itemSelecionado.scrollIntoView({
                            block: 'nearest'
                        });
                    }
                });
            },
            
            // Formatar texto do item para exibição
            formatarTextoItem(item) {
                const codigo = item.codigo || item.produto_codigo || item.servico_codigo || '';
                const descricao = item.descricao || item.produto_descricao || item.servico_descricao || '';
                
                if (codigo && descricao) {
                    return `${codigo} - ${descricao}`;
                }
                
                if (descricao) {
                    return descricao;
                }
                
                if (codigo) {
                    return codigo;
                }
                
                return item.id ? `Item ${item.id}` : 'Item sem identificação';
            },
            
            // Formatar valor de campo de acordo com o tipo
            formatarValorCampo(valor, tipo) {
                if (valor === undefined || valor === null) {
                    return '-';
                }
                
                if (tipo === 'moeda') {
                    return this.formatarMoeda(valor);
                }
                
                if (tipo === 'data') {
                    return this.formatarData(valor);
                }
                
                if (tipo === 'numero') {
                    return this.formatarNumero(valor);
                }
                
                return valor.toString();
            },
            
            // Verificar se um item é o melhor para um determinado critério
            ehMelhorValorParaCriterio(orcamento, criterio) {
                if (!criterio.melhor || !criterio.campo) return false;
                
                const campo = criterio.campo;
                const melhor = criterio.melhor;
                
                // Verificar se o item tem valor para este campo
                const valor = item[campo];
                if (valor === undefined || valor === null || valor === '') return false;
                
                // Coletar valores disponíveis para este critério
                const valores = this.resultados
                    .map(o => o[campo])
                    .filter(v => v !== undefined && v !== null && v !== '');
                
                if (valores.length === 0) return false;
                
                // Determinar o melhor valor
                const melhorValor = melhor === 'menor' 
                    ? Math.min(...valores) 
                    : Math.max(...valores);
                
                return valor === melhorValor;
            },
            
            // Formatadores
            formatarMoeda(valor) {
                return new Intl.NumberFormat('pt-BR', { 
                    style: 'currency', 
                    currency: 'BRL' 
                }).format(valor || 0);
            },
            
            formatarNumero(valor) {
                return new Intl.NumberFormat('pt-BR', { 
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(valor || 0);
            },
            
            formatarData(data) {
                if (!data) return '';
                
                try {
                    return new Date(data).toLocaleDateString('pt-BR');
                } catch (e) {
                    return data;
                }
            }
        }));
    });
</script>
@endpush