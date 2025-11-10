<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendWelcomeNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:welcome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificação de boas-vindas para todos os usuários ativos';

    protected NotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Enviando notificação de boas-vindas...');

        try {
            $notification = $this->notificationService->sendToAll(
                type: 'sistema.boas_vindas',
                title: '🎉 Sistema de Notificações em Tempo Real Ativado!',
                message: 'A partir de agora você receberá notificações importantes em tempo real. Configure suas preferências de notificação no menu do seu perfil.',
                data: [
                    'url' => '/notifications/settings',
                    'versao' => '2.0.0',
                    'feature' => 'Sistema de Notificações',
                ],
                priority: 'normal',
                icon: 'rocket',
                color: 'blue'
            );

            $this->info("✅ Notificação de boas-vindas enviada com sucesso!");
            $this->info("   ID da notificação: {$notification->id}");
            $this->info("   Tipo: {$notification->notification_type}");
            $this->info("   Broadcast: " . ($notification->is_broadcasted ? 'Sim' : 'Não'));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar notificação: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
