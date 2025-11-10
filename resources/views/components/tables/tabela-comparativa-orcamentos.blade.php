<div 
    id="{{ $componenteId() }}" 
    class="border border-gray-200 rounded-lg shadow-sm {{ $classesCss }}"
    x-data="tabelaComparativoOrcamentos({
        orcamentos: {{ json_encode($orcamentos) }},
        criterios: {{ json_encode($criterios) }},
        urlSelecionar: '{{ $urlSelecionar }}',
        urlRemover: '{{ $urlRemover }}',
        podeSelecionarOrcamento: {{ $podeSelecionarOrcamento ? 'true' : 'false' }},
        exibeTotal: {{ $exibeTotal ? 'true' : 'false' }},
        exibeUnitarios: {{ $exibeUnitarios ? 'true' : 'false' }},
        destacaMelhorOrcamento: {{ $destacaMelhorOrcamento ? 'true' : 'false' }},
        permiteExpandirDetalhes: {{ $permiteExpandirDetalhes ? 'true' : 'false' }}
    })"
>
    <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-700">Comparativo de Orçamentos</h3>
        <span class="text-sm text-gray-500">
            {{ $orcamentos->count() }} orçamento(s) disponível(is)
        </span>
    </div>

    <!-- Conteúdo principal -->
    <div class="p-4">
        <!-- Mensagem quando não há orçamentos -->
        <div x-show="orcamentos.length === 0" class="p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum orçamento registrado</h3>
            <p class="mt-1 text-sm text-gray-500">
                Não existem orçamentos registrados para comparação.
            </p>
        </div>

        <!-- Tabela comparativa -->
        <div x-show="orcamentos.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                            Critério
                        </th>
                        <template x-for="(orcamento, index) in orcamentos" :key="orcamento.id">
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="ehMelhorOrcamento(orcamento) ? 'bg-green-50 text-green-700' : 'text-gray-500'"
                            >
                                <div class="flex flex-col">
                                    <span x-text="orcamento.fornecedor_nome || 'Fornecedor ' + (index + 1)"></span>
                                    <span class="text-xs font-normal mt-1" x-text="formatarData(orcamento.data_orcamento)"></span>
                                    
                                    <div x-show="ehMelhorOrcamento(orcamento)" class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Melhor Opção
                                    </div>
                                    
                                    <div x-show="orcamento.selecionado" class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Selecionado
                                    </div>
                                </div>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Critérios de comparação -->
                    <template x-for="(criterio, criterioIndex) in criterios" :key="criterioIndex">
                        <tr :class="{'bg-gray-50': criterioIndex % 2 === 0}">
                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                <div class="flex items-center">
                                    <span x-text="criterio.titulo"></span>
                                    <span x-show="criterio.peso" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Peso: <span x-text="criterio.peso" class="ml-1"></span>
                                    </span>
                                </div>
                            </td>
                            <template x-for="(orcamento, orcamentoIndex) in orcamentos" :key="orcamento.id">
                                <td class="px-4 py-3 text-sm"
                                    :class="ehMelhorValorParaCriterio(orcamento, criterio) ? 'font-medium text-green-700 bg-green-50' : 'text-gray-900'"
                                >
                                    <template x-if="criterio.tipo === 'moeda'">
                                        <span x-text="formatarMoeda(orcamento[criterio.campo])"></span>
                                    </template>
                                    
                                    <template x-if="criterio.tipo === 'numero'">
                                        <span x-text="formatarNumero(orcamento[criterio.campo])"></span>
                                    </template>
                                    
                                    <template x-if="criterio.tipo === 'data'">
                                        <span x-text="formatarData(orcamento[criterio.campo])"></span>
                                    </template>
                                    
                                    <template x-if="criterio.tipo === 'texto' || !criterio.tipo">
                                        <span x-text="orcamento[criterio.campo] || '-'"></span>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                    
                    <!-- Linha para pontuação total calculada -->
                    <tr class="bg-gray-100 border-t-2 border-gray-300">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            Pontuação Total
                        </td>
                        <template x-for="(orcamento, index) in orcamentos" :key="orcamento.id">
                            <td class="px-4 py-3 text-sm font-bold"
                                :class="ehMelhorOrcamento(orcamento) ? 'text-green-700 bg-green-50' : 'text-gray-900'"
                            >
                                <span x-text="formatarNumero(calcularPontuacaoOrcamento(orcamento))"></span>
                            </td>
                        </template>
                    </tr>
                    
                    <!-- Botões de ação -->
                    <tr x-show="podeSelecionarOrcamento" class="bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500">
                            Ações
                        </td>
                        <template x-for="(orcamento, index) in orcamentos" :key="orcamento.id">
                            <td class="px-4 py-3 text-sm">
                                <template x-if="!orcamento.selecionado">
                                    <button 
                                        @click="selecionarOrcamento(orcamento)"
                                        type="button"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        :class="ehMelhorOrcamento(orcamento) ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : ''"
                                    >
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Selecionar
                                    </button>
                                </template>
                                <template x-if="orcamento.selecionado">
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-800">
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Selecionado
                                    </span>
                                </template>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Seção para detalhes de itens (expandível) -->
        <div x-show="orcamentos.length > 0 && permiteExpandirDetalhes" class="mt-6">
            <div class="flex items-center mb-3">
                <button 
                    @click="detalhesExpandidos = !detalhesExpandidos"
                    type="button"
                    class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 focus:outline-none"
                >
                    <svg 
                        class="h-5 w-5 mr-1 transition-transform duration-200" 
                        :class="detalhesExpandidos ? 'transform rotate-90' : ''"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24" 
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span x-text="detalhesExpandidos ? 'Ocultar detalhes dos itens' : 'Mostrar detalhes dos itens'"></span>
                </button>
            </div>
            
            <div x-show="detalhesExpandidos" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item
                            </th>
                            <template x-for="(orcamento, index) in orcamentos" :key="orcamento.id">
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    :class="ehMelhorOrcamento(orcamento) ? 'bg-green-50' : ''"
                                >
                                    <span x-text="orcamento.fornecedor_nome || 'Fornecedor ' + (index + 1)"></span>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(item, itemIndex) in obterListaItens()" :key="itemIndex">
                            <tr :class="{'bg-gray-50': itemIndex % 2 === 0}">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium" x-text="item.descricao"></div>
                                    <div class="text-xs text-gray-500" x-text="'Código: ' + item.codigo"></div>
                                    <div class="text-xs text-gray-500" x-text="'Qtde: ' + formatarNumero(item.quantidade) + ' ' + item.unidade"></div>
                                </td>
                                <template x-for="(orcamento, orcamentoIndex) in orcamentos" :key="orcamento.id">
                                    <td class="px-4 py-3 text-sm text-gray-900" :class="ehMelhorOrcamento(orcamento) ? 'bg-green-50' : ''">
                                        <template x-if="temItemNoOrcamento(orcamento, item)">
                                            <div>
                                                <div class="font-medium" x-text="formatarMoeda(obterValorItemNoOrcamento(orcamento, item))"></div>
                                                <div x-show="exibeUnitarios" class="text-xs text-gray-500" x-text="'Unit.: ' + formatarMoeda(obterValorUnitarioItemNoOrcamento(orcamento, item))"></div>
                                            </div>
                                        </template>
                                        <template x-if="!temItemNoOrcamento(orcamento, item)">
                                            <div class="text-gray-400">Não cotado</div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tabelaComparativoOrcamentos', (config) => ({
            orcamentos: config.orcamentos || [],
            criterios: config.criterios || [],
            urlSelecionar: config.urlSelecionar,
            urlRemover: config.urlRemover,
            podeSelecionarOrcamento: config.podeSelecionarOrcamento,
            exibeTotal: config.exibeTotal,
            exibeUnitarios: config.exibeUnitarios,
            destacaMelhorOrcamento: config.destacaMelhorOrcamento,
            permiteExpandirDetalhes: config.permiteExpandirDetalhes,
            
            // Estado interno
            detalhesExpandidos: false,
            processando: false,
            pontuacoes: {},
            
            init() {
                // Calcular pontuações na inicialização
                this.calcularPontuacoes();
            },
            
            // Calcular pontuações para todos os orçamentos
            calcularPontuacoes() {
                this.pontuacoes = {};
                
                if (this.orcamentos.length === 0 || this.criterios.length === 0) {
                    return;
                }
                
                // Para cada critério, calcular valores relativos e pontuações
                this.criterios.forEach(criterio => {
                    if (!criterio.melhor || !criterio.campo) return;
                    
                    const campo = criterio.campo;
                    const melhor = criterio.melhor;
                    const peso = criterio.peso || 1;
                    
                    // Coletar valores disponíveis para este critério
                    const valores = this.orcamentos
                        .map(o => o[campo])
                        .filter(v => v !== undefined && v !== null && v !== '');
                    
                    if (valores.length === 0) return;
                    
                    // Determinar o melhor valor
                    const melhorValor = melhor === 'menor' 
                        ? Math.min(...valores) 
                        : Math.max(...valores);
                    
                    // Atribuir pontuação para cada orçamento
                    this.orcamentos.forEach(orcamento => {
                        const valor = orcamento[campo];
                        if (valor === undefined || valor === null || valor === '') return;
                        
                        const id = orcamento.id;
                        if (!this.pontuacoes[id]) {
                            this.pontuacoes[id] = 0;
                        }
                        
                        // Calcular pontuação
                        if (melhor === 'menor') {
                            // Para critérios onde menor é melhor (ex: preço, prazo)
                            this.pontuacoes[id] += (melhorValor / valor) * peso;
                        } else {
                            // Para critérios onde maior é melhor (ex: validade, garantia)
                            this.pontuacoes[id] += (valor / melhorValor) * peso;
                        }
                    });
                });
            },
            
            // Calcular pontuação para um orçamento específico
            calcularPontuacaoOrcamento(orcamento) {
                return this.pontuacoes[orcamento.id] || 0;
            },
            
            // Verificar se um orçamento é o melhor (maior pontuação)
            ehMelhorOrcamento(orcamento) {
                if (!this.destacaMelhorOrcamento || this.orcamentos.length <= 1) {
                    return false;
                }
                
                // Encontrar o orçamento com maior pontuação
                const pontuacoes = Object.entries(this.pontuacoes)
                    .sort((a, b) => b[1] - a[1]); // Ordenar por pontuação decrescente
                
                if (pontuacoes.length === 0) return false;
                
                const melhorOrcamentoId = pontuacoes[0][0];
                return orcamento.id.toString() === melhorOrcamentoId.toString();
            },
            
            // Verificar se este orçamento tem o melhor valor para um critério específico
            ehMelhorValorParaCriterio(orcamento, criterio) {
                if (!criterio.melhor || !criterio.campo) return false;
                
                const campo = criterio.campo;
                const melhor = criterio.melhor;
                
                // Verificar se o orçamento tem valor para este campo
                const valor = orcamento[campo];
                if (valor === undefined || valor === null || valor === '') return false;
                
                // Coletar valores disponíveis para este critério
                const valores = this.orcamentos
                    .map(o => o[campo])
                    .filter(v => v !== undefined && v !== null && v !== '');
                
                if (valores.length === 0) return false;
                
                // Determinar o melhor valor
                const melhorValor = melhor === 'menor' 
                    ? Math.min(...valores) 
                    : Math.max(...valores);
                
                return valor === melhorValor;
            },
            
            // Obter lista unificada de itens de todos os orçamentos
            obterListaItens() {
                if (this.orcamentos.length === 0) return [];
                
                // Criar um mapa de todos os itens de todos os orçamentos
                const itensMap = new Map();
                
                this.orcamentos.forEach(orcamento => {
                    const itens = orcamento.itens || [];
                    
                    itens.forEach(item => {
                        // Usar código como chave única
                        const chave = item.codigo || item.produto_codigo || item.servico_codigo;
                        if (!chave) return;
                        
                        if (!itensMap.has(chave)) {
                            itensMap.set(chave, {
                                codigo: chave,
                                descricao: item.descricao || item.produto_descricao || item.servico_descricao,
                                quantidade: item.quantidade,
                                unidade: item.unidade
                            });
                        }
                    });
                });
                
                // Converter mapa para array
                return Array.from(itensMap.values());
            },
            
            // Verificar se um orçamento tem um determinado item
            temItemNoOrcamento(orcamento, item) {
                if (!orcamento.itens || !item.codigo) return false;
                
                return orcamento.itens.some(i => 
                    (i.codigo === item.codigo) || 
                    (i.produto_codigo === item.codigo) || 
                    (i.servico_codigo === item.codigo)
                );
            },
            
            // Obter o valor total de um item em um orçamento
            obterValorItemNoOrcamento(orcamento, item) {
                if (!orcamento.itens || !item.codigo) return 0;
                
                const itemNoOrcamento = orcamento.itens.find(i => 
                    (i.codigo === item.codigo) || 
                    (i.produto_codigo === item.codigo) || 
                    (i.servico_codigo === item.codigo)
                );
                
                if (!itemNoOrcamento) return 0;
                
                return itemNoOrcamento.valor_total || 
                       (itemNoOrcamento.quantidade * itemNoOrcamento.valor_unitario) || 
                       0;
            },
            
            // Obter o valor unitário de um item em um orçamento
            obterValorUnitarioItemNoOrcamento(orcamento, item) {
                if (!orcamento.itens || !item.codigo) return 0;
                
                const itemNoOrcamento = orcamento.itens.find(i => 
                    (i.codigo === item.codigo) || 
                    (i.produto_codigo === item.codigo) || 
                    (i.servico_codigo === item.codigo)
                );
                
                if (!itemNoOrcamento) return 0;
                
                return itemNoOrcamento.valor_unitario || 0;
            },
            
            // Selecionar um orçamento
            selecionarOrcamento(orcamento) {
                if (this.processando || !this.podeSelecionarOrcamento) return;
                
                if (!confirm(`Deseja selecionar o orçamento de ${orcamento.fornecedor_nome || 'Fornecedor'}?`)) {
                    return;
                }
                
                this.processando = true;
                
                const url = this.urlSelecionar.replace('_id_placeholder', orcamento.id);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualizar a UI marcando o orçamento como selecionado
                        this.orcamentos.forEach(o => {
                            o.selecionado = o.id === orcamento.id;
                        });
                        
                        // Notificar sucesso
                        this.notificar('Orçamento selecionado com sucesso!', 'success');
                        
                        // Redirecionar se necessário
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else {
                        this.notificar(data.message || 'Erro ao selecionar orçamento.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro ao selecionar orçamento:', error);
                    this.notificar('Erro ao selecionar orçamento. Tente novamente.', 'error');
                })
                .finally(() => {
                    this.processando = false;
                });
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