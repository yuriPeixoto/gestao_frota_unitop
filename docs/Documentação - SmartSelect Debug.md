# Smart Select - Modo Debug

## Descrição

O componente Smart Select agora possui um parâmetro `:debug` que permite habilitar logs detalhados para facilitar o debugging durante o desenvolvimento.

## Como usar

### Modo Normal (sem debug)
```blade
<x-forms.smart-select 
    name="usuario" 
    label="Usuário"
    :options="$usuarios"
    value-field="id"
    text-field="nome"
/>
```

### Modo Debug Habilitado
```blade
<x-forms.smart-select 
    name="usuario" 
    label="Usuário"
    :options="$usuarios"
    value-field="id"
    text-field="nome"
    :debug="true"
/>
```

## Logs Disponíveis

Quando o debug está habilitado (`:debug="true"`), o componente irá gerar logs detalhados no console do navegador com as seguintes informações:

### Inicialização
- **Component initialization**: Configurações iniciais do componente
- **Initial selection mapped**: Mapeamento das seleções iniciais
- **Component initialization completed**: Finalização da inicialização

### Interações do Usuário
- **Toggle dropdown triggered**: Quando o usuário clica para abrir/fechar o dropdown
- **Dropdown state changed**: Mudanças de estado do dropdown (aberto/fechado)
- **Search term changed**: Mudanças no termo de busca
- **Search cleared**: Limpeza do campo de busca

### Busca e Filtragem
- **Filtering options**: Processo de filtragem das opções
- **Starting async search**: Início de busca assíncrona
- **Search response received**: Resposta da busca recebida
- **Options updated after search**: Atualização das opções após busca

### Seleção e Remoção
- **Selecting option**: Processo de seleção de opção
- **Option selected/deselected**: Confirmação de seleção/deseleção
- **Selection updated**: Atualização da seleção
- **Item removed**: Remoção de item
- **Selection cleared**: Limpeza total da seleção

### Posicionamento
- **Dropdown position calculated**: Cálculo da posição do dropdown
- **Dropdown repositioned**: Reposicionamento do dropdown

### Navegação por Teclado
- **Highlight moved**: Movimentação do destaque por setas
- **Selecting highlighted option**: Seleção via Enter/Space

### Eventos
- **Dispatching selection event**: Disparo de eventos de seleção
- **Dispatching removal event**: Disparo de eventos de remoção
- **External selection event**: Eventos de seleção externa

### Callbacks
- **Executing callback**: Execução de callbacks
- **Callback not found**: Callbacks não encontrados

## Exemplo de Log no Console

```
[SmartSelect:usuario] Initializing component Object { config: {…} }
[SmartSelect:usuario] Initial selection mapped Object { selectedObjects: [], selectedLabels: [] }
[SmartSelect:usuario] Component initialization completed 
[SmartSelect:usuario] Toggle dropdown triggered Object { currentState: false, disabled: false }
[SmartSelect:usuario] Dropdown state updated Object { newState: true }
[SmartSelect:usuario] Dropdown position calculated Object { top: 45, left: 20, width: 300 }
[SmartSelect:usuario] Search term changed Object { searchTerm: "João", minLength: 3 }
[SmartSelect:usuario] Triggering search Object { url: "/api/usuarios/search" }
[SmartSelect:usuario] Starting async search Object { searchTerm: "João", url: "/api/usuarios/search" }
[SmartSelect:usuario] Search response received Object { resultCount: 5, results: [...] }
[SmartSelect:usuario] Selecting option Object { value: 1, label: "João Silva", option: {…} }
[SmartSelect:usuario] Option selected in single mode Object { selectedValue: 1 }
[SmartSelect:usuario] Selection updated Object { selectedValues: [1], selectedLabels: ["João Silva"] }
[SmartSelect:usuario] Dispatching selection event Object { name: "usuario", value: 1, label: "João Silva" }
```

## Importante

- O parâmetro `:debug` **sempre deve ser false** em produção
- Use `:debug="true"` **apenas durante o desenvolvimento**
- Os logs são exibidos no console do navegador (F12 → Console)
- Cada log é prefixado com `[SmartSelect:nome_do_campo]` para facilitar a identificação

## Troubleshooting

Com os logs habilitados, você pode:

1. **Verificar se o componente foi inicializado corretamente**
2. **Acompanhar o processo de busca assíncrona**
3. **Debugar problemas de seleção/remoção**
4. **Verificar se eventos estão sendo disparados**
5. **Monitorar a execução de callbacks**
6. **Identificar problemas de posicionamento do dropdown**

## Exemplo Completo

```blade
<!-- Em desenvolvimento com debug -->
<x-forms.smart-select 
    name="produto" 
    label="Produto"
    search-url="{{ route('api.produtos.search') }}"
    value-field="id"
    text-field="nome"
    :multiple="true"
    :min-search-length="2"
    :debug="true"
    on-select-callback="onProdutoSelected"
/>

<!-- Em produção sem debug -->
<x-forms.smart-select 
    name="produto" 
    label="Produto"
    search-url="{{ route('api.produtos.search') }}"
    value-field="id"
    text-field="nome"
    :multiple="true"
    :min-search-length="2"
    on-select-callback="onProdutoSelected"
/>
```
