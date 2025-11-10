<?php

namespace App\Enums;

enum TicketStatus: string
{
    case NOVO = 'novo';
    case AGUARDANDO_QUALIDADE = 'aguardando_qualidade';
    case APROVADO_QUALIDADE = 'aprovado_qualidade';
    case REJEITADO_QUALIDADE = 'rejeitado_qualidade';
    case EM_ATENDIMENTO = 'em_atendimento';
    case AGUARDANDO_CLIENTE = 'aguardando_cliente';
    case RESOLVIDO = 'resolvido';
    case FECHADO = 'fechado';
    case CANCELADO = 'cancelado';

    /**
     * Retorna label amigável
     */
    public function label(): string
    {
        return match($this) {
            self::NOVO => 'Novo',
            self::AGUARDANDO_QUALIDADE => 'Aguardando Qualidade',
            self::APROVADO_QUALIDADE => 'Aprovado pela Qualidade',
            self::REJEITADO_QUALIDADE => 'Rejeitado pela Qualidade',
            self::EM_ATENDIMENTO => 'Em Atendimento',
            self::AGUARDANDO_CLIENTE => 'Aguardando Cliente',
            self::RESOLVIDO => 'Resolvido',
            self::FECHADO => 'Fechado',
            self::CANCELADO => 'Cancelado',
        };
    }

    /**
     * Retorna cor para badge
     */
    public function color(): string
    {
        return match($this) {
            self::NOVO => 'blue',
            self::AGUARDANDO_QUALIDADE => 'purple',
            self::APROVADO_QUALIDADE => 'green',
            self::REJEITADO_QUALIDADE => 'red',
            self::EM_ATENDIMENTO => 'yellow',
            self::AGUARDANDO_CLIENTE => 'orange',
            self::RESOLVIDO => 'green',
            self::FECHADO => 'gray',
            self::CANCELADO => 'red',
        };
    }

    /**
     * Retorna ícone
     */
    public function icon(): string
    {
        return match($this) {
            self::NOVO => 'plus-circle',
            self::AGUARDANDO_QUALIDADE => 'clock',
            self::APROVADO_QUALIDADE => 'check-circle',
            self::REJEITADO_QUALIDADE => 'times-circle',
            self::EM_ATENDIMENTO => 'cog',
            self::AGUARDANDO_CLIENTE => 'user-clock',
            self::RESOLVIDO => 'check-double',
            self::FECHADO => 'lock',
            self::CANCELADO => 'ban',
        };
    }

    /**
     * Verifica se está aberto
     */
    public function isOpen(): bool
    {
        return !in_array($this, [
            self::RESOLVIDO,
            self::FECHADO,
            self::CANCELADO,
        ]);
    }

    /**
     * Verifica se está fechado
     */
    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    /**
     * Verifica se pode ser editado
     */
    public function canBeEdited(): bool
    {
        return !in_array($this, [
            self::FECHADO,
            self::CANCELADO,
        ]);
    }

    /**
     * Próximos status possíveis
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::NOVO => [
                self::AGUARDANDO_QUALIDADE,
                self::EM_ATENDIMENTO,
                self::CANCELADO,
            ],
            self::AGUARDANDO_QUALIDADE => [
                self::APROVADO_QUALIDADE,
                self::REJEITADO_QUALIDADE,
                self::CANCELADO,
            ],
            self::APROVADO_QUALIDADE => [
                self::EM_ATENDIMENTO,
                self::CANCELADO,
            ],
            self::REJEITADO_QUALIDADE => [
                self::NOVO,
                self::FECHADO,
            ],
            self::EM_ATENDIMENTO => [
                self::AGUARDANDO_CLIENTE,
                self::RESOLVIDO,
                self::CANCELADO,
            ],
            self::AGUARDANDO_CLIENTE => [
                self::EM_ATENDIMENTO,
                self::RESOLVIDO,
                self::CANCELADO,
            ],
            self::RESOLVIDO => [
                self::FECHADO,
                self::EM_ATENDIMENTO, // Caso precise reabrir
            ],
            self::FECHADO => [],
            self::CANCELADO => [],
        };
    }

    /**
     * Verifica se pode transicionar para outro status
     */
    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    /**
     * Retorna todos como array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna status abertos
     */
    public static function openStatuses(): array
    {
        return collect(self::cases())
            ->filter(fn($status) => $status->isOpen())
            ->map(fn($status) => $status->value)
            ->toArray();
    }

    /**
     * Retorna opções para select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }
}
