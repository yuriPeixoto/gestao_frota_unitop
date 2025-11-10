<?php

use App\Console\Commands\ImportUsersFromXlsx;
use App\Providers\NfeServiceProvider;
use App\Services\DescarteService;
use App\Services\SinistroDocumentService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Criar a aplicação
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            '2fa' => \App\Http\Middleware\RequireTwoFactorAuthentication::class,
            // 'session.timeout' => \App\Http\Middleware\SessionTimeoutMiddleware::class,
            'check.approval.level' => \App\Http\Middleware\CheckCompraApprovalLevel::class,
            'auto.permission' => \App\Http\Middleware\AutoPermissionMiddleware::class,
            'jwt.lumen' => \App\Http\Middleware\ValidateJwtFromLumen::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LogoutOtherDevices::class,
            \App\Http\Middleware\RedirectIfAuthenticated::class,
            // \App\Http\Middleware\SessionTimeoutMiddleware::class,
        ]);

        $middleware->api([
            Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Excluir rotas de API de notificações da verificação CSRF
        $middleware->validateCsrfTokens(except: [
            '/api/notifications/send',
            '/api/mobile/notifications',
            '/api/mobile/notifications/*',
        ]);
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\BladeServiceProvider::class,
        \App\Providers\MigrationBlockProvider::class,
        \App\Providers\AuthServiceProvider::class, // AuthServiceProvider no lugar do PermissionServiceProvider
        \Barryvdh\DomPDF\ServiceProvider::class,
        NfeServiceProvider::class,
        App\Providers\TooltipServiceProvider::class,
        // Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        /*
        |--------------------------------------------------------------------------
        | Importação de Notas Fiscais Eletrônicas (NFe)
        |--------------------------------------------------------------------------
        */

        // NFe HOJE - Horário comercial (hora em hora)
        $schedule->command('nfe:import-hoje --queue')
            ->hourly()
            ->between('6:00', '22:00')
            ->weekdays()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/nfe-hoje-scheduler.log'));

        // NFe HOJE - Fins de semana (a cada 6 horas)
        $schedule->command('nfe:import-hoje --queue')
            ->everySixHours()
            ->weekends()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/nfe-hoje-weekend.log'));

        // NFe HISTÓRICO - Processamento diário na madrugada
        $schedule->command('nfe:import-historico --queue')
            ->dailyAt('02:30')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/nfe-historico-scheduler.log'));

        // Processar filas de NFe com retry
        $schedule->command('queue:work --queue=nfe-hoje,nfe-historico,nfe-retry --tries=3 --max-time=600')
            ->hourly()
            ->withoutOverlapping(30)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/nfe-queue-worker.log'));

        // Verificar certificados vencidos de veículos
        $schedule->command('check:expired')
            ->daily()
            ->at('08:00')
            ->runInBackground();

        // Limpeza diária de sessões auto-save
        $schedule->command('autosave:limpar-sessoes')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/autosave-cleanup.log'));

        // Limpeza semanal mais agressiva (sessões > 12 horas)
        $schedule->command('autosave:limpar-sessoes --older-than=12')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/autosave-cleanup-weekly.log'));

        // Limpeza de arquivos temporários de sinistros
        $schedule->command('sinistros:cleanup-temp-files')
            ->daily()
            ->at('01:00')
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/sinistros-cleanup.log'))
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Limpeza mensal de logs
        $schedule->command('logs:cleanup --clean --days=30')
            ->monthly()
            ->at('00:00')
            ->description('Limpeza mensal de logs verbosos')
            ->runInBackground();

        // Lembrete de manutenção de logs
        $schedule->command('logs:send-maintenance-reminder')
            ->monthlyOn(1, '09:00')
            ->description('Lembrete mensal de manutenção de logs')
            ->runInBackground();

        // Sincronizar permissões básicas diariamente
        $schedule->command('permissions:sync-basic')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/permissions-sync.log'));
    })
    // 🚀 REGISTRAR COMMANDS CUSTOMIZADOS
    ->withCommands([
        ImportUsersFromXlsx::class,
        \App\Console\Commands\VerifyEmailDependencies::class,
        \App\Console\Commands\LogCleanupCommand::class,
        \App\Console\Commands\LogMaintenanceReminderCommand::class,
        \App\Console\Commands\AuditControllersPermissions::class,
        \App\Console\Commands\AuditViewPermissions::class,
    ])
    ->create();

// ============================================================================
// 🎯 REGISTRAR SERVIÇOS NO CONTAINER
// ============================================================================

// Serviço de Documentos de Sinistro (existente)
$app->singleton(SinistroDocumentService::class);

// ✅ NOVO: Serviço de Descarte/Baixa de Pneus
$app->singleton(DescarteService::class, function ($app) {
    return new DescarteService;
});

// Registrar o Kernel do Console, se necessário
if (! $app->bound(ConsoleKernelContract::class)) {
    $app->singleton(ConsoleKernelContract::class, \App\Console\Kernel::class);
}

return $app;
