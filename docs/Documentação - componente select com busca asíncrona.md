# Documentação: Componente Smart Select com Busca Assíncrona e Interatividade Entre Campos

**Versão 2.0.0 - Data: 22/04/2025**

## Índice
1. [Visão Geral](#visão-geral)
2. [Problema Resolvido](#problema-resolvido)
3. [Atualizações e Correções](#atualizações-e-correções)
4. [Atualização Mais Recente: Compatibilidade e Melhorias](#atualização-mais-recente-compatibilidade-e-melhorias)
5. [Configuração e Instalação](#configuração-e-instalação)
6. [Uso Básico do Componente](#uso-básico-do-componente)
7. [Busca Assíncrona](#busca-assíncrona)
8. [Interatividade Entre Campos](#interatividade-entre-campos)
9. [Considerações de Performance](#considerações-de-performance)
10. [Troubleshooting](#troubleshooting)

## Visão Geral

O Smart Select é um componente de seleção avançado para Laravel Blade + AlpineJS, projetado para lidar eficientemente com grandes conjuntos de dados e oferecer busca assíncrona. 

### Principais recursos:
- Busca assíncrona para grandes conjuntos de dados
- Busca local para conjuntos pequenos
- Seleção única e múltipla
- Navegação por teclado
- Interatividade entre campos via eventos e callbacks
- Caching para melhorar performance
- Design responsivo e acessível

## Problema Resolvido

O componente Smart Select resolve principalmente:

1. **Performance com grandes conjuntos de dados**: Evita carregar milhares de opções no DOM
2. **Experiência de usuário otimizada**: Fornece busca instantânea e navegação fácil
3. **Consistência visual**: Interface unificada para todos os selects da aplicação
4. **Interatividade entre campos**: Facilita preenchimento automático de campos relacionados

## Atualizações e Correções

**Versão 2.0.1 (22/04/2025):**

Principais melhorias:
- **Correção do problema de renderização do dropdown**: Corrigido problema onde o dropdown não abria corretamente em alguns casos
- **Eliminação da dependência de teleport**: O dropdown agora é renderizado diretamente no DOM para maior compatibilidade
- **Melhor manipulação de eventos**: Adicionado `stopPropagation()` para evitar conflitos de eventos
- **Posicionamento inteligente**: O dropdown agora verifica espaço na tela e se ajusta para cima se necessário
- **Melhor compatibilidade com diferentes versões do AlpineJS**: Removidas dependências de recursos específicos de versões recentes
- **Navegação por teclado aprimorada**: Suporte robusto para navegação por setas e teclas Enter/Space
- **Logging de diagnóstico**: Logs adicionais para facilitar diagnóstico de problemas

## Atualização Mais Recente: Compatibilidade e Melhorias

### 1. Compatibilidade entre parâmetros `value` e `selected`

O componente agora aceita tanto o parâmetro `value` (usado em implementações mais antigas) quanto o parâmetro `selected` (padrão atual).

```php
@php
    // Compatibilidade: aceitar tanto "selected" quanto "value"
    $selectedValue = $selected ?? $value;
@endphp
```

Isso significa que views existentes que utilizam `value` continuarão funcionando sem necessidade de modificação:

```blade
<!-- Esta sintaxe antiga continua funcionando -->
<x-forms.smart-select name="id_tipo_ordem_servico" value="{{ request('id_tipo_ordem_servico') }}" />

<!-- Assim como a sintaxe atual -->
<x-forms.smart-select name="id_tipo_ordem_servico" selected="{{ request('id_tipo_ordem_servico') }}" />
```

### 2. Eliminação da dependência de teleport

A versão anterior do componente utilizava o recurso de teleport do Alpine.js para renderizar o dropdown:

```blade
<!-- Versão antiga com teleport -->
<template x-teleport="#portal-root">
    <div x-show="open" ... >
        <!-- Conteúdo do dropdown -->
    </div>
</template>
```

Isso foi substituído por uma abordagem mais direta com renderização no DOM normal:

```blade
<!-- Versão nova sem teleport -->
<div x-show="open" ... >
    <!-- Conteúdo do dropdown -->
</div>
```

Esta mudança elimina a necessidade de um elemento com ID `portal-root` no layout principal e evita problemas de renderização em certos cenários.

### 3. Melhor manipulação de eventos

Adicionamos `stopPropagation()` ao manipulador do evento de clique para evitar conflitos:

```javascript
toggleDropdown(event) {
    // Garantir que o evento não se propague
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    this.open = !this.open;
    // ...
}
```

### 4. Posicionamento inteligente do dropdown

O componente agora verifica o espaço disponível na tela e ajusta o posicionamento do dropdown:

```javascript
updateDropdownPosition() {
    // ...
    
    // Verificar se o dropdown vai sair da tela na parte inferior
    const windowHeight = window.innerHeight;
    const dropdownHeight = this.$refs.dropdown ? this.$refs.dropdown.offsetHeight : 300;
    
    if (buttonRect.bottom + dropdownHeight > windowHeight) {
        // Posicionar acima do botão se não houver espaço abaixo
        this.dropdownTop = buttonRect.top + scrollY - dropdownHeight;
    }
}
```

### 5. Logging de diagnóstico aprimorado

Adicionamos logs de diagnóstico para facilitar a identificação de problemas:

```javascript
init() {
    // ...
    console.log(`Smart Select '${this.name}' initialized`);
}
```

## Configuração e Instalação

### 1. Configuração de Rotas

Crie rotas específicas para busca assíncrona:

```php
// routes/web.php
Route::prefix('admin')->name('admin.')->group(function () {
    // Rotas para busca de registros
    Route::get('municipios/search', [MunicipioController::class, 'search'])
        ->name('municipios.search');
    Route::get('municipios/single/{id}', [MunicipioController::class, 'single'])
        ->name('municipios.single');
        
    // Outras rotas de busca para diferentes entidades...
});
```

### 2. Implementação dos Métodos no Controller

Adicione os métodos `search` e `single` no controlador da entidade:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MunicipioController extends Controller
{
    /**
     * Busca municípios baseado em um termo de pesquisa
     */
    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));
        
        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $municipios = Cache::remember('municipio_search_'.$term, now()->addMinutes(30), function() use ($term) {
            return Municipio::whereRaw('LOWER(nome_municipio) LIKE ?', ["%{$term}%"])
                ->orderBy('nome_municipio')
                ->limit(30)
                ->get(['id_municipio as value', 'nome_municipio as label']);
        });
            
        return response()->json($municipios);
    }

    /**
     * Retorna um município específico pelo ID
     */
    public function single($id)
    {
        $municipio = Municipio::select('id_municipio as value', 'nome_municipio as label')
            ->findOrFail($id);
            
        return response()->json($municipio);
    }
}
```

## Uso Básico do Componente

Para utilizar o componente em suas views:

```blade
<!-- Para conjuntos grandes de dados com busca assíncrona -->
<x-forms.smart-select 
    name="id_municipio" 
    label="Município" 
    placeholder="Selecione o município..."
    :options="$municipiosData"
    :searchUrl="route('admin.municipios.search')"
    :selected="old('id_municipio', $multas->id_municipio ?? '')"
    asyncSearch="true"
    minSearchLength="2"
/>

<!-- Para conjuntos pequenos de dados (busca local) -->
<x-forms.smart-select 
    name="id_filial" 
    label="Filial" 
    placeholder="Selecione..."
    :options="$filiais"
    :selected="old('id_filial', $model->id_filial ?? '')"
    asyncSearch="false"
/>
```

### Parâmetros Disponíveis

| Parâmetro | Tipo | Descrição | Padrão |
|-----------|------|-----------|--------|
| name | string | Nome do campo (obrigatório) | - |
| label | string | Rótulo do campo | null |
| placeholder | string | Texto de placeholder | "Selecione..." |
| options | array | Array de opções iniciais | [] |
| searchUrl | string | URL para busca assíncrona | null |
| selected | mixed | Valor(es) pré-selecionado(s) | null |
| value | mixed | (Compatibilidade) Alternativa ao selected | null |
| required | boolean | Se o campo é obrigatório | false |
| disabled | boolean | Se o campo está desabilitado | false |
| error | string | Mensagem de erro personalizada | null |
| valueField | string | Nome do campo de valor nas opções | "value" |
| textField | string | Nome do campo de texto nas opções | "label" |
| asyncSearch | boolean | Usar busca assíncrona | false |
| minSearchLength | integer | Mínimo de caracteres para iniciar busca | 3 |
| multiple | boolean | Permitir seleção múltipla | false |
| onSelectCallback | string | Nome da função callback JS | null |

## Busca Assíncrona

Para grandes conjuntos de dados, use a busca assíncrona:

1. **Configure o back-end**: Implemente o método `search` no controller como demonstrado acima
2. **Ative a busca assíncrona no componente**: Defina `asyncSearch="true"` e forneça a URL de busca
3. **Configure o cache para otimizar performance**

Exemplo:
```blade
<x-forms.smart-select 
    name="id_municipio" 
    label="Município" 
    :options="$municipiosFrequentes"  {{-- apenas opções iniciais --}}
    :searchUrl="route('admin.municipios.search')"
    asyncSearch="true"
    minSearchLength="2"
/>
```

## Interatividade Entre Campos

### Exemplo: Preenchimento Automático de Campos Relacionados

```javascript
// Adicione este script à sua página
document.addEventListener('DOMContentLoaded', function() {
    // Escutar evento de seleção de município
    window.addEventListener('id_municipio:selected', function(e) {
        const municipioData = e.detail;
        
        // Preencher o estado automaticamente
        if (municipioData.object && municipioData.object.uf) {
            document.getElementById('uf').value = municipioData.object.uf;
        }
    });
});
```

### Usando Callbacks Diretos

```blade
<x-forms.smart-select 
    name="id_veiculo" 
    label="Placa"
    :options="$placasData"
    :selected="isset($multas) ? $multas->id_veiculo : null" 
    required="true"
    asyncSearch="true" 
    searchUrl="{{ route('admin.veiculos.search') }}"
    minSearchLength="2" 
    onSelectCallback="atualizarDadosVeiculo" 
/>
```

E no JavaScript:
```javascript
function atualizarDadosVeiculo(id, objetoCompleto) {
    // Lógica para atualizar campos relacionados
    document.getElementById('departamento').value = objetoCompleto.departamento || '';
    document.getElementById('filial').value = objetoCompleto.filial || '';
}
```

## Considerações de Performance

1. **Indexe as colunas de busca**:
   ```php
   // Na migration
   $table->string('nome_municipio')->index();
   ```

2. **Use caching em produção** como implementado nos exemplos

3. **Limite o número de resultados** (normalmente 20-30 é suficiente)

4. **Defina um valor apropriado para `minSearchLength`**: 
   - 2 caracteres para nomes curtos (municípios, estados)
   - 3+ caracteres para dados maiores (usuários, produtos)

5. **Carregue apenas dados frequentes inicialmente**:
   ```php
   $municipiosFrequentes = Cache::remember('municipios_frequentes', now()->addHours(12), function() {
       return Municipio::select('id_municipio as value', 'nome_municipio as label')
           ->orderBy('nome_municipio')
           ->limit(20)
           ->get();
   });
   ```

## Troubleshooting

### Se os dropdowns não aparecerem

1. **Limpe o cache da aplicação** após atualizar o componente:

```bash
php artisan view:clear
php artisan optimize:clear
```

2. **Verifique conflitos de z-index**:
   - O dropdown usa `z-index: 10000`
   - Certifique-se que não há outros elementos com z-index maior que estejam bloqueando a visualização

3. **Problema de inicialização do AlpineJS**:
   - Verifique se o AlpineJS está sendo inicializado corretamente
   - Se estiver usando outras bibliotecas JavaScript, verifique conflitos

### Script para Diagnóstico

Se estiver tendo problemas com os dropdowns, pode adicionar este script para diagnóstico:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se o AlpineJS está carregado corretamente
    if (typeof window.Alpine === 'undefined') {
        console.error('DIAGNÓSTICO: AlpineJS não está disponível globalmente');
        return;
    }
    
    // Verificar a versão do AlpineJS
    console.log('DIAGNÓSTICO: Versão do AlpineJS:', Alpine.version || 'Não disponível');
    
    // Verificar se o elemento portal existe
    const portalRoot = document.getElementById('portal-root');
    if (!portalRoot) {
        console.error('DIAGNÓSTICO: Elemento #portal-root não encontrado');
    } else {
        console.log('DIAGNÓSTICO: Elemento #portal-root encontrado');
    }
    
    // Adicionar logs para o select de municípios
    const municipioButton = document.getElementById('id_municipio-button');
    if (municipioButton) {
        console.log('DIAGNÓSTICO: Botão do município encontrado');
        
        // Adicionar listener manual para testar o comportamento do click
        municipioButton.addEventListener('click', function(e) {
            console.log('DIAGNÓSTICO: Botão do município clicado manualmente');
        });
    }
    
    // Verifique os componentes que usam o alpine:
    document.querySelectorAll('[x-data*="asyncSearchableSelect"]').forEach(el => {
        console.log('DIAGNÓSTICO: Componente encontrado:', el.getAttribute('name'));
    });
});
```

### Caso sejam necessários ajustes adicionais

Se mesmo após a atualização algum select específico apresentar problemas:

1. Inspect o elemento no navegador para verificar se o Alpine.js está inicializando corretamente
2. Verifique o console do navegador para erros relacionados ao componente
3. Certifique-se que o formato dos dados passados ao componente está correto

### Problemas com o formato de dados

Verifique se o formato dos dados das opções está correto:

```php
// Formato esperado
$options = [
    ['value' => 1, 'label' => 'Opção 1'],
    ['value' => 2, 'label' => 'Opção 2'],
];

// Para valores diferentes, configure os atributos valueField e textField
<x-forms.smart-select 
    ...
    valueField="id_municipio"
    textField="nome_municipio"
/>
```