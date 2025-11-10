# Documentação: componente loading

**Versão 1.0.0 - Data: 26/02/2025**

Um componente flexível de loading/spinner para o Laravel Blade, com várias opções de personalização.

## Instalação

O arquivo `loading.blade.php` se encontra dentro da pasta `resources/views/components/ui/`.

## Uso Básico

```blade
<x-ui.loading />
```

## Opções

| Propriedade | Tipo    | Padrão        | Descrição                                          |
|-------------|---------|---------------|---------------------------------------------------|
| message     | string  | "Carregando..." | Texto exibido abaixo do spinner                   |
| size        | string  | "md"          | Tamanho do spinner: "sm", "md", "lg", "xl"         |
| color       | string  | "primary"     | Cor do spinner: "primary", "secondary", "success", "danger", "warning", "info" |
| animation   | string  | "spin"        | Tipo de animação: "spin", "pulse", "bounce"        |
| fullscreen  | boolean | false         | Se verdadeiro, cria um overlay em tela cheia       |

## Exemplos

### Loading simples:
```blade
<x-ui.loading />
```

### Com mensagem personalizada:
```blade
<x-ui.loading message="Processando dados..." />
```

### Tamanhos disponíveis (sm, md, lg, xl):
```blade
<x-ui.loading size="sm" />
<x-ui.loading size="lg" />
```

### Cores disponíveis:
```blade
<x-ui.loading color="success" />
<x-ui.loading color="danger" />
<x-ui.loading color="warning" />
```

### Animações diferentes:
```blade
<x-ui.loading animation="pulse" />
<x-ui.loading animation="bounce" />
```

### Loading em tela cheia (overlay):
```blade
<x-ui.loading fullscreen="true" />
```

### Combinando opções:
```blade
<x-ui.loading 
    message="Salvando alterações..." 
    size="lg" 
    color="success" 
    fullscreen="true" 
/>
```

## Integração com Alpine.js

Para exibir/ocultar o loading dinamicamente com Alpine.js:

```blade
<div x-data="{ isLoading: false }">
    <button 
        @click="isLoading = true; setTimeout(() => isLoading = false, 2000)"
        class="px-4 py-2 bg-indigo-600 text-white rounded"
    >
        Mostrar Loading
    </button>
    
    <div x-show="isLoading">
        <x-ui.loading fullscreen="true" message="Processando..." />
    </div>
</div>
```

## Uso em formulários

Exemplo de uso em um formulário para mostrar o indicador de loading durante o envio:

```blade
<form x-data="{ submitting: false }" @submit="submitting = true">
    <!-- Campos do formulário -->
    
    <button 
        type="submit" 
        class="px-4 py-2 bg-indigo-600 text-white rounded"
        :disabled="submitting"
    >
        <span x-show="!submitting">Salvar</span>
        <span x-show="submitting" class="flex items-center">
            <x-ui.loading size="sm" message="" />
            <span class="ml-2">Enviando...</span>
        </span>
    </button>

    <!-- Overlay de loading -->
    <div x-show="submitting">
        <x-ui.loading fullscreen="true" message="Salvando dados..." />
    </div>
</form>
```

## Personalização adicional

O componente aceita atributos adicionais que são mesclados com a div do container:

```blade
<x-ui.loading class="my-custom-class" id="my-loader" />
```