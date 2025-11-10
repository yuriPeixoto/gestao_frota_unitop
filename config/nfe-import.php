<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações FTP
    |--------------------------------------------------------------------------
    |
    | Configurações de conexão com o servidor FTP que contém os arquivos XML de NFe
    |
    */
    'ftp' => [
        'host' => env('NFE_FTP_HOST', 'ftpnota.carvalima.com.br'),
        'username' => env('NFE_FTP_USERNAME', 'unitop'),
        'password' => env('NFE_FTP_PASSWORD', 'Piasolation@2024'),
        'port' => env('NFE_FTP_PORT', 60000),

        // Diretório padrão (mantendo compatibilidade)
        'directory' => env('NFE_FTP_DIRECTORY', 'XMLs'),

        // Novos diretórios com configurações específicas
        'directories' => [
            'historico' => [
                'path' => env('NFE_FTP_DIRECTORY_HISTORICO', 'XMLs-HISTORICO'),
                'frequency' => 'daily', // Processar uma vez por dia
                'schedule_time' => '02:00', // Horário para processar (2h da manhã)
            ],
            'hoje' => [
                'path' => env('NFE_FTP_DIRECTORY_HOJE', 'XMLs-HOJE'),
                'frequency' => 'hourly', // Processar de hora em hora
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Diretórios de trabalho
    |--------------------------------------------------------------------------
    |
    | Diretórios onde os arquivos serão armazenados durante o processamento
    |
    */
    'directories' => [
        'queue' => storage_path('app/nfe/' . env('NFE_QUEUE_DIR', 'queue')),
        'processing' => storage_path('app/nfe/' . env('NFE_PROCESSING_DIR', 'processing')),
        'failed' => storage_path('app/nfe/' . env('NFE_FAILED_DIR', 'failed')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de processamento
    |--------------------------------------------------------------------------
    |
    | Configurações adicionais para o processamento das NFes
    |
    */
    'batch_size' => env('NFE_BATCH_SIZE', 50),
    'max_attempts' => env('NFE_MAX_ATTEMPTS', 3),

    // NOVA CONFIGURAÇÃO: Limita o número de arquivos processados por execução
    // para evitar sobrecarga com diretórios muito grandes
    'max_files_per_run' => env('NFE_MAX_FILES_PER_RUN', 100),

    /*
    |--------------------------------------------------------------------------
    | Configurações do banco de dados
    |--------------------------------------------------------------------------
    |
    | Configuração da conexão com o banco de dados para salvar as NFes
    |
    */
    'database' => [
        'connection' => 'pgsql',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de log
    |--------------------------------------------------------------------------
    |
    | Configurações para o log do importador
    |
    */
    'log_file' => env('NFE_LOG_FILE', 'logs/nfe_import.log'),

    /*
    |--------------------------------------------------------------------------
    | Tratamento de campos vazios
    |--------------------------------------------------------------------------
    |
    | Configurações para tratamento automático de campos
    |
    */
    'field_defaults' => [
        // Quando dhsaient estiver vazio, copiar de dhemi
        'copy_dhemi_to_dhsaient' => env('NFE_COPY_DHEMI_TO_DHSAIENT', true),
    ],
];
