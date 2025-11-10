# Componente de Notificação

## Visão Geral

O componente de notificação é uma solução elegante para exibir mensagens de feedback ao usuário no formato de toast notifications. O componente suporta diferentes tipos de notificações (sucesso, erro, aviso e informação), cada uma com cores e ícones distintos. As notificações são exibidas temporariamente e desaparecem automaticamente após um período definido.

## Características

- **Tipos de notificação:** Sucesso, erro, aviso e informação
- **Cores específicas por tipo:** Verde para sucesso, vermelho para erro, amarelo para aviso e azul para informação
- **Animações suaves:** Transições de entrada e saída
- **Duração configurável:** Tempo de exibição personalizável
- **Responsivo:** Layout adaptável para diferentes tamanhos de tela
- **Botão de fechamento:** Permite que o usuário feche a notificação manualmente

## Instalação

Certifique-se de que seu projeto Laravel já tenha configurado o [Alpine.js](https://alpinejs.dev/), pois este componente depende dele para suas funcionalidades interativas.

## Estrutura do Componente

O componente é composto por dois arquivos principais:

1. `notification.blade.php` - O arquivo de visualização do componente
2. Código para invocar o componente na sua aplicação

## Como Usar

### 1. Crie o Arquivo de Componente

Coloque o arquivo `notification.blade.php` na pasta `resources/views/components/` do seu projeto Laravel.

### 2. Exiba a Notificação em sua View

Adicione o seguinte código em suas views onde deseja que as notificações apareçam:

```blade
@if (session('notification'))
    <x-notification :notification="session('notification')" />
@endif
```

### 3. Dispare uma Notificação no Controller

```php
return redirect()->back()->with('notification', [
    'type' => 'success',
    'title' => 'Operação concluída',
    'message' => 'Os dados foram salvos com sucesso.',
    'duration' => 3000, // opcional (padrão: 5000ms)
]);
```

Exemplo para retornar a mesma página
```php
return back()->with('notification', [
    'type' => 'error',
    'title' => 'Erro de validação',
    'message' => 'A data de vencimento não pode ser anterior ou igual à data de certificação',
    'duration' => 5000,
])->withInput();
```

## Parâmetros da Notificação

| Parâmetro | Tipo | Obrigatório | Descrição | Valores Possíveis |
|-----------|------|-------------|-----------|-------------------|
| `type` | string | Sim | Define o tipo e estilo da notificação | `success`, `error`, `warning`, `info` |
| `title` | string | Sim | Título da notificação | Qualquer texto |
| `message` | string | Sim | Conteúdo principal da notificação | Qualquer texto |
| `duration` | number | Não | Duração em milissegundos (padrão: 5000) | Qualquer número positivo |

## Exemplos de Uso

### Notificação de Sucesso

```php
return redirect()->route('dashboard')->with('notification', [
    'type' => 'success',
    'title' => 'Conta criada',
    'message' => 'Sua conta foi criada com sucesso.',
]);
```

### Notificação de Erro

```php
return redirect()->back()->with('notification', [
    'type' => 'error',
    'title' => 'Erro no servidor',
    'message' => 'Não foi possível processar sua solicitação.',
]);
```

### Notificação de Aviso

```php
return redirect()->back()->with('notification', [
    'type' => 'warning',
    'title' => 'Atenção',
    'message' => 'Sua assinatura expirará em 3 dias.',
    'duration' => 7000,
]);
```

### Notificação de Informação

```php
return redirect()->back()->with('notification', [
    'type' => 'info',
    'title' => 'Dica',
    'message' => 'Você pode clicar em "Salvar" para manter suas alterações.',
]);
```

## Personalização

### Alterando as Cores

Para alterar as cores padrão, modifique o array `$colors` no início do arquivo `notification.blade.php`:

```php
$colors = [
    'success' => 'bg-green-500', // Altere para qualquer classe do Tailwind
    'error' => 'bg-red-500',
    'warning' => 'bg-yellow-500',
    'info' => 'bg-blue-500',
];
```

### Alterando os Ícones

Para alterar os ícones, modifique o array `$icons` no início do arquivo:

```php
$icons = [
    'success' => 'M5 13l4 4L19 7', // Path SVG para ícone de sucesso
    'error' => 'M6 18L18 6M6 6l12 12',
    'warning' => '...',
    'info' => '...',
];
```

## Considerações Técnicas

- O componente usa Alpine.js para gerenciar o estado e as animações.
- As animações de entrada e saída são controladas por classes de transição do Tailwind.
- A posição padrão da notificação é no canto superior direito em telas maiores (`sm`) e centralizada na parte inferior em dispositivos móveis.
- O z-index é definido como 50 para garantir que a notificação seja exibida acima de outros elementos.

## Compatibilidade

Este componente é compatível com:

- Laravel 8.x ou superior
- Tailwind CSS 2.x ou superior
- Alpine.js 2.x ou superior

## Troubleshooting

### A notificação não aparece

- Verifique se você está passando a notificação corretamente via session.
- Confirme que Alpine.js está carregado no seu layout.
- Verifique se não há conflitos de z-index com outros elementos.

### A notificação não desaparece automaticamente

- Verifique se o Alpine.js está funcionando corretamente.
- Confirme que o valor de `duration` é um número válido em milissegundos.
