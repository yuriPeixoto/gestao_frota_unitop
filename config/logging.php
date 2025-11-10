<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // 🧹 CANAIS CUSTOMIZADOS PARA CONTROLE E LIMPEZA

        // Seu canal NFE existente (mantido)
        'nfe' => [
            'driver' => 'daily',
            'path' => storage_path('logs/nfe-import.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        // Canal específico para SQL queries (controlado por env)
        'sql' => [
            'driver' => env('LOG_SQL_ENABLED', false) ? 'daily' : 'null',
            'path' => storage_path('logs/sql.log'),
            'level' => 'debug',
            'days' => env('LOG_SQL_DAYS', 3), // SQL logs ficam apenas 3 dias
        ],

        // Canal para activity logs (separado para limpeza fácil)
        'activity' => [
            'driver' => 'daily',
            'path' => storage_path('logs/activity.log'),
            'level' => 'info',
            'days' => env('LOG_ACTIVITY_DAYS', 30),
        ],

        // Canal para debug temporário (limpeza mais agressiva)
        'temp_debug' => [
            'driver' => 'daily',
            'path' => storage_path('logs/temp-debug.log'),
            'level' => 'debug',
            'days' => env('LOG_TEMP_DEBUG_DAYS', 2), // Apenas 2 dias
        ],

        // Canal para logs de sistema/cache (limpeza mensal)
        'system' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system.log'),
            'level' => 'info',
            'days' => env('LOG_SYSTEM_DAYS', 7),
        ],

        // Canal para erros importantes (mantém mais tempo)
        'errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => 'error',
            'days' => env('LOG_ERRORS_DAYS', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 🎯 CONFIGURAÇÕES DE LIMPEZA AUTOMÁTICA
    |--------------------------------------------------------------------------
    */

    'cleanup_schedule' => [
        'enabled' => env('LOG_AUTO_CLEANUP_ENABLED', true),

        // Configuração por tipo de log
        'rules' => [
            'sql_logs' => [
                'pattern' => 'local.INFO: SQL:',
                'keep_days' => 1,
                'cleanup_frequency' => 'daily',
            ],
            'debug_logs' => [
                'pattern' => 'local.DEBUG:',
                'keep_days' => 3,
                'cleanup_frequency' => 'daily',
            ],
            'activity_logs_verbose' => [
                'pattern' => 'insert into "activity_logs"',
                'keep_days' => 7,
                'cleanup_frequency' => 'weekly',
            ],
            'session_logs' => [
                'pattern' => 'sessions" where "id"',
                'keep_days' => 1,
                'cleanup_frequency' => 'daily',
            ],
            'cache_info' => [
                'pattern' => 'Cache de permissões',
                'keep_days' => 1,
                'cleanup_frequency' => 'daily',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 📋 CONFIGURAÇÕES DE RATE LIMITING
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'enabled' => env('LOG_RATE_LIMITING', false), // Desabilitado por padrão para debug
        'max_same_messages' => env('LOG_MAX_SAME_MESSAGES', 10),
        'time_window' => env('LOG_TIME_WINDOW', 300), // 5 minutos
    ],

];

/*
|--------------------------------------------------------------------------
| 📝 CONFIGURAÇÕES RECOMENDADAS PARA .ENV
|--------------------------------------------------------------------------
|
| # Configuração básica (mantém debug habilitado)
| LOG_CHANNEL=daily
| LOG_LEVEL=debug
| LOG_DAILY_DAYS=14
|
| # Controle de SQL logging (desabilitado por padrão)
| LOG_SQL_ENABLED=false
| LOG_SQL_DAYS=3
|
| # Controle de limpeza por tipo de log
| LOG_AUTO_CLEANUP_ENABLED=true
| LOG_ACTIVITY_DAYS=30
| LOG_TEMP_DEBUG_DAYS=2
| LOG_SYSTEM_DAYS=7
| LOG_ERRORS_DAYS=60
|
| # Rate limiting (opcional para desenvolvimento)
| LOG_RATE_LIMITING=false
|
|--------------------------------------------------------------------------
| 🚀 COMO USAR OS NOVOS CANAIS
|--------------------------------------------------------------------------
|
| // Para logs SQL (quando necessário)
| Log::channel('sql')->debug('Query executada: ' . $sql);
|
| // Para activity logs otimizados
| Log::channel('activity')->info('User login', ['user_id' => $user->id]);
|
| // Para debug temporário (limpa em 2 dias)
| Log::channel('temp_debug')->debug('Debug temporário: ' . $data);
|
| // Para logs de sistema/cache
| Log::channel('system')->info('Cache recarregado');
|
| // Para erros importantes (mantém 60 dias)
| Log::channel('errors')->error('Erro crítico: ' . $exception);
|
|--------------------------------------------------------------------------
| ⏰ CRON PARA LIMPEZA AUTOMÁTICA (SUGESTÃO)
|--------------------------------------------------------------------------
|
| Adicione no bootstrap/app.php na seção withSchedule():
|
| $schedule->command('logs:cleanup --clean --days=1')
|     ->daily()
|     ->description('Limpeza diária de logs verbosos');
|
| $schedule->command('logs:cleanup --clean --days=30')
|     ->monthly()
|     ->description('Limpeza mensal completa de logs');
|
|--------------------------------------------------------------------------
*/
