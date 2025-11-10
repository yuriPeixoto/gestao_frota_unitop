# Documentação do Componente de Confirmação

**Versão 1.0.0 - Data: 14/03/2025**

Este documento explica como usar o componente de confirmação, que substitui a funcionalidade do `TQuestion` do MadBuilder no Laravel.

## Visão Geral

O componente de confirmação consiste em duas partes principais:

1. **Modal de Confirmação** (`confirmation-modal.blade.php`): Um componente Blade que exibe um modal interativo.
2. **Helper JavaScript** (`confirmation.js`): Um script que facilita a ativação do modal.

Esse sistema permite que você solicite facilmente confirmação do usuário antes de executar ações críticas, como exclusão de registros, cancelamento de processos, etc.

## O Componente Blade

O componente `confirmation-modal.blade.php` é construído com Alpine.js e Tailwind CSS. Ele fornece um modal de confirmação completo com:

- Título personalizável
- Mensagem personalizada
- Botões de confirmação e cancelamento personalizáveis
- Suporte a ícones
- Opções de estilo flexíveis

### Parâmetros do Componente

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `id` | string | `confirmation-modal` | ID do modal |
| `title` | string | `Confirmação` | Título do modal |
| `confirmText` | string | `Confirmar` | Texto do botão de confirmação |
| `cancelText` | string | `Cancelar` | Texto do botão de cancelamento |
| `confirmButtonClass` | string | `bg-indigo-600 hover:bg-indigo-700` | Classes CSS do botão de confirmação |
| `cancelButtonClass` | string | `bg-gray-500 hover:bg-gray-600` | Classes CSS do botão de cancelamento |
| `icon` | string | `null` | Ícone a ser exibido (HTML ou emoji) |
| `iconClass` | string | `text-yellow-400` | Classes CSS do ícone |
| `width` | string | `max-w-md` | Largura do modal |

## O Helper JavaScript

O arquivo `confirmation.js` fornece uma interface simples para interagir com o modal de confirmação:

### Função `confirmAction`

A função global `confirmAction()` é o principal método para abrir o modal de confirmação:

```javascript
window.confirmAction({
    title: 'Título da confirmação',
    message: 'Tem certeza que deseja realizar esta ação?',
    confirmText: 'Sim',
    cancelText: 'Não',
    confirmRoute: '/rota/para/confirmar',
    method: 'DELETE',
    params: { id: 123 },
    onConfirm: function() { /* código a executar */ },
    onCancel: function() { /* código a executar */ }
});
```

#### Parâmetros da função `confirmAction`

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `title` | string | Título do modal de confirmação |
| `message` | string | Mensagem a ser exibida |
| `confirmText` | string | Texto do botão de confirmação |
| `cancelText` | string | Texto do botão de cancelamento |
| `confirmRoute` | string | Rota para a qual enviar a requisição quando confirmado |
| `method` | string | Método HTTP a ser usado (GET, POST, PUT, DELETE, etc.) |
| `params` | object | Parâmetros a serem enviados na requisição |
| `onConfirm` | function | Função de callback quando o usuário confirma |
| `onCancel` | function | Função de callback quando o usuário cancela |

### Atributos data para HTML

O helper também detecta automaticamente elementos HTML com o atributo `data-confirm="true"` e adiciona a funcionalidade de confirmação a eles.

## Como Usar (3 Opções)

### Opção 1: Atributos data em HTML

Este método é útil quando você deseja adicionar confirmação a elementos HTML estáticos, como botões em tabelas.

```html
<button 
    class="bg-red-600 text-white px-4 py-2 rounded"
    data-confirm="true" 
    data-title="Confirmar Exclusão" 
    data-message="Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita."
    data-confirm-text="Sim, excluir"
    data-cancel-text="Cancelar"
    data-route="/users/123"
    data-method="DELETE"
>
    Excluir
</button>
```

#### Atributos disponíveis:

| Atributo | Descrição |
|----------|-----------|
| `data-confirm` | Define que o elemento precisa de confirmação (deve ser "true") |
| `data-title` | Título do modal de confirmação |
| `data-message` | Mensagem de confirmação |
| `data-confirm-text` | Texto do botão de confirmação |
| `data-cancel-text` | Texto do botão de cancelamento |
| `data-route` | URL para onde enviar a requisição ao confirmar |
| `data-method` | Método HTTP a usar (GET, POST, PUT, DELETE, etc.) |
| `data-params` | JSON string com parâmetros adicionais a enviar |

### Opção 2: Chamada via JavaScript com redirecionamento

Este método é útil quando você precisa chamar a confirmação a partir de um evento JavaScript, como um clique em um botão, e quer redirecionar para uma rota específica após a confirmação.

```html
<button 
    class="bg-red-600 text-white px-4 py-2 rounded"
    onclick="confirmAction({
        title: 'Confirmar Exclusão',
        message: 'Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.',
        confirmText: 'Sim, excluir',
        cancelText: 'Cancelar',
        confirmRoute: '/users/123',
        method: 'DELETE'
    })"
>
    Excluir
</button>
```

No exemplo acima, ao clicar em "Sim, excluir", o sistema enviará uma requisição DELETE para "/users/123".

### Opção 3: Chamada via JavaScript com callbacks

Este método é ideal quando você precisa executar código personalizado após a confirmação, em vez de simplesmente redirecionar para uma rota.

```html
<button 
    class="bg-blue-600 text-white px-4 py-2 rounded"
    onclick="confirmAction({
        title: 'Encerrar Ordem de Serviço',
        message: 'Realmente deseja encerrar a Ordem de Serviço #12345?',
        confirmText: 'Sim, encerrar',
        cancelText: 'Não, voltar',
        onConfirm: function() {
            // Código a ser executado quando o usuário confirmar
            console.log('Usuário confirmou!');
            
            // Exemplo: chamada AJAX
            fetch('/api/ordens/12345/encerrar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar UI ou redirecionar
                    window.location.href = '/ordens';
                }
            });
        },
        onCancel: function() {
            // Código a ser executado quando o usuário cancelar
            console.log('Usuário cancelou!');
        }
    })"
>
    Encerrar OS
</button>
```

## Exemplos de Uso Prático

### Exemplo 1: Excluir um registro

```blade
<button 
    data-confirm="true" 
    data-title="Excluir Veículo" 
    data-message="Tem certeza que deseja excluir o veículo <strong>{{ $veiculo->placa }}</strong>? Esta ação não pode ser desfeita."
    data-route="{{ route('admin.veiculos.destroy', $veiculo->id) }}"
    data-method="DELETE"
    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
>
    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
</button>
```

### Exemplo 2: Usando dentro de um loop

```blade
@foreach ($abastecimentos as $abastecimento)
    <tr>
        <td>{{ $abastecimento->id }}</td>
        <td>{{ $abastecimento->placa }}</td>
        <td>
            <button 
                onclick="confirmAction({
                    title: 'Cancelar Abastecimento',
                    message: 'Deseja cancelar o abastecimento do veículo <strong>{{ $abastecimento->placa }}</strong> realizado em {{ $abastecimento->data_abastecimento->format('d/m/Y') }}?',
                    confirmText: 'Sim, cancelar',
                    cancelText: 'Não',
                    confirmRoute: '{{ route('admin.abastecimentomanual.cancelar', $abastecimento->id) }}',
                    method: 'POST'
                })"
                class="btn btn-danger btn-sm"
            >
                Cancelar
            </button>
        </td>
    </tr>
@endforeach
```

### Exemplo 3: Usando com AJAX

```blade
<button 
    class="btn btn-warning"
    onclick="confirmAction({
        title: 'Reativar Veículo',
        message: 'Deseja reativar o veículo {{ $veiculo->placa }}?',
        onConfirm: function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route('admin.veiculos.reativar', $veiculo->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar a UI sem recarregar a página
                    document.getElementById('status-badge-' + {{ $veiculo->id }}).className = 'badge bg-success';
                    document.getElementById('status-badge-' + {{ $veiculo->id }}).innerText = 'Ativo';
                    
                    // Opcional: exibir notificação
                    alert('Veículo reativado com sucesso!');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao reativar o veículo');
            });
        }
    })"
>
    Reativar
</button>
```

## Tratamento no Controller

No seu controller Laravel, não é necessário fazer nada especial. Trate a requisição normalmente, como faria com qualquer outra ação:

```php
public function destroy($id)
{
    $recurso = Recurso::findOrFail($id);
    $recurso->delete();
    
    return redirect()->route('recursos.index')
        ->with('success', 'Recurso excluído com sucesso!');
}
```

## Personalizando o Estilo

Você pode personalizar o estilo do modal em sua chamada:

```php
<x-ui.confirmation-modal 
    id="my-custom-modal"
    confirmButtonClass="bg-red-600 hover:bg-red-700" 
    cancelButtonClass="bg-gray-500 hover:bg-gray-600"
    icon="⚠️" 
    iconClass="text-yellow-500"
    width="max-w-lg"
/>
```

## Conclusão

O componente de confirmação é uma solução flexível e poderosa para substituir a funcionalidade do TQuestion do Adianti Framework. Ele se integra perfeitamente ao ecossistema Laravel e pode ser usado de várias maneiras para atender a diferentes necessidades.

- Use **atributos data** para casos simples e estáticos
- Use **chamadas JavaScript com rotas** para ações que devem redirecionar
- Use **chamadas JavaScript com callbacks** para controle total e personalização

Esta implementação mantém a mesma funcionalidade do TQuestion, mas com uma interface mais moderna e integrada ao seu projeto Laravel.