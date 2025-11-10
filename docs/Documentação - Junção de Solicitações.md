# Funcionalidade de Junção de Solicitações de Compra

## Visão Geral

A funcionalidade de junção permite combinar múltiplas solicitações de compra em uma única solicitação, otimizando o processo de compras e facilitando a gestão.

## Como Funciona

### 1. Acesso à Funcionalidade

-   Na listagem de solicitações (`/admin/compras/solicitacoes`), clique no botão "Juntar Solicitações"
-   Ou acesse diretamente: `/admin/compras/solicitacoes/juntar`

### 2. Seleção de Solicitações

-   Selecione pelo menos 2 solicitações que deseja juntar
-   Apenas solicitações com status "AGUARDANDO APROVAÇÃO DO GESTOR" ou "AGUARDANDO INÍCIO DE COMPRAS" podem ser juntadas
-   Use os botões "Selecionar Todas" ou "Limpar Seleção" para facilitar a seleção

### 3. Configuração da Junção

Escolha uma das opções:

#### Apenas Produtos

-   Cria uma única solicitação contendo apenas os produtos de todas as solicitações selecionadas
-   Serviços são ignorados

#### Apenas Serviços

-   Cria uma única solicitação contendo apenas os serviços de todas as solicitações selecionadas
-   Produtos são ignorados

#### Produtos e Serviços (Ambos)

-   Cria duas solicitações separadas:
    -   Uma com todos os produtos
    -   Outra com todos os serviços
-   Esta é a opção recomendada para manter a separação entre produtos e serviços

### 4. Consolidação de Itens

-   Itens iguais (mesmo produto ou serviço) são automaticamente consolidados
-   As quantidades são somadas
-   As justificativas são combinadas

### 5. Resultado da Junção

-   Uma ou duas novas solicitações são criadas (dependendo da opção escolhida)
-   As solicitações originais são automaticamente canceladas
-   Uma observação é adicionada explicando a origem da junção

## Exemplo de Uso

### Cenário

Você tem 3 solicitações:

-   Solicitação #100: 5 Canetas BIC azuis + 1 Serviço de limpeza
-   Solicitação #101: 3 Canetas BIC azuis + 2 Blocos de notas
-   Solicitação #102: 1 Serviço de limpeza + 1 Serviço de manutenção

### Resultado com "Produtos e Serviços (Ambos)"

-   **Nova Solicitação #103 (Produtos):**

    -   8 Canetas BIC azuis (5+3)
    -   2 Blocos de notas

-   **Nova Solicitação #104 (Serviços):**
    -   2 Serviços de limpeza (1+1)
    -   1 Serviço de manutenção

### Solicitações Originais

-   Solicitações #100, #101 e #102 são canceladas automaticamente

## Benefícios

1. **Otimização de Compras**: Consolida itens iguais, facilitando negociações
2. **Redução de Processos**: Diminui o número de solicitações para processar
3. **Melhor Gestão**: Separa produtos de serviços quando necessário
4. **Histórico Mantido**: Mantém rastreabilidade das solicitações originais

## Permissões Necessárias

-   Usuário deve ter permissão para criar solicitações de compra
-   A mesma permissão que permite criar solicitações individuais

## Limitações

-   Apenas solicitações não canceladas podem ser juntadas
-   Apenas solicitações em status adequados podem ser selecionadas
-   Processo é irreversível (solicitações originais são canceladas)

## Observações Técnicas

-   A nova solicitação herda dados da primeira solicitação selecionada (departamento, filial, etc.)
-   O solicitante da nova solicitação é o usuário que está executando a junção
-   Status inicial da nova solicitação é "AGUARDANDO APROVAÇÃO DO GESTOR"
-   Anexos são preservados quando disponíveis
