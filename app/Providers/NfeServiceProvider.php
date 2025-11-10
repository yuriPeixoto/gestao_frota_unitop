<?php

namespace App\Providers;

use App\Services\Nfe\Contracts\NfeImporterInterface;
use App\Services\Nfe\Contracts\NfePersistenceInterface;
use App\Services\Nfe\Contracts\NfeProcessorInterface;
use App\Services\Nfe\NfeImportService;
use App\Services\Nfe\NfePersistence;
use App\Services\Nfe\NfeProcessor;
use Illuminate\Support\ServiceProvider;

class NfeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrando o processador de NFe
        $this->app->bind(NfeProcessorInterface::class, NfeProcessor::class);

        // Registrando a persistência de NFe
        $this->app->bind(NfePersistenceInterface::class, NfePersistence::class);

        // Registrando o importador de NFe com as configurações FTP
        $this->app->bind(NfeImporterInterface::class, function ($app) {
            // Obter as configurações FTP do config
            $ftpConfig = config('nfe-import.ftp', [
                'host' => env('NFE_FTP_HOST', ''),
                'username' => env('NFE_FTP_USERNAME', ''),
                'password' => env('NFE_FTP_PASSWORD', ''),
                'port' => env('NFE_FTP_PORT', 60000),
                'directory' => env('NFE_FTP_DIRECTORY', '/'),
            ]);

            return new NfeImportService(
                $ftpConfig,
                $app->make(NfeProcessorInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar configurações
        $this->publishes([
            __DIR__ . '/../config/nfe-import.php' => config_path('nfe-import.php'),
        ], 'nfe-config');
    }
}
