# 🎯 Smart Select - Definindo Valores Programaticamente

Documentação completa para definir, manipular e gerenciar valores nos smart-selects de forma programática.

## 📑 Índice

- [Visão Geral](#-visão-geral)
- [Instalação](#-instalação)
- [Funções Básicas](#-funções-básicas)
- [Seleção Única](#-seleção-única)
- [Seleção Múltipla](#-seleção-múltipla)
- [Gerenciamento de Opções](#-gerenciamento-de-opções)
- [Configurações Avançadas](#-configurações-avançadas)
- [Exemplos Práticos](#-exemplos-práticos)
- [Integração com AJAX](#-integração-com-ajax)
- [Troubleshooting](#-troubleshooting)
- [API Completa](#-api-completa)

---

## 🎯 Visão Geral

O sistema de definição de valores permite **controle total** sobre os smart-selects, oferecendo:

### ✨ Características Principais

- ✅ **Definição por valor ou label** - Flexibilidade total na busca
- ✅ **Seleção única e múltipla** - Suporte completo para ambos os tipos
- ✅ **Operações incrementais** - Adicionar/remover valores específicos
- ✅ **Gerenciamento de opções** - Atualizar/adicionar opções dinamicamente
- ✅ **Eventos automáticos** - Disparar listeners configurados
- ✅ **Validação integrada** - Verificações automáticas de consistência
- ✅ **Fallbacks seguros** - Comportamento previsível em cenários de erro

### 🔧 Casos de Uso

- Carregar valores padrão em formulários
- Sincronizar selects dependentes via AJAX
- Implementar validações com correção automática
- Criar interfaces dinâmicas e reativas
- Integrar com APIs externas
- Implementar auto-complete e sugestões

---

## 🚀 Instalação

### Pré-requisitos

1. ✅ Smart-select component funcionando
2. ✅ SmartSelectListener instalado e ativo
3. ✅ Alpine.js carregado

### Adicionar Sistema de Definição

```php
{{-- No seu layout após o SmartSelectListener --}}
<script>
    // Cole aqui todo o código do sistema de definição de valores
    // ... código completo do artifact anterior
</script>
```

### Verificar Instalação

```javascript
// No console do navegador
console.log('Funções disponíveis:', {
    setSmartSelectValue: typeof setSmartSelectValue,
    clearSmartSelect: typeof clearSmartSelect,
    addToSmartSelect: typeof addToSmartSelect
});
```

---

## 📖 Funções Básicas

### `setSmartSelectValue(selectName, value, options)`

**Função principal** para definir valores em qualquer smart-select.

```javascript
// Sintaxe básica
setSmartSelectValue('categoria', '123');

// Com opções
setSmartSelectValue('categoria', '123', {
    triggerEvents: true,
    createIfNotFound: false
});
```

**Parâmetros:**
- `selectName` (string) - Nome do smart-select
- `value` (any) - Valor a ser definido (string, array, null)
- `options` (object, opcional) - Configurações adicionais

**Retorno:** `boolean` - true se sucesso, false se erro

### `clearSmartSelect(selectName)`

**Limpa** toda a seleção do smart-select.

```javascript
clearSmartSelect('categoria');
```

### `isValueSelected(selectName, value)`

**Verifica** se um valor específico está selecionado.

```javascript
if (isValueSelected('categoria', '123')) {
    console.log('Categoria 123 está selecionada');
}
```

---

## 🎯 Seleção Única

### Definir Valor

```javascript
// Por valor (ID)
setSmartSelectValue('categoria', '123');

// Por label/texto
setSmartSelectByLabel('categoria', 'Eletrônicos');

// Limpar seleção
setSmartSelectValue('categoria', null);
// ou
clearSmartSelect('categoria');
```

### Exemplos Práticos

```javascript
// 1. Definir valor padrão ao carregar página
document.addEventListener('DOMContentLoaded', function() {
    setSmartSelectValue('categoria', '123');
});

// 2. Definir baseado em outro campo
onSmartSelectChange('tipo_produto', function(data) {
    if (data.value === 'eletronico') {
        setSmartSelectValue('categoria', '1'); // Eletrônicos
    } else if (data.value === 'roupas') {
        setSmartSelectValue('categoria', '2'); // Vestuário
    }
});

// 3. Alternar entre valores
function alternarStatus() {
    const atual = getSmartSelectValue('status');
    const novoStatus = atual.value === '1' ? '0' : '1';
    setSmartSelectValue('status', novoStatus);
}

// 4. Definir com validação
function definirCategoria(categoriaId) {
    if (isValueSelected('categoria', categoriaId)) {
        console.log('Categoria já está selecionada');
        return;
    }
    
    const sucesso = setSmartSelectValue('categoria', categoriaId);
    if (!sucesso) {
        console.error('Falha ao definir categoria');
    }
}
```

---

## 🔢 Seleção Múltipla

### Operações Básicas

```javascript
// Definir múltiplos valores
setSmartSelectValue('produtos', ['123', '456', '789']);

// Adicionar um valor
addToSmartSelect('produtos', '999');

// Remover um valor
removeFromSmartSelect('produtos', '123');

// Alternar valor (adiciona se não tem, remove se tem)
toggleSmartSelectValue('produtos', '456');

// Limpar todos
clearSmartSelect('produtos');
```

### Operações Avançadas

```javascript
// Verificar se valor existe antes de adicionar
function adicionarProdutoSeguro(produtoId) {
    if (!isValueSelected('produtos', produtoId)) {
        addToSmartSelect('produtos', produtoId);
        console.log(`Produto ${produtoId} adicionado`);
    } else {
        console.log('Produto já estava selecionado');
    }
}

// Substituir um valor por outro
function substituirProduto(antigoId, novoId) {
    if (isValueSelected('produtos', antigoId)) {
        removeFromSmartSelect('produtos', antigoId);
        addToSmartSelect('produtos', novoId);
        console.log(`Produto ${antigoId} substituído por ${novoId}`);
    }
}

// Definir lista com limite máximo
function definirProdutosComLimite(produtoIds, limite = 5) {
    const produtosLimitados = produtoIds.slice(0, limite);
    setSmartSelectValue('produtos', produtosLimitados);
    
    if (produtoIds.length > limite) {
        console.warn(`Apenas ${limite} produtos foram selecionados de ${produtoIds.length}`);
    }
}

// Mover itens entre listas
function moverParaLista(produtoId, listaOrigem, listaDestino) {
    if (isValueSelected(listaOrigem, produtoId)) {
        removeFromSmartSelect(listaOrigem, produtoId);
        addToSmartSelect(listaDestino, produtoId);
        console.log(`Produto ${produtoId} movido de ${listaOrigem} para ${listaDestino}`);
    }
}
```

### Validações em Seleção Múltipla

```javascript
// Limitar quantidade de seleções
onSmartSelectChange('produtos', function(data) {
    const LIMITE_MAX = 5;
    
    if (data.values.length > LIMITE_MAX) {
        // Remover último item adicionado
        const ultimoItem = data.values[data.values.length - 1];
        removeFromSmartSelect('produtos', ultimoItem);
        
        alert(`Máximo ${LIMITE_MAX} produtos permitidos`);
    }
});

// Validar combinações não permitidas
onSmartSelectChange('produtos', function(data) {
    const produtosIncompativeis = [
        ['123', '456'], // Produto 123 não pode estar com 456
        ['789', '999']  // Produto 789 não pode estar com 999
    ];
    
    produtosIncompativeis.forEach(([produto1, produto2]) => {
        if (data.values.includes(produto1) && data.values.includes(produto2)) {
            removeFromSmartSelect('produtos', produto2);
            alert(`Produtos ${produto1} e ${produto2} são incompatíveis`);
        }
    });
});

// Selecionar automaticamente produtos dependentes
onSmartSelectChange('produtos', function(data) {
    const dependencias = {
        '123': ['456', '789'], // Se selecionar 123, adicionar 456 e 789
        '999': ['888']         // Se selecionar 999, adicionar 888
    };
    
    data.values.forEach(produtoId => {
        if (dependencias[produtoId]) {
            dependencias[produtoId].forEach(dependente => {
                if (!isValueSelected('produtos', dependente)) {
                    addToSmartSelect('produtos', dependente);
                }
            });
        }
    });
});
```

---

## 🛠️ Gerenciamento de Opções

### Atualizar Opções

```javascript
// Substituir todas as opções
updateSmartSelectOptions('categoria', novasCategorias, true); // true = preservar seleção

// Adicionar uma opção específica
addSmartSelectOption('categoria', {
    value: '999',
    label: 'Nova Categoria',
    codigo: 'NC001'
});

// Carregar opções via AJAX
async function carregarCategorias() {
    try {
        const response = await fetch('/api/categorias');
        const categorias = await response.json();
        
        updateSmartSelectOptions('categoria', categorias, false); // false = não preservar
        console.log(`${categorias.length} categorias carregadas`);
        
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}
```

### Opções Dinâmicas

```javascript
// Filtrar opções baseado em outro select
onSmartSelectChange('marca', function(data) {
    if (data.value) {
        // Carregar produtos da marca selecionada
        fetch(`/api/produtos?marca=${data.value}`)
            .then(response => response.json())
            .then(produtos => {
                updateSmartSelectOptions('produto', produtos);
                
                // Opcional: Selecionar primeiro produto
                if (produtos.length > 0) {
                    setSmartSelectValue('produto', produtos[0].id);
                }
            });
    } else {
        // Limpar produtos se nenhuma marca selecionada
        updateSmartSelectOptions('produto', []);
    }
});

// Adicionar opção "Criar Novo" dinamicamente
function adicionarOpcaoCriarNovo(selectName) {
    addSmartSelectOption(selectName, {
        value: 'criar_novo',
        label: '+ Criar Novo...',
        classe: 'option-criar-novo'
    });
}

// Remover opções temporárias
function limparOpcoesTemporarias(selectName) {
    const elemento = document.querySelector(`[x-data*="${selectName}"]`);
    if (elemento && elemento._x_dataStack) {
        const alpineData = elemento._x_dataStack[0];
        alpineData.options = alpineData.options.filter(opt => !opt.temporaria);
    }
}
```

---

## ⚙️ Configurações Avançadas

### Opções de Configuração

```javascript
const opcoes = {
    triggerEvents: true,        // Disparar eventos de mudança (padrão: true)
    updateLabel: true,          // Atualizar label automaticamente (padrão: true)
    forceUpdate: false,         // Forçar atualização mesmo se valor igual (padrão: false)
    findByValue: true,          // Buscar opção por valor (padrão: true)
    findByLabel: false,         // Buscar opção por label (padrão: false)
    createIfNotFound: false,    // Criar opção se não encontrar (padrão: false)
    tempLabel: null,            // Label para opções temporárias
    valueField: 'value',        // Campo usado como valor
    textField: 'label'          // Campo usado como texto
};

setSmartSelectValue('categoria', '123', opcoes);
```

### Exemplos de Configurações

```javascript
// 1. Definir sem disparar eventos (silencioso)
setSmartSelectValue('categoria', '123', {
    triggerEvents: false
});

// 2. Criar opção se não encontrar
setSmartSelectValue('categoria', '999', {
    createIfNotFound: true,
    tempLabel: 'Categoria Personalizada'
});

// 3. Buscar por label em vez de valor
setSmartSelectValue('categoria', 'Eletrônicos', {
    findByLabel: true,
    findByValue: false
});

// 4. Forçar atualização mesmo se valor igual
setSmartSelectValue('categoria', '123', {
    forceUpdate: true
});

// 5. Usar campos personalizados
setSmartSelectValue('categoria', '123', {
    valueField: 'id',
    textField: 'nome'
});
```

---

## 💼 Exemplos Práticos

### 1. Formulário de Cadastro com Dependências

```javascript
document.addEventListener('DOMContentLoaded', function() {
    
    // Configurar cascata Estado → Cidade
    onSmartSelectChange('estado', function(data) {
        // Limpar cidade atual
        clearSmartSelect('cidade');
        
        if (data.value) {
            // Carregar cidades do estado
            carregarCidades(data.value);
        }
    });
    
    // Configurar cascata Categoria → Subcategoria
    onSmartSelectChange('categoria', function(data) {
        clearSmartSelect('subcategoria');
        
        if (data.value) {
            carregarSubcategorias(data.value);
        }
    });
    
    // Função para carregar cidades
    async function carregarCidades(estadoId) {
        try {
            const response = await fetch(`/api/cidades?estado=${estadoId}`);
            const cidades = await response.json();
            
            updateSmartSelectOptions('cidade', cidades);
            console.log(`${cidades.length} cidades carregadas`);
            
        } catch (error) {
            console.error('Erro ao carregar cidades:', error);
        }
    }
    
    // Função para carregar subcategorias
    async function carregarSubcategorias(categoriaId) {
        try {
            const response = await fetch(`/api/subcategorias?categoria=${categoriaId}`);
            const subcategorias = await response.json();
            
            updateSmartSelectOptions('subcategoria', subcategorias);
            
            // Auto-selecionar se só houver uma opção
            if (subcategorias.length === 1) {
                setSmartSelectValue('subcategoria', subcategorias[0].id);
            }
            
        } catch (error) {
            console.error('Erro ao carregar subcategorias:', error);
        }
    }
    
});
```

### 2. Sistema de Filtros Dinâmicos

```javascript
// Sistema de filtros para listagem de produtos
class FiltrosProdutos {
    constructor() {
        this.filtros = {};
        this.configurarListeners();
    }
    
    configurarListeners() {
        // Listener para todos os filtros
        onMultipleSmartSelectChange({
            'filtro_categoria': (data) => this.atualizarFiltro('categoria', data.values),
            'filtro_marca': (data) => this.atualizarFiltro('marca', data.values),
            'filtro_preco': (data) => this.atualizarFiltro('preco', data.value),
            'filtro_disponibilidade': (data) => this.atualizarFiltro('disponivel', data.value)
        });
    }
    
    atualizarFiltro(tipo, valor) {
        this.filtros[tipo] = valor;
        this.aplicarFiltros();
    }
    
    async aplicarFiltros() {
        try {
            const params = new URLSearchParams();
            
            // Converter filtros para parâmetros de URL
            Object.entries(this.filtros).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(`${key}[]`, v));
                } else if (value) {
                    params.append(key, value);
                }
            });
            
            const response = await fetch(`/api/produtos?${params}`);
            const produtos = await response.json();
            
            this.atualizarListagem(produtos);
            
        } catch (error) {
            console.error('Erro ao aplicar filtros:', error);
        }
    }
    
    atualizarListagem(produtos) {
        // Atualizar interface com produtos filtrados
        console.log(`${produtos.length} produtos encontrados`);
        // ... implementar atualização da UI
    }
    
    limparFiltros() {
        this.filtros = {};
        
        // Limpar todos os selects
        clearSmartSelect('filtro_categoria');
        clearSmartSelect('filtro_marca');
        clearSmartSelect('filtro_preco');
        clearSmartSelect('filtro_disponibilidade');
        
        this.aplicarFiltros();
    }
}

// Inicializar sistema de filtros
const filtros = new FiltrosProdutos();
```

### 3. Carrinho de Compras Dinâmico

```javascript
class CarrinhoCompras {
    constructor() {
        this.itens = [];
        this.configurar();
    }
    
    configurar() {
        // Listener para seleção de produtos
        onSmartSelectChange('produtos_carrinho', (data) => {
            this.atualizarCarrinho(data.objects);
        });
    }
    
    atualizarCarrinho(produtos) {
        this.itens = produtos.map(produto => ({
            id: produto.id,
            nome: produto.nome,
            preco: produto.preco || 0,
            quantidade: 1
        }));
        
        this.calcularTotal();
        this.renderizarCarrinho();
    }
    
    adicionarProduto(produtoId) {
        // Verificar se produto já está no carrinho
        if (!isValueSelected('produtos_carrinho', produtoId)) {
            addToSmartSelect('produtos_carrinho', produtoId);
            console.log(`Produto ${produtoId} adicionado ao carrinho`);
        } else {
            // Se já está, aumentar quantidade
            this.aumentarQuantidade(produtoId);
        }
    }
    
    removerProduto(produtoId) {
        removeFromSmartSelect('produtos_carrinho', produtoId);
        console.log(`Produto ${produtoId} removido do carrinho`);
    }
    
    aumentarQuantidade(produtoId) {
        const item = this.itens.find(i => i.id === produtoId);
        if (item) {
            item.quantidade++;
            this.calcularTotal();
            this.renderizarCarrinho();
        }
    }
    
    diminuirQuantidade(produtoId) {
        const item = this.itens.find(i => i.id === produtoId);
        if (item && item.quantidade > 1) {
            item.quantidade--;
            this.calcularTotal();
            this.renderizarCarrinho();
        } else if (item && item.quantidade === 1) {
            this.removerProduto(produtoId);
        }
    }
    
    calcularTotal() {
        this.total = this.itens.reduce((sum, item) => {
            return sum + (item.preco * item.quantidade);
        }, 0);
        
        console.log(`Total do carrinho: R$ ${this.total.toFixed(2)}`);
    }
    
    renderizarCarrinho() {
        // Implementar renderização da interface
        // ...
    }
    
    limparCarrinho() {
        clearSmartSelect('produtos_carrinho');
        this.itens = [];
        this.total = 0;
        this.renderizarCarrinho();
    }
}

// Inicializar carrinho
const carrinho = new CarrinhoCompras();
```

### 4. Auto-complete com Criação Dinâmica

```javascript
class AutoCompletePersonalizado {
    constructor(selectName, apiUrl) {
        this.selectName = selectName;
        this.apiUrl = apiUrl;
        this.configurar();
    }
    
    configurar() {
        // Listener para mudanças
        onSmartSelectChange(this.selectName, (data) => {
            if (data.value === 'criar_novo') {
                this.criarNovoItem();
            }
        });
        
        // Adicionar opção "Criar Novo" inicialmente
        this.adicionarOpcaoCriarNovo();
    }
    
    adicionarOpcaoCriarNovo() {
        addSmartSelectOption(this.selectName, {
            value: 'criar_novo',
            label: '+ Criar Novo...',
            css_class: 'criar-novo-option'
        });
    }
    
    async criarNovoItem() {
        const nome = prompt('Digite o nome do novo item:');
        if (!nome) return;
        
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nome: nome })
            });
            
            const novoItem = await response.json();
            
            // Adicionar novo item às opções
            addSmartSelectOption(this.selectName, {
                value: novoItem.id,
                label: novoItem.nome
            });
            
            // Selecionar o novo item
            setSmartSelectValue(this.selectName, novoItem.id);
            
            console.log('Novo item criado e selecionado:', novoItem);
            
        } catch (error) {
            console.error('Erro ao criar novo item:', error);
            alert('Erro ao criar novo item');
        }
    }
}

// Uso
const autoCompleteCategoria = new AutoCompletePersonalizado('categoria', '/api/categorias');
const autoCompleteFornecedor = new AutoCompletePersonalizado('fornecedor', '/api/fornecedores');
```

---

## 🌐 Integração com AJAX

### Padrões de Integração

```javascript
// 1. Carregar opções ao inicializar
async function inicializarSelect(selectName, apiUrl, valorPadrao = null) {
    try {
        const response = await fetch(apiUrl);
        const opcoes = await response.json();
        
        updateSmartSelectOptions(selectName, opcoes);
        
        if (valorPadrao) {
            setSmartSelectValue(selectName, valorPadrao);
        }
        
        console.log(`Select ${selectName} inicializado com ${opcoes.length} opções`);
        
    } catch (error) {
        console.error(`Erro ao inicializar ${selectName}:`, error);
    }
}

// 2. Busca com debounce
function configurarBuscaComDebounce(selectName, apiUrl, delay = 300) {
    let timeoutId;
    
    // Interceptar mudanças no campo de busca do smart-select
    document.addEventListener('input', function(event) {
        if (event.target.closest(`[x-data*="${selectName}"]`) && 
            event.target.type === 'search') {
            
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                buscarOpcoes(event.target.value, selectName, apiUrl);
            }, delay);
        }
    });
}

async function buscarOpcoes(termo, selectName, apiUrl) {
    if (termo.length < 2) return;
    
    try {
        const response = await fetch(`${apiUrl}?q=${encodeURIComponent(termo)}`);
        const opcoes = await response.json();
        
        updateSmartSelectOptions(selectName, opcoes, true);
        
    } catch (error) {
        console.error('Erro na busca:', error);
    }
}

// 3. Cache de opções
class CacheOpcoes {
    constructor() {
        this.cache = new Map();
        this.tempoExpiracao = 5 * 60 * 1000; // 5 minutos
    }
    
    async obterOpcoes(chave, apiUrl) {
        const agora = Date.now();
        const cached = this.cache.get(chave);
        
        if (cached && (agora - cached.timestamp) < this.tempoExpiracao) {
            return cached.dados;
        }
        
        try {
            const response = await fetch(apiUrl);
            const dados = await response.json();
            
            this.cache.set(chave, {
                dados: dados,
                timestamp: agora
            });
            
            return dados;
            
        } catch (error) {
            // Retornar cache expirado se houver erro
            return cached ? cached.dados : [];
        }
    }
    
    limparCache(chave = null) {
        if (chave) {
            this.cache.delete(chave);
        } else {
            this.cache.clear();
        }
    }
}

const cache = new CacheOpcoes();

// Uso do cache
async function carregarComCache(selectName, apiUrl) {
    const opcoes = await cache.obterOpcoes(selectName, apiUrl);
    updateSmartSelectOptions(selectName, opcoes);
}
```

### Sincronização com Servidor

```javascript
// Auto-save quando valores mudarem
class AutoSave {
    constructor(endpoint, delay = 2000) {
        this.endpoint = endpoint;
        this.delay = delay;
        this.timeoutId = null;
        this.configurar();
    }
    
    configurar() {
        // Monitorar todos os smart-selects
        document.addEventListener('select-change', (event) => {
            this.agendarSalvamento();
        });
    }
    
    agendarSalvamento() {
        clearTimeout(this.timeoutId);
        this.timeoutId = setTimeout(() => {
            this.salvar();
        }, this.delay);
    }
    
    async salvar() {
        try {
            const dados = SmartSelectListener.getAllValues();
            
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });
            
            if (response.ok) {
                console.log('✅ Dados salvos automaticamente');
                this.mostrarIndicadorSucesso();
            } else {
                throw new Error('Erro no servidor');
            }
            
        } catch (error) {
            console.error('❌ Erro no auto-save:', error);
            this.mostrarIndicadorErro();
        }
    }
    
    mostrarIndicadorSucesso() {
        // Implementar indicador visual
        this.mostrarIndicador('Salvo automaticamente', 'success');
    }
    
    mostrarIndicadorErro() {
        this.mostrarIndicador('Erro ao salvar', 'error');
    }
    
    mostrarIndicador(mensagem, tipo) {
        // Implementar notificação visual
        console.log(`[${tipo.toUpperCase()}] ${mensagem}`);
    }
}

// Inicializar auto-save
const autoSave = new AutoSave('/api/auto-save');
```

---

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Valor não está sendo definido

**Sintomas:** `setSmartSelectValue()` retorna false ou valor não aparece

**Diagnóstico:**
```javascript
// Verificar se smart-select existe
const elemento = document.querySelector(`[x-data*="'${selectName}'"]`);
console.log('Elemento encontrado:', !!elemento);

// Verificar opções disponíveis
if (elemento && elemento._x_dataStack) {
    const opcoes = elemento._x_dataStack[0].options;
    console.log('Opções disponíveis:', opcoes);
    
    // Procurar valor específico
    const valorEncontrado = opcoes.find(opt => 
        String(opt.value || opt.id) === String(valorProcurado)
    );
    console.log('Valor encontrado nas opções:', valorEncontrado);
}
```

**Soluções:**
```javascript
// 1. Criar opção se não existir
setSmartSelectValue('categoria', '123', {
    createIfNotFound: true,
    tempLabel: 'Categoria Temporária'
});

// 2. Buscar por label em vez de valor
setSmartSelectByLabel('categoria', 'Nome da Categoria');

// 3. Verificar estrutura das opções
const opcoes = getSmartSelectOptions('categoria');
console.log('Estrutura das opções:', opcoes[0]);
```

#### 2. Eventos não estão sendo disparados

**Sintomas:** Listeners configurados não executam após definir valor

**Soluções:**
```javascript
// Garantir que eventos sejam disparados
setSmartSelectValue('categoria', '123', {
    triggerEvents: true  // Padrão é true, mas garantir
});

// Verificar se listener está ativo
SmartSelectListener.setDebug(true);
setSmartSelectValue('categoria', '123');
// Deve mostrar logs de execução
```

#### 3. Seleção múltipla não funciona corretamente

**Sintomas:** `addToSmartSelect()` não adiciona ou remove valores incorretos

**Diagnóstico:**
```javascript
// Verificar se é múltiplo
const valorAtual = getSmartSelectValue('produtos');
console.log('É múltiplo:', valorAtual.multiple);
console.log('Valores atuais:', valorAtual.values);

// Verificar configuração do smart-select
const elemento = document.querySelector(`[x-data*="'produtos'"]`);
if (elemento && elemento._x_dataStack) {
    const config = elemento._x_dataStack[0];
    console.log('Configuração multiple:', config.multiple);
}
```

**Soluções:**
```javascript
// 1. Forçar array para seleção múltipla
const valores = Array.isArray(valorDesejado) ? valorDesejado : [valorDesejado];
setSmartSelectValue('produtos', valores);

// 2. Verificar antes de usar funções específicas
if (getSmartSelectValue('produtos').multiple) {
    addToSmartSelect('produtos', '123');
} else {
    console.warn('Smart-select não é múltiplo');
}
```

#### 4. Performance lenta com muitas opções

**Sintomas:** Lentidão ao definir valores em selects com centenas de opções

**Soluções:**
```javascript
// 1. Desabilitar eventos durante operações em lote
const valores = ['123', '456', '789', '999'];
valores.forEach((valor, index) => {
    setSmartSelectValue('produtos', valor, {
        triggerEvents: index === valores.length - 1 // Só no último
    });
});

// 2. Usar operação única para múltiplos valores
setSmartSelectValue('produtos', valores); // Mais eficiente que múltiplas chamadas

// 3. Implementar debounce para atualizações frequentes
let debounceTimeout;
function definirValorComDebounce(selectName, valor) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        setSmartSelectValue(selectName, valor);
    }, 100);
}
```

#### 5. Conflitos com outros scripts

**Sintomas:** Funções não estão disponíveis ou comportamento inconsistente

**Diagnóstico:**
```javascript
// Verificar se funções estão carregadas
console.log('Funções disponíveis:', {
    setSmartSelectValue: typeof window.setSmartSelectValue,
    SmartSelectListener: typeof window.SmartSelectListener,
    Alpine: typeof window.Alpine
});

// Verificar ordem de carregamento
console.log('Scripts carregados na ordem correta:',
    typeof window.Alpine !== 'undefined' &&
    typeof window.SmartSelectListener !== 'undefined' &&
    typeof window.setSmartSelectValue !== 'undefined'
);
```

**Soluções:**
```javascript
// 1. Aguardar carregamento completo
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar um pouco mais para garantir que tudo está carregado
    setTimeout(() => {
        setSmartSelectValue('categoria', '123');
    }, 100);
});

// 2. Verificar antes de usar
function definirValorSeguro(selectName, valor) {
    if (typeof setSmartSelectValue === 'function') {
        return setSmartSelectValue(selectName, valor);
    } else {
        console.error('setSmartSelectValue não está disponível');
        return false;
    }
}
```

### Comandos de Debug

```javascript
// Função utilitária para debug completo
function debugSmartSelect(selectName) {
    console.log(`🔍 DEBUG SMART-SELECT: ${selectName}`);
    console.log('==========================================');
    
    // 1. Verificar se elemento existe
    const elemento = document.querySelector(`[x-data*="'${selectName}'"]`);
    console.log('✓ Elemento encontrado:', !!elemento);
    
    if (!elemento) {
        console.error('❌ Elemento não encontrado');
        return;
    }
    
    // 2. Verificar dados Alpine
    const temDados = elemento._x_dataStack && elemento._x_dataStack[0];
    console.log('✓ Dados Alpine disponíveis:', !!temDados);
    
    if (temDados) {
        const dados = elemento._x_dataStack[0];
        console.log('✓ Configuração multiple:', dados.multiple);
        console.log('✓ Opções disponíveis:', dados.options?.length || 0);
        console.log('✓ Valores selecionados:', dados.selectedValues);
        console.log('✓ Labels selecionados:', dados.selectedLabels);
    }
    
    // 3. Verificar valor atual via função
    try {
        const valorAtual = getSmartSelectValue(selectName);
        console.log('✓ Valor atual (função):', valorAtual);
    } catch (error) {
        console.error('❌ Erro ao obter valor:', error);
    }
    
    // 4. Verificar inputs hidden
    const inputsSingle = document.querySelectorAll(`input[name="${selectName}"]`);
    const inputsMultiple = document.querySelectorAll(`input[name="${selectName}[]"]`);
    console.log('✓ Inputs single:', inputsSingle.length);
    console.log('✓ Inputs multiple:', inputsMultiple.length);
    
    // 5. Listar opções disponíveis
    if (temDados && dados.options) {
        console.log('📋 Primeiras 5 opções:');
        dados.options.slice(0, 5).forEach((opcao, index) => {
            console.log(`  ${index + 1}. Valor: ${dados.getOptionValue(opcao)}, Label: ${dados.getOptionText(opcao)}`);
        });
    }
    
    console.log('==========================================');
}

// Testar definição de valor com debug
function testarDefinicaoValor(selectName, valor) {
    console.log(`🧪 TESTE: Definindo "${valor}" em "${selectName}"`);
    
    debugSmartSelect(selectName);
    
    const sucesso = setSmartSelectValue(selectName, valor);
    console.log('Resultado:', sucesso ? '✅ Sucesso' : '❌ Falhou');
    
    if (sucesso) {
        const novoValor = getSmartSelectValue(selectName);
        console.log('Valor após definição:', novoValor.value);
    }
}

// Exemplo de uso:
// debugSmartSelect('categoria');
// testarDefinicaoValor('categoria', '123');
```

---

## 📖 API Completa

### Funções Principais

| Função | Descrição | Parâmetros | Retorno |
|--------|-----------|------------|---------|
| `setSmartSelectValue(name, value, options)` | Define valor(es) no smart-select | name (string), value (any), options (object) | boolean |
| `setSmartSelectByLabel(name, label, options)` | Define valor por label | name (string), label (string), options (object) | boolean |
| `clearSmartSelect(name)` | Limpa seleção | name (string) | boolean |
| `getSmartSelectValue(name)` | Obtém valor atual | name (string) | object |
| `isValueSelected(name, value)` | Verifica se valor está selecionado | name (string), value (any) | boolean |

### Funções de Seleção Múltipla

| Função | Descrição | Parâmetros | Retorno |
|--------|-----------|------------|---------|
| `addToSmartSelect(name, value, options)` | Adiciona valor à seleção múltipla | name (string), value (any), options (object) | boolean |
| `removeFromSmartSelect(name, value)` | Remove valor da seleção múltipla | name (string), value (any) | boolean |
| `toggleSmartSelectValue(name, value)` | Alterna valor (adiciona/remove) | name (string), value (any) | boolean |

### Funções de Gerenciamento

| Função | Descrição | Parâmetros | Retorno |
|--------|-----------|------------|---------|
| `updateSmartSelectOptions(name, options, preserve)` | Atualiza opções | name (string), options (array), preserve (boolean) | boolean |
| `addSmartSelectOption(name, option)` | Adiciona uma opção | name (string), option (object) | boolean |

### Opções de Configuração

```javascript
const options = {
    triggerEvents: true,        // boolean - Disparar eventos de mudança
    updateLabel: true,          // boolean - Atualizar label automaticamente  
    forceUpdate: false,         // boolean - Forçar atualização mesmo se valor igual
    findByValue: true,          // boolean - Buscar opção por valor
    findByLabel: false,         // boolean - Buscar opção por label
    createIfNotFound: false,    // boolean - Criar opção se não encontrar
    tempLabel: null,            // string - Label para opções temporárias
    valueField: 'value',        // string - Campo usado como valor
    textField: 'label'          // string - Campo usado como texto
};
```

### Estrutura de Dados Retornados

```javascript
// Retorno de getSmartSelectValue()
{
    name: 'categoria',              // string - Nome do select
    value: 'single-value',          // any - Valor único (ou null)
    values: ['val1', 'val2'],       // array - Array de valores
    label: 'Label selecionado',     // string - Label único (ou null)
    labels: ['Label1', 'Label2'],   // array - Array de labels
    object: {id: 1, name: 'Item'},  // object - Objeto único (ou null)
    objects: [{}, {}],              // array - Array de objetos
    multiple: false                 // boolean - Se é seleção múltipla
}
```

---

## 🎯 Casos de Uso Avançados

### 1. Sistema de Aprovações com Múltiplos Níveis

```javascript
class SistemaAprovacoes {
    constructor() {
        this.configurarFluxo();
    }
    
    configurarFluxo() {
        // Quando status mudar, ajustar aprovadores disponíveis
        onSmartSelectChange('status', (data) => {
            this.atualizarAprovadores(data.value);
        });
        
        // Quando aprovador mudar, verificar permissões
        onSmartSelectChange('aprovador', (data) => {
            this.verificarPermissoes(data.value);
        });
    }
    
    async atualizarAprovadores(status) {
        const fluxos = {
            'pendente': ['supervisor', 'gerente'],
            'aprovado_nivel_1': ['diretor', 'presidente'],
            'rejeitado': []
        };
        
        const aprovadoresPermitidos = fluxos[status] || [];
        
        if (aprovadoresPermitidos.length === 0) {
            clearSmartSelect('aprovador');
            updateSmartSelectOptions('aprovador', []);
            return;
        }
        
        try {
            const response = await fetch(`/api/aprovadores?nivel=${aprovadoresPermitidos.join(',')}`);
            const aprovadores = await response.json();
            
            updateSmartSelectOptions('aprovador', aprovadores);
            
            // Auto-selecionar se só houver um aprovador
            if (aprovadores.length === 1) {
                setSmartSelectValue('aprovador', aprovadores[0].id);
            }
            
        } catch (error) {
            console.error('Erro ao carregar aprovadores:', error);
        }
    }
    
    verificarPermissoes(aprovadorId) {
        const statusAtual = getSmartSelectValue('status').value;
        
        // Lógica de validação específica
        if (statusAtual === 'aprovado_nivel_1' && aprovadorId) {
            this.habilitarCampoJustificativa();
        }
    }
    
    habilitarCampoJustificativa() {
        const campo = document.getElementById('justificativa');
        if (campo) {
            campo.disabled = false;
            campo.required = true;
        }
    }
}

new SistemaAprovacoes();
```

### 2. Configurador de Produtos Dinâmico

```javascript
class ConfiguradorProduto {
    constructor() {
        this.configuracao = {};
        this.precoBase = 0;
        this.configurar();
    }
    
    configurar() {
        // Configurar dependências entre opções
        onMultipleSmartSelectChange({
            'categoria': (data) => this.atualizarTipos(data.value),
            'tipo': (data) => this.atualizarModelos(data.value),
            'modelo': (data) => this.atualizarOpcoes(data.value),
            'cor': (data) => this.calcularPreco(),
            'acabamento': (data) => this.calcularPreco(),
            'extras': (data) => this.calcularPreco()
        });
    }
    
    async atualizarTipos(categoriaId) {
        if (!categoriaId) {
            this.limparSelecoesDependentes(['tipo', 'modelo', 'cor', 'acabamento', 'extras']);
            return;
        }
        
        try {
            const tipos = await this.buscarOpcoes('/api/tipos', { categoria: categoriaId });
            updateSmartSelectOptions('tipo', tipos);
            this.limparSelecoesDependentes(['modelo', 'cor', 'acabamento', 'extras']);
            
        } catch (error) {
            console.error('Erro ao carregar tipos:', error);
        }
    }
    
    async atualizarModelos(tipoId) {
        if (!tipoId) {
            this.limparSelecoesDependentes(['modelo', 'cor', 'acabamento', 'extras']);
            return;
        }
        
        try {
            const modelos = await this.buscarOpcoes('/api/modelos', { tipo: tipoId });
            updateSmartSelectOptions('modelo', modelos);
            this.limparSelecoesDependentes(['cor', 'acabamento', 'extras']);
            
        } catch (error) {
            console.error('Erro ao carregar modelos:', error);
        }
    }
    
    async atualizarOpcoes(modeloId) {
        if (!modeloId) {
            this.limparSelecoesDependentes(['cor', 'acabamento', 'extras']);
            return;
        }
        
        try {
            // Carregar opções em paralelo
            const [cores, acabamentos, extras] = await Promise.all([
                this.buscarOpcoes('/api/cores', { modelo: modeloId }),
                this.buscarOpcoes('/api/acabamentos', { modelo: modeloId }),
                this.buscarOpcoes('/api/extras', { modelo: modeloId })
            ]);
            
            updateSmartSelectOptions('cor', cores);
            updateSmartSelectOptions('acabamento', acabamentos);
            updateSmartSelectOptions('extras', extras);
            
            // Obter preço base do modelo
            const modeloInfo = await this.buscarOpcoes('/api/modelos/' + modeloId);
            this.precoBase = modeloInfo.preco || 0;
            this.calcularPreco();
            
        } catch (error) {
            console.error('Erro ao carregar opções:', error);
        }
    }
    
    calcularPreco() {
        let precoTotal = this.precoBase;
        
        // Somar preços de opções selecionadas
        const selecoes = {
            cor: getSmartSelectValue('cor'),
            acabamento: getSmartSelectValue('acabamento'),
            extras: getSmartSelectValue('extras')
        };
        
        Object.values(selecoes).forEach(selecao => {
            if (selecao.multiple && selecao.objects) {
                // Múltipla seleção
                selecao.objects.forEach(obj => {
                    precoTotal += obj.preco_adicional || 0;
                });
            } else if (selecao.object) {
                // Seleção única
                precoTotal += selecao.object.preco_adicional || 0;
            }
        });
        
        this.atualizarPrecoInterface(precoTotal);
    }
    
    atualizarPrecoInterface(preco) {
        const elementoPreco = document.getElementById('preco-total');
        if (elementoPreco) {
            elementoPreco.textContent = `R$ ${preco.toFixed(2)}`;
        }
        
        console.log(`Preço atualizado: R$ ${preco.toFixed(2)}`);
    }
    
    limparSelecoesDependentes(selects) {
        selects.forEach(select => clearSmartSelect(select));
    }
    
    async buscarOpcoes(url, params = {}) {
        const searchParams = new URLSearchParams(params);
        const response = await fetch(`${url}?${searchParams}`);
        return response.json();
    }
    
    obterConfiguracaoCompleta() {
        return {
            categoria: getSmartSelectValue('categoria'),
            tipo: getSmartSelectValue('tipo'),
            modelo: getSmartSelectValue('modelo'),
            cor: getSmartSelectValue('cor'),
            acabamento: getSmartSelectValue('acabamento'),
            extras: getSmartSelectValue('extras'),
            precoTotal: this.calcularPrecoAtual()
        };
    }
    
    salvarConfiguracao() {
        const configuracao = this.obterConfiguracaoCompleta();
        
        // Validar se configuração está completa
        const obrigatorios = ['categoria', 'tipo', 'modelo', 'cor'];
        const faltando = obrigatorios.filter(campo => !configuracao[campo].value);
        
        if (faltando.length > 0) {
            alert(`Campos obrigatórios: ${faltando.join(', ')}`);
            return false;
        }
        
        // Salvar configuração
        console.log('Salvando configuração:', configuracao);
        return true;
    }
}

new ConfiguradorProduto();
```

### 3. Sistema de Tags Inteligente

```javascript
class SistemaTags {
    constructor(selectName, apiUrl) {
        this.selectName = selectName;
        this.apiUrl = apiUrl;
        this.tagsPersonalizadas = new Set();
        this.configurar();
    }
    
    configurar() {
        // Configurar busca de tags
        this.configurarBuscaAjax();
        
        // Listener para criação de novas tags
        onSmartSelectChange(this.selectName, (data) => {
            this.processarSelecao(data);
        });
        
        // Adicionar opção "Criar nova tag"
        this.adicionarOpcaoCriarTag();
    }
    
    configurarBuscaAjax() {
        let timeoutBusca;
        
        // Interceptar busca no smart-select
        document.addEventListener('input', (event) => {
            if (event.target.closest(`[x-data*="${this.selectName}"]`) &&
                event.target.type === 'search') {
                
                clearTimeout(timeoutBusca);
                timeoutBusca = setTimeout(() => {
                    this.buscarTags(event.target.value);
                }, 300);
            }
        });
    }
    
    async buscarTags(termo) {
        if (termo.length < 2) return;
        
        try {
            const response = await fetch(`${this.apiUrl}?q=${encodeURIComponent(termo)}`);
            const tags = await response.json();
            
            // Adicionar tags encontradas
            const todasTags = [...tags];
            
            // Adicionar opção para criar nova tag se não encontrar exatamente
            const tagExataEncontrada = tags.some(tag => 
                tag.nome.toLowerCase() === termo.toLowerCase()
            );
            
            if (!tagExataEncontrada && termo.trim()) {
                todasTags.unshift({
                    id: `criar_${termo}`,
                    nome: `Criar "${termo}"`,
                    tipo: 'criar_nova'
                });
            }
            
            updateSmartSelectOptions(this.selectName, todasTags, true);
            
        } catch (error) {
            console.error('Erro ao buscar tags:', error);
        }
    }
    
    processarSelecao(data) {
        if (data.objects) {
            data.objects.forEach(tag => {
                if (tag.tipo === 'criar_nova' || tag.id?.toString().startsWith('criar_')) {
                    this.criarNovaTag(tag);
                }
            });
        }
    }
    
    async criarNovaTag(tagInfo) {
        const nomeTag = tagInfo.nome.replace('Criar "', '').replace('"', '');
        
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nome: nomeTag })
            });
            
            const novaTag = await response.json();
            
            // Remover tag temporária e adicionar a real
            removeFromSmartSelect(this.selectName, tagInfo.id);
            
            // Adicionar nova tag às opções
            addSmartSelectOption(this.selectName, novaTag);
            
            // Selecionar a nova tag
            addToSmartSelect(this.selectName, novaTag.id);
            
            this.tagsPersonalizadas.add(novaTag.id);
            console.log('Nova tag criada:', novaTag);
            
        } catch (error) {
            console.error('Erro ao criar nova tag:', error);
            // Remover tag temporária em caso de erro
            removeFromSmartSelect(this.selectName, tagInfo.id);
        }
    }
    
    adicionarOpcaoCriarTag() {
        addSmartSelectOption(this.selectName, {
            id: 'criar_personalizada',
            nome: '+ Criar nova tag...',
            tipo: 'acao_especial'
        });
    }
    
    obterTagsSelecionadas() {
        const valores = getSmartSelectValue(this.selectName);
        return {
            todas: valores.objects || [],
            personalizadas: valores.objects?.filter(tag => 
                this.tagsPersonalizadas.has(tag.id)
            ) || [],
            existentes: valores.objects?.filter(tag => 
                !this.tagsPersonalizadas.has(tag.id)
            ) || []
        };
    }
    
    definirTagsPadrao(tags) {
        const idsValidos = tags.filter(tag => tag.id && tag.id !== 'criar_personalizada');
        setSmartSelectValue(this.selectName, idsValidos.map(tag => tag.id));
    }
}

// Uso
const sistemaTags = new SistemaTags('tags_produto', '/api/tags');
```

---

## 🎓 Conclusão

Esta documentação fornece **guia completo** para definir e manipular valores nos smart-selects, cobrindo desde operações básicas até casos de uso avançados.

### 🎯 Principais Benefícios

- ✅ **Controle Total** - Manipule qualquer aspecto do smart-select programaticamente
- ✅ **Flexibilidade** - Múltiplas formas de definir valores (por ID, label, objeto)
- ✅ **Performance** - Operações otimizadas para diferentes cenários
- ✅ **Robustez** - Tratamento de erros e fallbacks seguros
- ✅ **Integração** - Funciona perfeitamente com AJAX e APIs
- ✅ **Escalabilidade** - Suporte para aplicações simples e complexas

### 🚀 Próximos Passos

1. **Implemente** as funções básicas em seus formulários
2. **Teste** os exemplos práticos em seu ambiente
3. **Personalize** para suas necessidades específicas
4. **Monitore** performance e otimize conforme necessário
5. **Documente** suas implementações específicas

### 📞 Suporte

Para problemas ou dúvidas:
1. Use as ferramentas de debug fornecidas
2. Consulte a seção Troubleshooting
3. Verifique a API completa
4. Teste com exemplos isolados

---

**Versão:** 2.0.0  
**Compatibilidade:** Laravel + Alpine.js + Smart-Select System  
**Status:** 🟢 Produção Ready  
**Última Atualização:** 2025