# Documentação - Sistema de Envio de Email para Cotações

## Resumo das Implementações

Este documento descreve as implementações realizadas para integrar o sistema de envio de emails ao `CotacoesController`.

## Arquivos Criados/Modificados

### 1. PHPMailerService (`app/Services/PHPMailerService.php`)

Service customizado que estende a classe PHPMailer com configurações específicas para o projeto.

**Principais métodos:**

-   `configureSMTP()`: Configura as opções SMTP
-   `sendCustomEmail()`: Envia email com dados estruturados
-   `testConnection()`: Testa conectividade SMTP

**Dependências:**

-   `phpmailer/phpmailer` (instalado via Composer)

### 2. Configuração Centralizada (`config/cotacao-email.php`)

Arquivo de configuração específico para cotações que centraliza todas as configurações de email.

**Principais seções:**

-   `smtp`: Configurações do servidor SMTP
-   `from`: Remetente padrão
-   `empresa`: Dados da empresa
-   `validation`: Regras de validação
-   `logging`: Configurações de log

**Variáveis de ambiente suportadas:**

```env
COTACAO_MAIL_HOST=colaboracao.carvalima.com.br
COTACAO_MAIL_PORT=587
COTACAO_MAIL_USERNAME=orcamento@carvalima.com.br
COTACAO_MAIL_PASSWORD=senha_aqui
COTACAO_MAIL_ENCRYPTION=tls
```

### 3. Controller Melhorado (`app/Http/Controllers/Admin/CotacoesController.php`)

O método `onEnviarCotacoes` foi totalmente refatorado com as seguintes melhorias:

**Melhorias implementadas:**

-   ✅ Uso de configuração centralizada
-   ✅ Melhor tratamento de erros com detalhes específicos
-   ✅ Validação de email do fornecedor (tanto na cotação quanto no fornecedor relacionado)
-   ✅ Validação de formato de email
-   ✅ Logs detalhados configuráveis
-   ✅ Resposta JSON com detalhes dos erros
-   ✅ Eager loading dos fornecedores para otimização

### 4. Comando de Teste (`app/Console/Commands/TestEmailSystem.php`)

Comando Artisan para testar o sistema de email:

```bash
php artisan email:test-system
```

**Funcionalidades:**

-   Verifica se todas as dependências estão carregadas
-   Testa conectividade SMTP (opcional)
-   Exibe configurações atuais
-   Valida integridade do sistema

### 5. Service Provider Existente (`app/Providers/EmailServiceProvider.php`)

O EmailServiceProvider já estava configurado corretamente e registrado no sistema.

## Como Usar

### 1. Configuração Básica

As configurações padrão já estão funcionais. Para personalizar, edite o arquivo `config/cotacao-email.php` ou use variáveis de ambiente.

### 2. Envio de Cotações

O método `onEnviarCotacoes` no controller já está implementado e funcionando. Ele:

1. Busca cotações da solicitação
2. Valida emails dos fornecedores
3. Envia emails usando o `EmailSenderService`
4. Retorna resposta JSON com estatísticas

### 3. Teste do Sistema

Execute o comando de teste para verificar se tudo está funcionando:

```bash
php artisan email:test-system
```

## Estrutura de Resposta da API

```json
{
    "success": true,
    "title": "Sucesso",
    "message": "Processo concluído: 5 email(s) enviado(s) e 1 erro(s)",
    "data": {
        "emails_enviados": 5,
        "emails_com_erro": 1,
        "total_cotacoes": 6,
        "detalhes_erros": [
            "Fornecedor 'Empresa XYZ' não possui email cadastrado"
        ]
    }
}
```

## Logs

O sistema gera logs detalhados em `storage/logs/laravel.log`:

-   **INFO**: Tentativas de envio e sucessos
-   **WARNING**: Emails inválidos ou não cadastrados
-   **ERROR**: Falhas no envio e exceções

## Dependências Instaladas

-   `phpmailer/phpmailer ^6.10`: Biblioteca para envio de emails

## Próximos Passos Sugeridos

1. **Configurar SMTP**: Verificar configurações de rede e firewall para conectividade SMTP
2. **Monitoramento**: Implementar dashboard para acompanhar estatísticas de envio
3. **Templates**: Criar templates dinâmicos para diferentes tipos de cotação
4. **Filas**: Implementar sistema de filas para envios em lote
5. **Retry**: Sistema de retry automático para falhas temporárias

## Troubleshooting

### Erro: "Class PHPMailer not found"

Execute: `composer require phpmailer/phpmailer`

### Erro: "EmailSenderService not found"

Execute:

```bash
php artisan config:clear
php artisan clear-compiled
```

### Erro de conectividade SMTP

1. Verifique configurações de firewall
2. Teste conectividade com telnet: `telnet colaboracao.carvalima.com.br 587`
3. Verifique credenciais SMTP

### Debug de envios

Ative o debug SMTP editando `config/cotacao-email.php`:

```php
'debug' => 2, // Ativar debug detalhado
```

## Segurança

-   ⚠️ As credenciais SMTP estão hardcoded no arquivo de configuração
-   📋 **Recomendação**: Mover para variáveis de ambiente (.env)
-   🔒 Implementar criptografia de credenciais sensíveis
