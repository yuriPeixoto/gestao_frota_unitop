<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Habilitar o Telescope somente quando a rota do Telescope estiver sendo acessada
        if (! $this->shouldEnableTelescope()) {
            config(['telescope.enabled' => false]);

            return;
        }

        // Verificar se as tabelas do Telescope existem
        if (! $this->telescopeTablesExist()) {
            config(['telescope.enabled' => false]);

            return;
        }

        // Telescope::night();
        $this->hideSensitiveRequestDetails();
        $this->configureFiltering();
        $this->configurePostgreSQLCompatibility(); // Nova função para PostgreSQL
    }

    /**
     * Determina se o Telescope deve ser habilitado para esta requisição.
     */
    protected function shouldEnableTelescope(): bool
    {
        $enabled = (bool) config('telescope.enabled');

        // Habilitar somente quando a própria rota do Telescope estiver sendo acessada
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = trim(parse_url($uri, PHP_URL_PATH) ?? '', '/');

        // Respeita o path configurado (padrão: 'telescope')
        $telescopePath = trim(config('telescope.path', 'telescope'), '/');

        return $enabled && str_starts_with($path, $telescopePath);
    }

    /**
     * Verificar se as tabelas do Telescope existem
     */
    protected function telescopeTablesExist(): bool
    {
        try {
            DB::table('telescope_entries')->limit(1)->get();

            return true;
        } catch (\Exception $e) {
            Log::info('Telescope desabilitado: tabelas não encontradas. Execute as migrations.');

            return false;
        }
    }

    /**
     * Configurar filtragem do Telescope para performance
     */
    protected function configureFiltering(): void
    {
        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            // Em ambiente local, capturar tudo
            if ($isLocal) {
                return true;
            }

            // Limpar dados problemáticos de request/session
            if ($entry->type === 'request') {
                // Remove ViewErrorBag / MessageBag da sessão
                if (isset($entry->content['session']['_errors'])) {
                    unset($entry->content['session']['_errors']);
                }

                // Converte objetos complexos em strings JSON
                if (isset($entry->content['request'])) {
                    array_walk_recursive($entry->content['request'], function (&$value) {
                        if (is_object($value)) {
                            if (method_exists($value, '__toString')) {
                                $value = (string) $value;
                            } else {
                                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                            }
                        }
                    });
                }
            }

            // Capturar apenas itens importantes em ambientes não-local
            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag() ||
                // Queries lentas (> 100ms)
                ($entry->type === 'query' && isset($entry->content['time']) && $entry->content['time'] > 100);
        });
    }

    /**
     * Configurar compatibilidade com PostgreSQL
     * Remove caracteres que causam erro SQLSTATE[22P05]
     */
    protected function configurePostgreSQLCompatibility(): void
    {
        Telescope::filter(function (IncomingEntry $entry) {
            // Sanitiza o conteúdo para remover null bytes e caracteres problemáticos
            $entry->content = $this->sanitizeContent($entry->content);

            // Para entradas de cache/redis/session, limita o tamanho para evitar dados muito grandes
            if (in_array($entry->type, ['cache', 'redis', 'session'])) {
                if (isset($entry->content['value']) && is_string($entry->content['value'])) {
                    $valueLength = strlen($entry->content['value']);
                    if ($valueLength > 10000) {
                        $entry->content['value'] = substr($entry->content['value'], 0, 1000)
                            ."\n... [truncated {$valueLength} chars for PostgreSQL compatibility]";
                    }
                }
            }

            // Para queries com bindings muito longos
            if ($entry->type === 'query' && isset($entry->content['bindings'])) {
                foreach ($entry->content['bindings'] as $key => $binding) {
                    if (is_string($binding) && strlen($binding) > 1000) {
                        $entry->content['bindings'][$key] = substr($binding, 0, 100).'... [truncated]';
                    }
                }
            }

            return $entry;
        });

        // Tags para melhor organização (mantendo as existentes)
        Telescope::tag(function (IncomingEntry $entry) {
            switch ($entry->type) {
                case 'request':
                    return [
                        'controller:'.($entry->content['controller_action'] ?? 'unknown'),
                        'status:'.($entry->content['response_status'] ?? 'unknown'),
                    ];

                case 'query':
                    $tags = ['connection:'.($entry->content['connection'] ?? 'unknown')];
                    if (isset($entry->content['slow']) && $entry->content['slow']) {
                        $tags[] = 'slow:true';
                    }

                    return $tags;

                default:
                    return [];
            }
        });
    }


    /**
     * Sanitiza conteúdo recursivamente removendo caracteres problemáticos para PostgreSQL
     */
    protected function sanitizeContent($content)
    {
        if (is_array($content)) {
            return array_map([$this, 'sanitizeContent'], $content);
        }

        if (is_string($content)) {
            // Remove null bytes (\u0000, \x00)
            $content = str_replace(["\x00", "\u0000"], '', $content);

            // Remove outros caracteres de controle problemáticos
            $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

            // Garante que seja UTF-8 válido
            if (! mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8//IGNORE');
            }

            return $content;
        }

        if (is_object($content)) {
            // Para objetos, converte para array e sanitiza
            return $this->sanitizeContent((array) $content);
        }

        return $content;
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'yuripeixoto@gmail.com',
                'geo.xodrom@gmail.com', // Adicionei este baseado no log
                // Adicione outros emails autorizados aqui
            ]) || $user->is_superuser ?? false;
        });
    }
}
