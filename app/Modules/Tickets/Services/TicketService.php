<?php

namespace App\Modules\Tickets\Services;

use App\Modules\Tickets\Enums\TicketPriority;
use App\Modules\Tickets\Enums\TicketStatus;
use App\Modules\Tickets\Enums\TicketType;
use App\Modules\Tickets\Models\SupportTicket;
use App\Modules\Tickets\Models\TicketAssignment;
use App\Modules\Tickets\Models\TicketResponse;
use App\Modules\Tickets\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Cria um novo chamado
     */
    public function createTicket(array $data, User $user): SupportTicket
    {
        return DB::transaction(function () use ($data, $user) {
            // Criar ticket
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'filial_id' => $user->filial_id,
                'category_id' => $data['category_id'],
                'type' => $data['type'],
                'priority' => $data['priority'] ?? TicketPriority::MEDIA->value,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'browser' => $data['browser'] ?? null,
                'device' => $data['device'] ?? null,
                'ip_address' => request()->ip(),
                'url' => $data['url'] ?? null,
                'is_internal' => $data['is_internal'] ?? false,
            ]);

            // Refresh para garantir que o status foi definido pelo hook
            $ticket->refresh();

            // Adicionar tags se houver
            if (! empty($data['tags'])) {
                $ticket->tags()->attach($data['tags']);
            }

            // Registrar histórico de status
            $this->addStatusHistory($ticket, null, $ticket->status, $user);

            // Enviar notificações
            $this->notifyTicketCreated($ticket);

            Log::info("Ticket #{$ticket->ticket_number} criado por {$user->name}");

            return $ticket->load(['category', 'user', 'tags']);
        });
    }

    /**
     * Adiciona resposta ao ticket
     */
    public function addResponse(SupportTicket $ticket, array $data, User $user): TicketResponse
    {
        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_internal' => $data['is_internal'] ?? false,
            'is_solution' => $data['is_solution'] ?? false,
            'time_spent_minutes' => $data['time_spent_minutes'] ?? null,
        ]);

        // Se for marcado como solução, atualizar ticket
        if ($response->is_solution && $ticket->status === TicketStatus::EM_ATENDIMENTO) {
            $this->updateStatus($ticket, TicketStatus::RESOLVIDO, $user, 'Solução fornecida');
        }

        // Enviar notificações
        $this->notifyNewResponse($ticket, $response, $user);

        return $response;
    }

    /**
     * Atualiza status do ticket
     */
    public function updateStatus(
        SupportTicket $ticket,
        TicketStatus $newStatus,
        User $user,
        ?string $comment = null
    ): void {
        $oldStatus = $ticket->status;

        // Validar transição
        if (! $oldStatus->canTransitionTo($newStatus)) {
            throw new \Exception("Transição de '{$oldStatus->label()}' para '{$newStatus->label()}' não permitida");
        }

        $ticket->update(['status' => $newStatus]);

        // Atualizar timestamps específicos
        match ($newStatus) {
            TicketStatus::EM_ATENDIMENTO => $ticket->started_at ??= now(),
            TicketStatus::RESOLVIDO => $ticket->resolved_at ??= now(),
            TicketStatus::FECHADO => $ticket->closed_at ??= now(),
            default => null,
        };

        $ticket->save();

        // Registrar histórico
        $this->addStatusHistory($ticket, $oldStatus, $newStatus, $user, $comment);

        // Enviar notificações
        $this->notifyStatusChange($ticket, $oldStatus, $newStatus, $user);

        Log::info("Ticket #{$ticket->ticket_number} mudou de {$oldStatus->label()} para {$newStatus->label()}");
    }

    /**
     * Atribui ticket a um atendente
     */
    public function assignTicket(SupportTicket $ticket, User $assignee, User $assignedBy, ?string $comment = null): void
    {
        $oldAssignee = $ticket->assigned_to;

        $ticket->update(['assigned_to' => $assignee->id]);

        // Registrar no histórico
        TicketAssignment::create([
            'ticket_id' => $ticket->id,
            'assigned_by' => $assignedBy->id,
            'assigned_to' => $assignee->id,
            'comment' => $comment,
        ]);

        // Se estava novo, mudar para em atendimento
        if ($ticket->status === TicketStatus::NOVO || $ticket->status === TicketStatus::APROVADO_QUALIDADE) {
            $this->updateStatus($ticket, TicketStatus::EM_ATENDIMENTO, $assignedBy, 'Ticket atribuído');
        }

        // Notificar atendente
        $this->notifyTicketAssigned($ticket, $assignee, $assignedBy);

        Log::info("Ticket #{$ticket->ticket_number} atribuído para {$assignee->name} por {$assignedBy->name}");
    }

    /**
     * Revisão da Qualidade (aprovar/rejeitar melhoria)
     */
    public function qualityReview(
        SupportTicket $ticket,
        User $reviewer,
        bool $approved,
        ?string $comments = null
    ): void {
        if ($ticket->type !== TicketType::MELHORIA) {
            throw new \Exception('Apenas melhorias passam pela revisão de qualidade');
        }

        if ($ticket->status !== TicketStatus::AGUARDANDO_QUALIDADE) {
            throw new \Exception('Ticket não está aguardando revisão da qualidade');
        }

        $newStatus = $approved ? TicketStatus::APROVADO_QUALIDADE : TicketStatus::REJEITADO_QUALIDADE;

        $ticket->update([
            'quality_reviewed_by' => $reviewer->id,
            'quality_reviewed_at' => now(),
            'quality_comments' => $comments,
        ]);

        $this->updateStatus($ticket, $newStatus, $reviewer, $comments);

        // Se aprovado, notificar equipe Unitop
        if ($approved) {
            $this->notifyQualityApproved($ticket);
        } else {
            $this->notifyQualityRejected($ticket);
        }

        Log::info("Melhoria #{$ticket->ticket_number} ".($approved ? 'aprovada' : 'rejeitada').' pela qualidade');
    }

    /**
     * Define estimativa de prazo
     */
    public function setEstimate(SupportTicket $ticket, float $hours, User $user): void
    {
        $completionDate = now()->addHours($hours);

        $ticket->update([
            'estimated_hours' => $hours,
            'estimated_completion_date' => $completionDate,
        ]);

        // Notificar solicitante
        $this->notificationService->sendToUsers(
            userIds: [$ticket->user_id],
            type: 'tickets.estimativa',
            title: 'Estimativa de Conclusão Definida',
            message: "O ticket #{$ticket->ticket_number} tem previsão de {$hours}h para conclusão.",
            data: [
                'url' => route('tickets.show', $ticket->id),
                'ticket_id' => $ticket->id,
                'hours' => $hours,
                'completion_date' => $completionDate->format('d/m/Y H:i'),
            ],
            priority: 'normal',
            icon: 'clock',
            color: 'blue'
        );
    }

    /**
     * Adiciona observador ao ticket
     */
    public function addWatcher(SupportTicket $ticket, User $watcher): void
    {
        if (! $ticket->watchers()->where('user_id', $watcher->id)->exists()) {
            $ticket->watchers()->attach($watcher->id);
        }
    }

    /**
     * Remove observador
     */
    public function removeWatcher(SupportTicket $ticket, User $watcher): void
    {
        $ticket->watchers()->detach($watcher->id);
    }

    /**
     * Adiciona avaliação de satisfação
     */
    public function addSatisfactionRating(
        SupportTicket $ticket,
        int $rating,
        ?string $comment = null
    ): void {
        if ($ticket->status !== TicketStatus::RESOLVIDO && $ticket->status !== TicketStatus::FECHADO) {
            throw new \Exception('Apenas tickets resolvidos/fechados podem ser avaliados');
        }

        $ticket->update([
            'satisfaction_rating' => $rating,
            'satisfaction_comment' => $comment,
        ]);

        // Fechar automaticamente se estiver resolvido
        if ($ticket->status === TicketStatus::RESOLVIDO) {
            $this->updateStatus($ticket, TicketStatus::FECHADO, $ticket->user, 'Avaliado pelo cliente');
        }
    }

    /**
     * Upload de anexo
     */
    public function uploadAttachment(
        SupportTicket $ticket,
        $file,
        User $user,
        ?int $responseId = null
    ): \App\Models\TicketAttachment {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedName = uniqid().'_'.time().'.'.$extension;
        $path = $file->storeAs("tickets/{$ticket->id}", $storedName, 'public');

        return \App\Models\TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'response_id' => $responseId,
            'user_id' => $user->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Adiciona histórico de status
     */
    protected function addStatusHistory(
        SupportTicket $ticket,
        ?TicketStatus $fromStatus,
        TicketStatus $toStatus,
        User $user,
        ?string $comment = null
    ): void {
        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'comment' => $comment,
        ]);
    }

    /**
     * NOTIFICAÇÕES
     */
    protected function notifyTicketCreated(SupportTicket $ticket): void
    {
        // Se for melhoria, notificar equipe de qualidade
        if ($ticket->type === TicketType::MELHORIA) {
            $qualityUsers = User::role('Equipe Qualidade')->pluck('id')->toArray();

            if (! empty($qualityUsers)) {
                $this->notificationService->sendToUsers(
                    userIds: $qualityUsers,
                    type: 'tickets.nova_melhoria',
                    title: 'Nova Melhoria para Revisar',
                    message: "#{$ticket->ticket_number}: {$ticket->subject}",
                    data: [
                        'url' => route('tickets.show', $ticket->id),
                        'ticket_id' => $ticket->id,
                    ],
                    priority: 'high',
                    icon: 'lightbulb',
                    color: 'yellow'
                );
            }
        } else {
            // Outros tipos: notificar equipe Unitop diretamente
            $unitopUsers = User::role('Equipe Unitop')->pluck('id')->toArray();

            if (! empty($unitopUsers)) {
                $this->notificationService->sendToUsers(
                    userIds: $unitopUsers,
                    type: 'tickets.novo_chamado',
                    title: "Novo Chamado: {$ticket->type->label()}",
                    message: "#{$ticket->ticket_number}: {$ticket->subject}",
                    data: [
                        'url' => route('tickets.show', $ticket->id),
                        'ticket_id' => $ticket->id,
                    ],
                    priority: $ticket->priority === TicketPriority::URGENTE ? 'urgent' : 'high',
                    icon: $ticket->type->icon(),
                    color: $ticket->type->color()
                );
            }
        }
    }

    protected function notifyNewResponse(SupportTicket $ticket, TicketResponse $response, User $author): void
    {
        // Notificar criador do ticket (se não for ele quem respondeu)
        if ($ticket->user_id !== $author->id && ! $response->is_internal) {
            $this->notificationService->sendToUsers(
                userIds: [$ticket->user_id],
                type: 'tickets.nova_resposta',
                title: 'Nova Resposta no seu Chamado',
                message: "#{$ticket->ticket_number}: {$author->name} respondeu",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'normal',
                icon: 'comment',
                color: 'blue'
            );
        }

        // Notificar atendente atribuído (se não for ele quem respondeu)
        if ($ticket->assigned_to && $ticket->assigned_to !== $author->id) {
            $this->notificationService->sendToUsers(
                userIds: [$ticket->assigned_to],
                type: 'tickets.nova_resposta',
                title: 'Nova Resposta no Chamado',
                message: "#{$ticket->ticket_number}: {$author->name} respondeu",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'normal',
                icon: 'comment',
                color: 'blue'
            );
        }

        // Notificar observadores
        $watcherIds = $ticket->watchers()->where('user_id', '!=', $author->id)->pluck('user_id')->toArray();
        if (! empty($watcherIds)) {
            $this->notificationService->sendToUsers(
                userIds: $watcherIds,
                type: 'tickets.nova_resposta',
                title: 'Atualização no Chamado',
                message: "#{$ticket->ticket_number}: Nova resposta",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'low',
                icon: 'comment',
                color: 'gray'
            );
        }
    }

    protected function notifyStatusChange(
        SupportTicket $ticket,
        TicketStatus $oldStatus,
        TicketStatus $newStatus,
        User $changer
    ): void {
        $this->notificationService->sendToUsers(
            userIds: [$ticket->user_id],
            type: 'tickets.mudanca_status',
            title: 'Status do Chamado Atualizado',
            message: "#{$ticket->ticket_number}: {$oldStatus->label()} → {$newStatus->label()}",
            data: [
                'url' => route('tickets.show', $ticket->id),
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
            ],
            priority: 'normal',
            icon: $newStatus->icon(),
            color: $newStatus->color()
        );
    }

    protected function notifyTicketAssigned(SupportTicket $ticket, User $assignee, User $assignedBy): void
    {
        // Notificar o novo atendente
        $this->notificationService->sendToUsers(
            userIds: [$assignee->id],
            type: 'tickets.atribuicao',
            title: 'Novo Chamado Atribuído',
            message: "#{$ticket->ticket_number}: {$ticket->subject}",
            data: ['url' => route('tickets.show', $ticket->id)],
            priority: $ticket->priority === TicketPriority::URGENTE ? 'urgent' : 'high',
            icon: 'user-check',
            color: 'green'
        );

        // Notificar o criador do ticket (se não for ele quem atribuiu)
        if ($ticket->user_id !== $assignedBy->id) {
            $this->notificationService->sendToUsers(
                userIds: [$ticket->user_id],
                type: 'tickets.atribuicao_criador',
                title: 'Chamado Atribuído',
                message: "#{$ticket->ticket_number} foi atribuído para {$assignee->name}",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'normal',
                icon: 'user-check',
                color: 'blue'
            );
        }
    }

    protected function notifyQualityApproved(SupportTicket $ticket): void
    {
        // Notificar criador
        $this->notificationService->sendToUsers(
            userIds: [$ticket->user_id],
            type: 'tickets.aprovado_qualidade',
            title: 'Melhoria Aprovada pela Qualidade',
            message: "Sua melhoria #{$ticket->ticket_number} foi aprovada e será desenvolvida!",
            data: ['url' => route('tickets.show', $ticket->id)],
            priority: 'normal',
            icon: 'check-circle',
            color: 'green'
        );

        // Notificar equipe Unitop
        $unitopUsers = User::role('Equipe Unitop')->pluck('id')->toArray();
        if (! empty($unitopUsers)) {
            $this->notificationService->sendToUsers(
                userIds: $unitopUsers,
                type: 'tickets.melhoria_aprovada',
                title: 'Melhoria Aprovada para Desenvolvimento',
                message: "#{$ticket->ticket_number}: {$ticket->subject}",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'high',
                icon: 'thumbs-up',
                color: 'green'
            );
        }
    }

    protected function notifyQualityRejected(SupportTicket $ticket): void
    {
        $this->notificationService->sendToUsers(
            userIds: [$ticket->user_id],
            type: 'tickets.rejeitado_qualidade',
            title: 'Melhoria Não Aprovada',
            message: "Sua melhoria #{$ticket->ticket_number} não foi aprovada. Veja os comentários.",
            data: ['url' => route('tickets.show', $ticket->id)],
            priority: 'normal',
            icon: 'times-circle',
            color: 'red'
        );
    }
}
