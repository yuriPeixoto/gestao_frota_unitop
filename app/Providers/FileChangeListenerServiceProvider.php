<?php

namespace App\Providers;

use App\Observers\ModelCreationObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class FileChangeListenerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apenas registrar os eventos em ambiente de desenvolvimento
        if ($this->app->environment('local', 'development')) {
            // Registrar o observer para o evento de criação de arquivos
            Event::listen('Illuminate\Foundation\Events\LocaleUpdated', function () {
                $this->setupFileWatcher();
            });
        }
    }

    /**
     * Configura o watcher de arquivos para ambiente de desenvolvimento
     */
    private function setupFileWatcher()
    {
        // Implementação simplificada - Em produção é melhor usar a cron
        // Esta implementação é mais para desenvolvimento
        $modelsDir = app_path('Models');
        $observer = new ModelCreationObserver();

        // Verificar por novos modelos a cada requisição em ambiente de desenvolvimento
        if (is_dir($modelsDir)) {
            $currentModelFiles = glob($modelsDir . '/*.php');
            $cachedModelsList = cache()->get('models_list', []);

            // Comparar arquivos atuais com cache
            $newModels = array_diff($currentModelFiles, $cachedModelsList);

            foreach ($newModels as $newModelPath) {
                $event = new \stdClass();
                $event->path = $newModelPath;
                $observer->handle($event);
            }

            // Atualizar cache
            cache()->put('models_list', $currentModelFiles, now()->addDay());
        }
    }
}
