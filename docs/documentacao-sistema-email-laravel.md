# Documentação - Sistema de Email Laravel

## Visão Geral

Este documento descreve como os quatro componentes de email foram transformados em services Laravel organizados e estruturados, seguindo as melhores práticas do framework.

## Arquivos Criados/Modificados

### 1. Interface OAuthTokenProvider

**Localização:** `app/Contracts/OAuthTokenProvider.php`

Interface que define o contrato para provedores de tokens OAuth2 para autenticação SMTP.

```php
namespace App\Contracts;

interface OAuthTokenProvider
{
    public function getOauth64();
}
```

### 2. SmtpProviderService

**Localização:** `app/Services/SmtpProviderService.php`

Service responsável por gerenciar conexões SMTP, implementando funcionalidades do PHPMailer SMTP.

**Principais funcionalidades:**

-   Conexão com servidores SMTP
-   Debug de conexões
-   Configuração de timeouts
-   Patterns para extração de transaction IDs

### 3. PHPMailerService

**Localização:** `app/Services/PHPMailerService.php`

Service que estende o PHPMailer original, fornecendo uma interface Laravel-friendly.

**Principais métodos:**

-   `configureSMTP(array $config)`: Configura as configurações SMTP
-   `configureOAuth(?OAuthTokenProvider $oauth)`: Configura OAuth se disponível
-   `sendCustomEmail(array $emailData)`: Envia emails com configurações personalizadas

### 4. HTMLBodyService

**Localização:** `app/Services/HTMLBodyService.php`

Service responsável por gerar corpos HTML para emails de cotação.

**Principais métodos:**

-   `generateBody($empresa, $enderecoEmpresa, $numeroCotacao, $nomeFornecedor)`: Gera HTML completo
-   `generateBodyStatic()`: Método estático para compatibilidade

### 5. EmailSenderService

**Localização:** `app/Services/EmailSenderService.php`

Service principal que orquestra o envio de emails usando os outros services.

**Principais métodos:**

-   `sendEmail()`: Método principal para envio
-   `sendEmailStatic()`: Método estático para compatibilidade com código legado

## Como os Services se Relacionam

```
EmailSenderService
├── PHPMailerService (configuração e envio)
├── HTMLBodyService (geração de conteúdo)
└── SmtpProviderService (conexão SMTP)
```

O **EmailSenderService** é o orquestrador principal que:

1. Usa **PHPMailerService** para configurar SMTP e enviar emails
2. Usa **HTMLBodyService** para gerar o conteúdo HTML
3. Pode usar **SmtpProviderService** para conexões SMTP avançadas

## Exemplos de Uso

### 1. Usando Injeção de Dependência (Recomendado)

```php
class ExemploController extends Controller
{
    protected $emailSenderService;

    public function __construct(EmailSenderService $emailSenderService)
    {
        $this->emailSenderService = $emailSenderService;
    }

    public function enviarEmail()
    {
        $result = $this->emailSenderService->sendEmail(
            'smtp.gmail.com',  // host
            587,               // port
            'user@email.com',  // username
            'password',        // password
            'from@email.com',  // from
            'to@email.com',    // to
            'Assunto',         // subject
            'Empresa LTDA',    // empresa
            'Endereço',        // enderecoEmpresa
            'COT-001',         // numeroCotacao
            'Fornecedor'       // nomeFornecedor
        );
    }
}
```

### 2. Usando Services Diretamente

```php
public function enviarEmailCustom()
{
    $phpMailerService = new PHPMailerService();
    $htmlBodyService = new HTMLBodyService();

    // Configurar SMTP
    $phpMailerService->configureSMTP([
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'auth' => true,
        'username' => 'user@email.com',
        'password' => 'password',
        'encryption' => 'tls'
    ]);

    // Gerar HTML
    $html = $htmlBodyService->generateBody(
        'Empresa',
        'Endereço',
        'COT-001',
        'Fornecedor'
    );

    // Enviar
    $result = $phpMailerService->sendCustomEmail([
        'from' => 'from@email.com',
        'to' => 'to@email.com',
        'subject' => 'Assunto',
        'body' => $html,
        'is_html' => true
    ]);
}
```

### 3. Método Estático (Compatibilidade Legado)

```php
public function enviarEmailLegado()
{
    $result = EmailSenderService::sendEmailStatic(
        'smtp.gmail.com',
        587,
        'user@email.com',
        'password',
        'from@email.com',
        'to@email.com',
        'Assunto',
        'Empresa LTDA',
        'Endereço',
        'COT-001',
        'Fornecedor'
    );
}
```

## Service Provider

O **EmailServiceProvider** registra todos os services no container do Laravel:

```php
// Em app/Providers/EmailServiceProvider.php
public function register(): void
{
    $this->app->singleton(HTMLBodyService::class);
    $this->app->bind(PHPMailerService::class);
    $this->app->bind(SmtpProviderService::class);
    $this->app->bind(EmailSenderService::class);
}
```

Para usar, adicione ao array `providers` em `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\EmailServiceProvider::class,
],
```

## Rotas de Teste

As rotas foram criadas em `routes/email.php`:

-   `POST /email/send-cotacao` - Usando injeção de dependência
-   `POST /email/send-custom` - Usando instanciação direta
-   `POST /email/send-legacy` - Usando método estático

## Dependências

Para funcionar corretamente, é necessário instalar o PHPMailer:

```bash
composer require phpmailer/phpmailer
```

## Configuração

### Exemplo de configuração SMTP:

```php
$smtpConfig = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'auth' => true,
    'username' => 'seu@email.com',
    'password' => 'sua_senha_app',
    'encryption' => 'tls',
    'charset' => 'UTF-8',
    'debug' => 0,
    'auto_tls' => true
];
```

## Vantagens da Implementação Laravel

1. **Injeção de Dependência**: Services podem ser injetados automaticamente
2. **Namespace Organizado**: Todos os arquivos seguem PSR-4
3. **Compatibilidade**: Mantém métodos estáticos para código legado
4. **Testabilidade**: Services podem ser facilmente mockados para testes
5. **Reutilização**: Services podem ser usados em qualquer lugar da aplicação
6. **Configuração Centralizada**: Service Provider centraliza configurações

## Logs

O sistema utiliza o Log do Laravel para registrar sucessos e erros:

```php
Log::info("Email enviado com sucesso para: {$to}");
Log::error("Erro ao enviar email: " . $e->getMessage());
```

## Estrutura Final dos Arquivos

```
app/
├── Contracts/
│   └── OAuthTokenProvider.php
├── Http/Controllers/
│   └── EmailController.php
├── Providers/
│   └── EmailServiceProvider.php
└── Services/
    ├── EmailSenderService.php
    ├── HTMLBodyService.php
    ├── PHPMailerService.php
    └── SmtpProviderService.php

routes/
└── email.php
```

Esta implementação mantém a funcionalidade original dos quatro programas, mas agora estão organizados seguindo as convenções do Laravel, facilitando manutenção, testes e expansão futuras.
