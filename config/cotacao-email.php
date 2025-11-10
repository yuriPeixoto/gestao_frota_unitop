<?php

return [
   /*
    |--------------------------------------------------------------------------
    | Configurações de Email para Cotações
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para o envio de emails de cotações
    |
    */

   'smtp' => [
      'host' => env('COTACAO_MAIL_HOST', 'colaboracao.carvalima.com.br'),
      'port' => env('COTACAO_MAIL_PORT', 587),
      'username' => env('COTACAO_MAIL_USERNAME', 'orcamento@carvalima.com.br'),
      'password' => env('COTACAO_MAIL_PASSWORD', '3jYS%s74?yHtUL(Y'),
      'encryption' => env('COTACAO_MAIL_ENCRYPTION', 'tls'),
      'charset' => env('COTACAO_MAIL_CHARSET', 'UTF-8'),
      'debug' => env('COTACAO_MAIL_DEBUG', 0),
      'auto_tls' => env('COTACAO_MAIL_AUTO_TLS', true),
   ],

   // Configuração de fallback para teste
   'smtp_fallback' => [
      'enabled' => env('COTACAO_FALLBACK_ENABLED', false),
      'host' => env('COTACAO_FALLBACK_HOST', 'smtp.gmail.com'),
      'port' => env('COTACAO_FALLBACK_PORT', 587),
      'username' => env('COTACAO_FALLBACK_USERNAME', ''),
      'password' => env('COTACAO_FALLBACK_PASSWORD', ''),
      'encryption' => env('COTACAO_FALLBACK_ENCRYPTION', 'tls'),
   ],

   'from' => [
      'email' => env('COTACAO_FROM_EMAIL', 'orcamento@carvalima.com.br'),
      'name' => env('COTACAO_FROM_NAME', 'Carvalima Transportes'),
   ],

   'empresa' => [
      'nome' => env('COTACAO_EMPRESA_NOME', 'Carvalima Transportes LTDA'),
      'endereco' => env('COTACAO_EMPRESA_ENDERECO', 'Rod. Palmiro Paes de Barros, 2700, Cuiabá - MT 78090-702'),
   ],

   'subject' => env('COTACAO_EMAIL_SUBJECT', 'Cotação de preço'),

   'validation' => [
      'required_fields' => ['filial_entrega', 'filial_faturamento'],
      'validate_email_format' => true,
   ],

   'logging' => [
      'enabled' => env('COTACAO_LOGGING_ENABLED', true),
      'log_success' => env('COTACAO_LOG_SUCCESS', true),
      'log_errors' => env('COTACAO_LOG_ERRORS', true),
   ],

   'retry' => [
      'enabled' => env('COTACAO_RETRY_ENABLED', true),
      'max_attempts' => env('COTACAO_RETRY_ATTEMPTS', 2),
      'delay_seconds' => env('COTACAO_RETRY_DELAY', 60),
      'backoff_multiplier' => env('COTACAO_RETRY_BACKOFF', 2),
   ],

   'rate_limiting' => [
      'enabled' => env('COTACAO_RATE_LIMIT_ENABLED', true),
      'max_emails_per_minute' => env('COTACAO_RATE_LIMIT_PER_MINUTE', 5),
      'delay_between_emails' => env('COTACAO_EMAIL_DELAY_SECONDS', 3),
   ],
];
