# 📋 Table Modal Component - Documentação

Componente reutilizável para Laravel que cria modais com tabelas paginadas de forma simples e eficiente.

## 🚀 Instalação

1. Crie o arquivo `resources/views/components/table-modal.blade.php`
2. Cole o código do componente (fornecido abaixo)
3. Pronto para usar!

## 📁 Estrutura do Componente

```
resources/views/components/table-modal.blade.php
```


## 🎯 Funcionalidades

- ✅ **Modal responsivo** com Tailwind CSS
- ✅ **Paginação automática** configurável
- ✅ **Busca de dados via AJAX**
- ✅ **Suporte a campos aninhados** (ex: `user.profile.name`)
- ✅ **Múltiplos modais** na mesma página
- ✅ **Auto-inicialização** - zero JavaScript adicional
- ✅ **Fechamento via ESC** ou clique fora
- ✅ **Loading state** durante carregamento
- ✅ **Tratamento de erros**


## 📝 Como Usar

### 1. Definir as Colunas

```php
@php
$columns = [
    ['field' => 'id', 'label' => 'ID'],
    ['field' => 'nome', 'label' => 'Nome'],
    ['field' => 'usuario.email', 'label' => 'Email'], // Campo aninhado
    ['field' => 'valor', 'label' => 'Valor', 'class' => 'text-right'], // Com classe CSS
    ['field' => 'created_at', 'label' => 'Data', 'class' => 'whitespace-nowrap'],
];
@endphp
```


### 2. Incluir o Componente

```blade
<x-table-modal 
    modal-id="usuarios-{{ $item->id }}"
    title="Lista de Usuários"
    :columns="$columns"
    fetch-url="/api/usuarios/{{ $item->id }}/dados"
    :items-per-page="5"
    max-width="7xl"
/>
```


### 3. Criar o Trigger (Botão/Link)

```blade
<a href="#" 
   class="modal-trigger-usuarios-{{ $item->id }} btn btn-primary">
    Ver Detalhes
</a>
```


## ⚙️ Parâmetros do Componente

| Parâmetro | Tipo | Padrão | Descrição |
| :-- | :-- | :-- | :-- |
| `modal-id` | string | **obrigatório** | ID único do modal |
| `title` | string | "Modal" | Título do modal |
| `columns` | array | **obrigatório** | Configuração das colunas |
| `fetch-url` | string | **obrigatório** | URL para buscar os dados |
| `items-per-page` | int | 2 | Itens por página |
| `max-width` | string | "7xl" | Largura máxima (Tailwind) |

## 📊 Estrutura das Colunas

```php
$columns = [
    [
        'field' => 'campo_do_objeto',    // Campo do objeto (obrigatório)
        'label' => 'Nome da Coluna',    // Label da coluna (obrigatório)
        'class' => 'text-right'         // Classes CSS adicionais (opcional)
    ]
];
```


### Campos Aninhados

```php
// Para acessar propriedades aninhadas:
['field' => 'usuario.perfil.nome', 'label' => 'Nome do Usuário']
['field' => 'pedido.cliente.endereco.cidade', 'label' => 'Cidade']
```


## 🔗 Padrão de Nome das Classes Trigger

A classe do elemento trigger deve seguir o padrão:

```
modal-trigger-{modalId}
```

**Exemplos:**

- Modal ID: `usuarios-123` → Classe: `modal-trigger-usuarios-123`
- Modal ID: `produtos` → Classe: `modal-trigger-produtos`


## 🌐 API Endpoint

O endpoint deve retornar JSON no seguinte formato:

```json
{
    "success": true,
    "nfItens": [
        {
            "id": 1,
            "nome": "João",
            "email": "joao@email.com",
            "usuario": {
                "profile": {
                    "cidade": "São Paulo"
                }
            }
        }
    ]
}
```

**Alternativamente, pode usar `items` ao invés de `nfItens`:**

```json
{
    "success": true,
    "items": [...]
}
```


## 💡 Exemplos Práticos

### Exemplo 1: Modal Simples

```blade
@php
$colunasProdutos = [
    ['field' => 'id', 'label' => 'ID'],
    ['field' => 'nome', 'label' => 'Nome do Produto'],
    ['field' => 'preco', 'label' => 'Preço', 'class' => 'text-right']
];
@endphp

<!-- Em uma tabela -->
@foreach($pedidos as $pedido)
    <tr>
        <td>{{ $pedido->numero }}</td>
        <td>
            <button class="modal-trigger-produtos-{{ $pedido->id }} btn btn-info">
                Ver Produtos
            </button>
            
            <x-table-modal 
                modal-id="produtos-{{ $pedido->id }}"
                title="Produtos do Pedido #{{ $pedido->numero }}"
                :columns="$colunasProdutos"
                fetch-url="/pedidos/{{ $pedido->id }}/produtos"
                :items-per-page="10"
            />
        </td>
    </tr>
@endforeach
```


### Exemplo 2: Modal em Card

```blade
<div class="card">
    <div class="card-body">
        <h5>Nota Fiscal #{{ $nf->numero }}</h5>
        
        <a href="#" class="modal-trigger-itens-{{ $nf->id }} btn btn-primary">
            <i class="fas fa-eye"></i> Ver Itens
        </a>
    </div>
    
    <x-table-modal 
        modal-id="itens-{{ $nf->id }}"
        title="Itens da Nota Fiscal #{{ $nf->numero }}"
        :columns="$colunasItens"
        fetch-url="/notas-fiscais/{{ $nf->id }}/itens"
        :items-per-page="5"
        max-width="6xl"
    />
</div>
```


### Exemplo 3: Modal com Dropdown

```blade
<x-dropdown-menu button-text="Ações">
    <li>
        <a href="#" class="modal-trigger-detalhes-{{ $item->id }} dropdown-item">
            <i class="fas fa-search"></i> Ver Detalhes
        </a>
    </li>
    <li>
        <a href="/editar/{{ $item->id }}" class="dropdown-item">
            <i class="fas fa-edit"></i> Editar
        </a>
    </li>
</x-dropdown-menu>

<x-table-modal 
    modal-id="detalhes-{{ $item->id }}"
    title="Detalhes do Item"
    :columns="$colunasDetalhes"
    fetch-url="/items/{{ $item->id }}/detalhes"
/>
```


## 🛠️ Controller (Backend)

Exemplo de como estruturar o controller:

```php
<?php

class NotaFiscalController extends Controller
{
    public function getDados($id)
    {
        try {
            $itens = NotaFiscalItem::with(['produto', 'fornecedor'])
                ->where('id_nota_fiscal', $id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'codigo_produto' => $item->produto->codigo,
                        'nome_produto' => $item->produto->nome,
                        'quantidade' => $item->quantidade,
                        'valor_formatado' => 'R$ ' . number_format($item->valor, 2, ',', '.'),
                        'fornecedor' => [
                            'nome' => $item->fornecedor->nome,
                            'cnpj' => $item->fornecedor->cnpj
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'nfItens' => $itens
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados: ' . $e->getMessage()
            ], 500);
        }
    }
}
```


## 🎨 Customização de Estilos

O componente usa classes do Tailwind CSS. Para customizar:

### Cores do Modal

```blade
<!-- Altere as classes no componente -->
<div class="bg-white rounded-lg shadow-lg"> <!-- Fundo do modal -->
<tr class="bg-gray-100"> <!-- Header da tabela -->
<div class="bg-gray-50 border-t"> <!-- Footer da paginação -->
```


### Tamanhos Disponíveis (max-width)

- `sm` (24rem)
- `md` (28rem)
- `lg` (32rem)
- `xl` (36rem)
- `2xl` (42rem)
- `3xl` (48rem)
- `4xl` (56rem)
- `5xl` (64rem)
- `6xl` (72rem)
- `7xl` (80rem)


## 🚨 Troubleshooting

### Problema: Modal não abre

**Soluções:**

1. Verificar se a classe trigger está correta: `modal-trigger-{modalId}`
2. Verificar se o `fetch-url` está retornando dados corretos
3. Abrir o console (F12) e verificar erros JavaScript

### Problema: Dados não aparecem

**Soluções:**

1. Verificar formato do JSON retornado pela API
2. Verificar se os campos em `columns` existem nos dados
3. Verificar se está retornando `success: true`

### Problema: Paginação não funciona

**Soluções:**

1. Verificar se há mais itens que `items-per-page`
2. Verificar se não há erros JavaScript no console

### Problema: Modal não fecha com ESC

**Soluções:**

1. Verificar se não há outros event listeners interceptando o ESC
2. Verificar se o modal tem a estrutura correta

## 🔒 Segurança

- ✅ **XSS Protection**: Valores são tratados automaticamente
- ✅ **CSRF**: Use `@csrf` nos formulários se necessário
- ✅ **Validation**: Sempre valide dados no backend
- ✅ **Authorization**: Implemente verificações de permissão


## 📋 Checklist de Implementação

- [ ] Criar o arquivo do componente
- [ ] Definir array de colunas
- [ ] Criar endpoint da API
- [ ] Testar retorno JSON da API
- [ ] Incluir componente na view
- [ ] Adicionar classe trigger no elemento
- [ ] Testar abertura do modal
- [ ] Testar paginação
- [ ] Testar fechamento (ESC e clique fora)
- [ ] Validar responsividade


## 🤝 Suporte

Para dúvidas ou problemas:

1. Verificar esta documentação
2. Consultar o console do navegador (F12)
3. Verificar logs do Laravel
4. Contatar a equipe de desenvolvimento

**Criado para o projeto Laravel com Tailwind CSS e PostgreSQL** 🚀

