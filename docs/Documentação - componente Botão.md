# Documentação do Componente de Botão Laravel

## Visão Geral

O componente `<x-forms.button>` é uma solução flexível para padronização de botões em projetos Laravel, oferecendo várias opções de customização com Tailwind CSS. Este componente unifica o estilo da interface e melhora a consistência visual, além de facilitar manutenções futuras.

## Instalação

1. Crie o arquivo da classe do componente:

```bash
mkdir -p app/View/Components/Forms
```

2. Adicione o arquivo `Button.php` em `app/View/Components/Forms/`:

3. Crie o diretório para o template blade:

```bash
mkdir -p resources/views/components/forms
```

4. Adicione o template `button.blade.php` em `resources/views/components/forms/`:

5. (Opcional) Registre o componente no `AppServiceProvider.php`:

```php
use App\View\Components\Forms\Button;
use Illuminate\Support\Facades\Blade;

public function boot()
{
    Blade::component('forms.button', Button::class);
}
```

## Uso Básico

```blade
<x-forms.button>
    Botão Padrão
</x-forms.button>
```

## Propriedades

| Propriedade  | Tipo     | Padrão    | Descrição                                       | Opções                                   |
|--------------|----------|-----------|--------------------------------------------------|------------------------------------------|
| `type`       | string   | primary   | Define o esquema de cores do botão               | primary, secondary, success, danger      |
| `variant`    | string   | filled    | Define o estilo visual                           | filled, outlined, text                   |
| `size`       | string   | md        | Define o tamanho do botão                        | sm, md, lg                               |
| `href`       | string   | null      | Transforma o botão em um link se definido        | URL válida                               |
| `buttonType` | string   | button    | Define o tipo HTML do botão                      | button, submit, reset                    |
| `disabled`   | boolean  | false     | Define se o botão está desabilitado              | true, false                              |
| `class`      | string   | ''        | Classes adicionais para personalização           | Classes CSS válidas                      |

## Exemplos

### Tipos de Botões

```blade
{{-- Botão Primário (padrão) --}}
<x-forms.button>
    Botão Primário
</x-forms.button>

{{-- Botão Secundário --}}
<x-forms.button type="secondary">
    Botão Secundário
</x-forms.button>

{{-- Botão de Sucesso --}}
<x-forms.button type="success">
    Botão de Sucesso
</x-forms.button>

{{-- Botão de Perigo/Alerta --}}
<x-forms.button type="danger">
    Botão de Perigo
</x-forms.button>
```

### Variantes

```blade
{{-- Preenchido (padrão) --}}
<x-forms.button>
    Botão Preenchido
</x-forms.button>

{{-- Contornado --}}
<x-forms.button variant="outlined">
    Botão Contornado
</x-forms.button>

{{-- Apenas Texto --}}
<x-forms.button variant="text">
    Botão Texto
</x-forms.button>
```

### Tamanhos

```blade
{{-- Pequeno --}}
<x-forms.button size="sm">
    Botão Pequeno
</x-forms.button>

{{-- Médio (padrão) --}}
<x-forms.button>
    Botão Médio
</x-forms.button>

{{-- Grande --}}
<x-forms.button size="lg">
    Botão Grande
</x-forms.button>
```

### Uso como Link

```blade
<x-forms.button 
    href="{{ route('admin.dashboard') }}"
    type="secondary" 
    variant="outlined">
    Voltar ao Dashboard
</x-forms.button>
```

### Botão de Submissão de Formulário

```blade
<x-forms.button button-type="submit" type="success">
    Salvar Alterações
</x-forms.button>
```

### Botão Desabilitado

```blade
<x-forms.button disabled>
    Indisponível
</x-forms.button>
```

### Combinando Propriedades

```blade
<x-forms.button 
    type="danger" 
    variant="outlined" 
    size="lg" 
    class="mt-4 w-full">
    Excluir Conta
</x-forms.button>
```

## Funcionalidades Integradas

- **Efeito de Clique**: Todos os botões já possuem efeito de escala e mudança de cor ao clicar
- **Estados Hover e Focus**: Feedbacks visuais para interação do usuário
- **Acessibilidade**: Atributos apropriados para botões desabilitados
- **Responsividade**: Dimensionamento adequado para diferentes dispositivos

## Customização Avançada

### Adicionar Ícones

```blade
<x-forms.button>
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <!-- SVG path -->
    </svg>
    Botão com Ícone
</x-forms.button>
```

### Extensão do Componente

É possível estender o componente para criar variações específicas:

```php
// App\View\Components\Forms\DeleteButton.php
namespace App\View\Components\Forms;

class DeleteButton extends Button
{
    public function __construct($size = 'md', $href = null, $buttonType = 'button', $disabled = false, $class = '')
    {
        parent::__construct('danger', 'filled', $size, $href, $buttonType, $disabled, $class);
    }
}
```

```blade
{{-- resources/views/components/forms/delete-button.blade.php --}}
<x-forms.button
    :type="$type"
    :variant="$variant"
    :size="$size"
    :href="$href"
    :button-type="$buttonType"
    :disabled="$disabled"
    :class="$class"
    {{ $attributes }}
>
    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <!-- Ícone de lixeira -->
    </svg>
    {{ $slot }}
</x-forms.button>
```

## Boas Práticas

1. **Consistência**: Use o componente em vez de botões HTML puros para manter consistência visual
2. **Semântica**: Escolha o tipo adequado para cada ação (success para confirmações, danger para exclusões, etc.)
3. **Acessibilidade**: Forneça textos claros e descritivos para os botões
4. **Performance**: Evite adicionar muitas classes personalizadas que possam sobrescrever o comportamento padrão

## Solução de Problemas

### O componente não está disponível nas views

Verifique se:
- Os arquivos estão nos diretórios corretos
- O componente está registrado (se necessário)
- O cache de views foi limpo: `php artisan view:clear`

### Estilos não estão sendo aplicados corretamente

Verifique se:
- O Tailwind CSS está configurado corretamente no projeto
- As classes não estão sendo sobrescritas por outros estilos
- O arquivo de configuração do Tailwind inclui as classes utilizadas

## Contribuindo

Para adicionar novos tipos ou variantes ao componente, modifique os arrays de cores e tamanhos no método `getColorClasses()` e `getSizeClasses()` na classe `Button.php`.
