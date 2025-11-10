# Documentação da Diretiva Blade `@statusBadge`

## Visão Geral

A diretiva Blade `@statusBadge` foi criada para exibir um rótulo estilizado (badge) que representa o status de um item. Ela é útil para exibir informações como "QUITADO", "PARCIAL" ou outros estados, com cores e estilos específicos, independentemente de o texto estar em letras maiúsculas ou minúsculas.

## Instalação

A diretiva já está registrada no arquivo `AppServiceProvider.php`. Certifique-se de que o método `boot()` contém o seguinte código:

```php
Blade::directive('statusBadge', function ($status) {
    return "<?php
        \$statusLower = strtolower($status);
        \$classes = match (\$statusLower) {
            'quitado' => 'bg-green-100 text-green-800',
            'parcial' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-red-100 text-red-800',
        };
    ?>
    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \$classes }}\">
        {{ ucfirst($status) }}
    </span>";
});
```

## Uso Básico

Para usar a diretiva, basta chamá-la em qualquer arquivo Blade e passar o status como argumento:

```blade
@statusBadge('QUITADO')
```

### Exemplo em uma Tabela

```blade
<x-tables.cell>
    @statusBadge($ipvaveiculo->status_ipva)
</x-tables.cell>
```

## Resultado

A diretiva renderiza um elemento `<span>` com as classes CSS apropriadas para estilização e o texto formatado com a primeira letra maiúscula. Por exemplo:

### Para o status `QUITADO`:
```html
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Quitado
</span>
```

### Para o status `PARCIAL`:
```html
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
    Parcial
</span>
```

### Para qualquer outro status:
```html
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
    [Status]
</span>
```

## Personalização

Se for necessário alterar as cores ou estilos, edite o `match` dentro da diretiva no arquivo `AppServiceProvider.php`:

```php
\$classes = match (\$statusLower) {
    'quitado' => 'bg-green-200 text-green-900', // Exemplo de alteração
    'parcial' => 'bg-yellow-200 text-yellow-900',
    default => 'bg-red-200 text-red-900',
};
```

## Boas Práticas

1. **Consistência**: Use a diretiva em vez de duplicar a lógica de estilização em diferentes partes do código.
2. **Semântica**: Certifique-se de que os textos dos status sejam claros e descritivos.
3. **Manutenção**: Centralize as alterações de estilo na diretiva para facilitar a manutenção.

## Solução de Problemas

### A diretiva não está funcionando

1. Verifique se o método `boot()` no `AppServiceProvider.php` contém o registro da diretiva.
2. Certifique-se de que o cache de views foi limpo:
   ```bash
   php artisan view:clear
   ```
3. Confirme se o arquivo Blade onde a diretiva está sendo usada está sendo carregado corretamente.

### Estilos não estão sendo aplicados

1. Verifique se o Tailwind CSS está configurado corretamente no projeto.
2. Certifique-se de que as classes CSS usadas na diretiva não estão sendo sobrescritas por outros estilos.

## Exemplos Avançados

### Uso com Traduções

Se o status for armazenado em inglês no banco de dados, mas você quiser exibi-lo traduzido, use a função `__()` para traduzir o texto:

```blade
@statusBadge(__('status.' . $ipvaveiculo->status_ipva))
```

Certifique-se de que as traduções estão definidas no arquivo de linguagem apropriado, como `resources/lang/pt_BR/status.php`:

```php
return [
    'quitado' => 'Quitado',
    'parcial' => 'Parcial',
    'pendente' => 'Pendente',
];
```

### Uso com Dados Dinâmicos

A diretiva pode ser usada em qualquer lugar onde um status dinâmico precise ser exibido:

```blade
@statusBadge($pedido->status)
```

## Conclusão

A diretiva `@statusBadge` simplifica a exibição de rótulos estilizados para status, promovendo consistência e reutilização de código. Ela é altamente personalizável e fácil de usar, tornando-a uma ferramenta poderosa para melhorar a interface do usuário em projetos Laravel.
