# Resolução de Bloqueio SMTP - Sistema de Email Cotações

## Problema Identificado

**Erro:** `554 Bloqueado por excesso de tentativas de autenticacao. (Blocked by too many authentication attempts)`

**Causa:** O servidor SMTP `colaboracao.carvalima.com.br` está temporariamente bloqueando as credenciais devido ao excesso de tentativas de autenticação.

## Soluções Implementadas

### 1. **Detecção Melhorada de Bloqueio**

-   `PHPMailerService.php` agora detecta múltiplos padrões de bloqueio
-   Controller diferencia bloqueios SMTP de outros tipos de erro
-   Mensagens mais claras para o usuário final

### 2. **Rate Limiting Ativado**

```php
'rate_limiting' => [
   'enabled' => true,
   'max_emails_per_minute' => 5,
   'delay_between_emails' => 3, // 3 segundos entre emails
]
```

### 3. **Retry Logic Melhorado**

-   Sistema tenta reenviar emails falhos
-   Detecta automaticamente se é bloqueio temporário
-   Aguarda tempo configurável antes de tentar novamente

## Como Resolver o Bloqueio Atual

### Opção 1: Aguardar Desbloqueio Automático

-   **Tempo:** 30 minutos a 2 horas
-   **Ação:** Aguardar e tentar novamente depois

### Opção 2: Contatar Administrador SMTP

-   **Contato:** Administrador do servidor `carvalima.com.br`
-   **Solicitar:** Desbloqueio manual das credenciais
-   **Informar:** IP do servidor e horário do bloqueio

### Opção 3: Configurar Servidor Alternativo (Temporário)

```php
// No arquivo .env, alterar temporariamente:
COTACAO_SMTP_HOST=smtp.gmail.com
COTACAO_SMTP_PORT=587
COTACAO_SMTP_USERNAME=seu-email@gmail.com
COTACAO_SMTP_PASSWORD=sua-senha-app
```

## Prevenção de Futuros Bloqueios

### 1. **Rate Limiting Sempre Ativo**

```bash
# No .env:
COTACAO_RATE_LIMIT_ENABLED=true
COTACAO_RATE_LIMIT_PER_MINUTE=5
COTACAO_EMAIL_DELAY_SECONDS=3
```

### 2. **Monitoramento de Logs**

```bash
# Verificar logs em tempo real:
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i "email\|smtp"
```

### 3. **Teste de Conectividade Regular**

```bash
php artisan email:test-system
```

## Comandos Úteis

### Teste do Sistema

```bash
php artisan email:test-system
```

### Limpar Cache

```bash
php artisan cache:clear
php artisan config:clear
```

### Verificar Configuração

```bash
php artisan tinker --execute="dd(config('cotacao-email'));"
```

## Status do Sistema

✅ **Conectividade de Rede:** OK
✅ **Handshake SMTP:** OK
✅ **Rate Limiting:** ATIVO
✅ **Detecção de Bloqueio:** ATIVO
❌ **Autenticação SMTP:** BLOQUEADA (temporário)

## Próximos Passos

1. **Aguardar** desbloqueio automático (30min-2h)
2. **Testar** novamente com `php artisan email:test-system`
3. **Executar** envio de cotações quando desbloqueado
4. **Monitorar** logs para prevenir novos bloqueios

## Data da Última Atualização

04/09/2025 - 15:30
