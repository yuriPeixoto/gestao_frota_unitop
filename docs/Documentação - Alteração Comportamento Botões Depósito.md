# Documentação - Alteração Comportamento Botões Manutenção, Estoque e Descarte

## Resumo das Alterações
Esta documentação descreve as alterações implementadas no sistema de movimentação de pneus para modificar o comportamento dos botões **Manutenção**, **Estoque** e **Descarte** quando um pneu é desaplicado.

## Comportamento Anterior
- Quando um pneu era desaplicado e movido para uma das áreas (Manutenção/Estoque/Descarte), o status do pneu era alterado diretamente para o destino escolhido
- Não havia registro específico na tabela `pneudeposito`

## Novo Comportamento
Quando um pneu for desaplicado e o usuário escolher um dos 3 botões (Manutenção, Estoque, Descarte), o sistema:

### 1. Cria registro na tabela `pneudeposito`
- **data_inclusao**: `now()` (data e hora atual)
- **id_pneu**: ID do pneu desaplicado
- **descricao_destino**: `'DEPOSITO'` (sempre)
- **destinacao_solicitada**: Varia conforme o botão escolhido:
  - `'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO'` para Manutenção
  - `'ENVIAR PARA O ESTOQUE'` para Estoque
  - `'DESCARTE'` para Descarte

### 2. Atualiza status do pneu
- **status_pneu**: `'DEPOSITO'` (sempre, independente do botão escolhido)

### 3. Desaplica o pneu
- Remove o pneu da tabela `PneusAplicados` seguindo o processo existente (soft delete)

### 4. Registra no histórico
- Grava no `historicopneu` conforme o desenvolvimento já existente

## Arquivos Modificados

### 1. `app/Services/PneuAplicadoService.php`
```php
// Adicionado import
use App\Models\PneuDeposito;

// Método desativarPneuAplicado modificado
// - Criação do registro PneuDeposito
// - Status sempre para 'DEPOSITO'

// Novos métodos adicionados:
private function criarRegistroPneuDeposito($pneuId, $destino)
private function mapearDestinoParaDestinacao($destino)
```

### 2. Mapeamento de Destinos
```php
private function mapearDestinoParaDestinacao($destino)
{
    $mapeamento = [
        'MANUTENCAO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
        'MANUTENÇÃO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
        'EM MANUTENÇÃO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
        'ESTOQUE' => 'ENVIAR PARA O ESTOQUE',
        'DESCARTE' => 'DESCARTE'
    ];

    return $mapeamento[$destino] ?? 'ENVIAR PARA O ESTOQUE';
}
```

## Fluxo do Sistema

### Interface (Frontend)
1. Usuário seleciona um pneu aplicado
2. Arrasta para uma das áreas: Manutenção (`data-tipo="MANUTENCAO"`), Estoque (`data-tipo="ESTOQUE"`) ou Descarte (`data-tipo="DESCARTE"`)
3. Preenche informações nos modais (KM removido, sulco)
4. Sistema dispara auto-save com os dados

### Backend (Controller)
1. `MovimentacaoPneusController::handleAutoSave()` recebe os dados
2. Extrai o destino do array `$pneu['status']`
3. Chama `PneuAplicadoService::processarOperacoesMultiplas()`

### Processamento (Service)
1. `PneuAplicadoService::desativarPneuAplicado()` é executado
2. Atualiza histórico do pneu
3. **NOVO**: Chama `criarRegistroPneuDeposito()`
4. **ALTERADO**: Sempre define status como 'DEPOSITO'
5. Faz soft delete do registro em `PneusAplicados`

## Validações e Logs
- Log de sucesso ao criar registro em `PneuDeposito`
- Log de erro em caso de falha na criação
- Validações de dados antes da criação

## Compatibilidade
- As alterações são compatíveis com o sistema existente
- Não afetam outras funcionalidades
- Mantêm a funcionalidade de auto-save

## Testes Recomendados
1. Testar remoção de pneu para Manutenção
2. Testar remoção de pneu para Estoque  
3. Testar remoção de pneu para Descarte
4. Verificar criação correta dos registros em `pneudeposito`
5. Verificar status 'DEPOSITO' na tabela `pneu`
6. Verificar logs de sucesso/erro

## Data da Implementação
26 de agosto de 2025
