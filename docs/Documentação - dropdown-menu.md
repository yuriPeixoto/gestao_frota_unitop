# DropdownMenu Component

Um componente Blade flexível e reutilizável para criar menus dropdown com suporte a links, botões, Livewire e JavaScript personalizado.

## 📋 Índice

- [Instalação](#instala%C3%A7%C3%A3o)
- [Uso Básico](#uso-b%C3%A1sico)
- [Propriedades](#propriedades)
- [Tipos de Menu Items](#tipos-de-menu-items)
- [Exemplos de Uso](#exemplos-de-uso)
- [Personalização](#personaliza%C3%A7%C3%A3o)
- [API Reference](#api-reference)
- [FAQ](#faq)


## 🚀 Instalação

### 1. Criar o Component

```bash
php artisan make:component DropdownMenu
```


### 2. Component Class

Arquivo: `app/View/Components/DropdownMenu.php`

```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DropdownMenu extends Component
{
    public $buttonText;
    public $buttonIcon;
    public $menuItems;
    public $buttonClass;
    public $menuClass;

    public function __construct(
        $buttonText = 'Ações',
        $buttonIcon = 'gear',
        $menuItems = [],
        $buttonClass = '',
        $menuClass = ''
    ) {
        $this->buttonText = $buttonText;
        $this->buttonIcon = $buttonIcon;
        $this->menuItems = $menuItems;
        $this->buttonClass = $buttonClass;
        $this->menuClass = $menuClass;
    }

    public function render()
    {
        return view('components.dropdown-menu');
    }
}
```


### 3. Template do Component

Arquivo: `resources/views/components/dropdown-menu.blade.php`

```blade
<div class="relative inline-block">
    <button class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2 {{ $buttonClass }}">
        @if($buttonIcon)
            <x-dynamic-component :component="'icons.' . $buttonIcon" class="w-4 h-4" />
        @endif
        <span>{{ $buttonText }}</span>
    </button>
    
    <ul class="dropdown-menu absolute left-0 mt-2 w-48 bg-white border rounded shadow-lg hidden z-50 {{ $menuClass }}">
        @foreach($menuItems as $item)
            <li>
                @if($item['type'] === 'link')
                    <a href="{{ $item['url'] ?? '#' }}" 
                       class="flex items-center px-4 py-2 hover:bg-gray-100 {{ $item['class'] ?? 'text-gray-700' }}"
                       @if(isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif>
                        @if(isset($item['icon']))
                            <x-dynamic-component :component="'icons.' . $item['icon']" 
                                               class="h-4 w-4 mr-2 {{ $item['iconClass'] ?? '' }}" />
                        @endif
                        {{ $item['text'] }}
                    </a>
                @elseif($item['type'] === 'button')
                    <button class="flex items-center px-4 py-2 hover:bg-gray-100 w-full text-left {{ $item['class'] ?? 'text-gray-700' }}"
                            @if(isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif
                            @if(isset($item['wireClick'])) wire:click="{{ $item['wireClick'] }}" @endif>
                        @if(isset($item['icon']))
                            <x-dynamic-component :component="'icons.' . $item['icon']" 
                                               class="h-4 w-4 mr-2 {{ $item['iconClass'] ?? '' }}" />
                        @endif
                        {{ $item['text'] }}
                    </button>
                @elseif($item['type'] === 'divider')
                    <hr class="my-1">
                @endif
            </li>
        @endforeach
        
        {{ $slot }}
    </ul>
</div>

@pushOnce('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll(".dropdown-button");

    buttons.forEach(button => {
        button.addEventListener("click", function(event) {
            event.stopPropagation();

            document.querySelectorAll(".dropdown-menu").forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.add("hidden");
                }
            });

            this.nextElementSibling.classList.toggle("hidden");
        });
    });

    document.addEventListener("click", function() {
        document.querySelectorAll(".dropdown-menu").forEach(menu => {
            menu.classList.add("hidden");
        });
    });
});
</script>
@endPushOnce
```


## 🎯 Uso Básico

```blade
@php
$menuItems = [
    [
        'type' => 'link',
        'url' => '/edit',
        'text' => 'Editar',
        'icon' => 'edit',
        'class' => 'text-blue-600'
    ],
    [
        'type' => 'link',
        'url' => '/delete',
        'text' => 'Excluir',
        'icon' => 'trash',
        'class' => 'text-red-600'
    ]
];
@endphp

<x-dropdown-menu :menu-items="$menuItems" />
```


## ⚙️ Propriedades

| Propriedade | Tipo | Padrão | Descrição |
| :-- | :-- | :-- | :-- |
| `button-text` | String | 'Ações' | Texto do botão dropdown |
| `button-icon` | String | 'gear' | Ícone do botão (nome do componente sem 'icons.') |
| `menu-items` | Array | `[]` | Array de itens do menu |
| `button-class` | String | `''` | Classes CSS adicionais para o botão |
| `menu-class` | String | `''` | Classes CSS adicionais para o menu |

## 📝 Tipos de Menu Items

### 1. Link (`type: 'link'`)

```php
[
    'type' => 'link',
    'url' => '/caminho',           // URL de destino
    'text' => 'Texto do Link',     // Texto exibido
    'icon' => 'nome-do-icone',     // Ícone (opcional)
    'class' => 'text-blue-600',    // Classes CSS do link
    'iconClass' => 'text-blue-600', // Classes CSS do ícone
    'onclick' => 'funcaoJS()'      // JavaScript onclick (opcional)
]
```


### 2. Button (`type: 'button'`)

```php
[
    'type' => 'button',
    'text' => 'Texto do Botão',
    'icon' => 'nome-do-icone',     // Ícone (opcional)
    'class' => 'text-red-600',     // Classes CSS do botão
    'iconClass' => 'text-red-600', // Classes CSS do ícone
    'onclick' => 'funcaoJS()',     // JavaScript onclick (opcional)
    'wireClick' => 'metodoLivewire()' // Livewire wire:click (opcional)
]
```


### 3. Divider (`type: 'divider'`)

```php
[
    'type' => 'divider'
]
```


## 🔧 Exemplos de Uso

### Exemplo 1: CRUD Básico

```blade
@php
$crudItems = [
    [
        'type' => 'link',
        'url' => route('users.show', $user->id),
        'text' => 'Visualizar',
        'icon' => 'eye',
        'class' => 'text-gray-700'
    ],
    [
        'type' => 'link',
        'url' => route('users.edit', $user->id),
        'text' => 'Editar',
        'icon' => 'edit',
        'class' => 'text-blue-600',
        'iconClass' => 'text-blue-600'
    ],
    [
        'type' => 'divider'
    ],
    [
        'type' => 'link',
        'url' => route('users.destroy', $user->id),
        'text' => 'Excluir',
        'icon' => 'trash',
        'class' => 'text-red-600',
        'iconClass' => 'text-red-600',
        'onclick' => 'return confirm("Tem certeza que deseja excluir?")'
    ]
];
@endphp

<x-dropdown-menu 
    button-text="Ações"
    button-icon="dots-vertical"
    :menu-items="$crudItems" 
/>
```


### Exemplo 2: Com Livewire

```blade
@php
$livewireItems = [
    [
        'type' => 'button',
        'text' => 'Aprovar',
        'icon' => 'check',
        'class' => 'text-green-600',
        'iconClass' => 'text-green-600',
        'wireClick' => 'approve(' . $item->id . ')'
    ],
    [
        'type' => 'button',
        'text' => 'Rejeitar',
        'icon' => 'x-mark',
        'class' => 'text-red-600',
        'iconClass' => 'text-red-600',
        'wireClick' => 'reject(' . $item->id . ')'
    ]
];
@endphp

<x-dropdown-menu 
    button-text="Status"
    button-icon="clock"
    :menu-items="$livewireItems" 
/>
```


### Exemplo 3: JavaScript Personalizado

```blade
@php
$jsItems = [
    [
        'type' => 'button',
        'text' => 'Exportar PDF',
        'icon' => 'document-arrow-down',
        'class' => 'text-purple-600',
        'onclick' => 'exportToPDF(' . $report->id . ')'
    ],
    [
        'type' => 'button',
        'text' => 'Enviar Email',
        'icon' => 'envelope',
        'class' => 'text-blue-600',
        'onclick' => 'sendEmail(' . $report->id . ')'
    ]
];
@endphp

<x-dropdown-menu 
    button-text="Exportar"
    button-icon="share"
    :menu-items="$jsItems" 
/>

<script>
function exportToPDF(id) {
    // Lógica de exportação
    console.log('Exportando PDF para ID:', id);
}

function sendEmail(id) {
    // Lógica de envio de email
    console.log('Enviando email para ID:', id);
}
</script>
```


### Exemplo 4: Com Slot Personalizado

```blade
<x-dropdown-menu button-text="Menu Customizado" button-icon="cog">
    <li>
        <a href="#" class="flex items-center px-4 py-2 text-indigo-600 hover:bg-gray-100">
            <x-icons.sparkles class="h-4 w-4 mr-2 text-indigo-600" />
            Ação Especial
        </a>
    </li>
    <li>
        <hr class="my-1">
    </li>
    <li>
        <button class="flex items-center px-4 py-2 hover:bg-gray-100 w-full text-left text-orange-600"
                onclick="alert('Funcionalidade personalizada!')">
            <x-icons.star class="h-4 w-4 mr-2 text-orange-600" />
            Funcionalidade Única
        </button>
    </li>
</x-dropdown-menu>
```


## 🎨 Personalização

### Estilização do Botão

```blade
<x-dropdown-menu 
    button-text="Menu Personalizado"
    button-class="bg-blue-500 text-white hover:bg-blue-600 border-blue-500"
    :menu-items="$items" 
/>
```


### Estilização do Menu

```blade
<x-dropdown-menu 
    button-text="Menu Estilizado"
    menu-class="w-64 bg-gray-50"
    :menu-items="$items" 
/>
```


### Menu Alinhado à Direita

```blade
<x-dropdown-menu 
    button-text="Menu Direita"
    menu-class="right-0 left-auto"
    :menu-items="$items" 
/>
```


## 📚 API Reference

### Propriedades do Component

```php
/**
 * @param string $buttonText - Texto do botão dropdown
 * @param string $buttonIcon - Nome do ícone (sem 'icons.')
 * @param array $menuItems - Array de itens do menu
 * @param string $buttonClass - Classes CSS adicionais para o botão
 * @param string $menuClass - Classes CSS adicionais para o menu
 */
```


### Estrutura do Menu Item

```php
[
    'type' => 'link|button|divider',     // Tipo do item
    'text' => 'string',                  // Texto exibido
    'url' => 'string',                   // URL (apenas para links)
    'icon' => 'string',                  // Nome do ícone
    'class' => 'string',                 // Classes CSS do item
    'iconClass' => 'string',             // Classes CSS do ícone
    'onclick' => 'string',               // JavaScript onclick
    'wireClick' => 'string'              // Livewire wire:click
]
```


## ❓ FAQ

### Como adicionar um novo tipo de item?

Edite o template do componente e adicione uma nova condição:

```blade
@elseif($item['type'] === 'custom')
    <div class="px-4 py-2">
        <!-- Seu HTML customizado -->
    </div>
@endif
```


### Como alterar a animação do dropdown?

Modifique as classes do menu no template:

```blade
<ul class="dropdown-menu ... transition-all duration-200 transform origin-top-left scale-95 opacity-0 hidden">
```

E ajuste o JavaScript para usar animações CSS em vez de `hidden`.

### Como usar com Alpine.js?

Substitua o JavaScript por Alpine.js:

```blade
<div class="relative inline-block" x-data="{ open: false }">
    <button @click="open = !open" class="...">
        <!-- botão -->
    </button>
    <ul x-show="open" @click.outside="open = false" class="...">
        <!-- menu -->
    </ul>
</div>
```


### Como adicionar suporte a tooltips?

Adicione suporte no array de itens:

```php
[
    'type' => 'link',
    'text' => 'Editar',
    'tooltip' => 'Editar este item'
]
```

E no template:

```blade
<a href="..." @if(isset($item['tooltip'])) title="{{ $item['tooltip'] }}" @endif>
```


## 🏷️ Versão

**v1.0.0** - Laravel 10+ | Tailwind CSS 3+ | PHP 8+

## 📄 Licença

MIT License - Sinta-se livre para usar e modificar conforme necessário.

**💡 Dica**: Para melhor performance, considere usar cache de views em produção com `php artisan view:cache`.

