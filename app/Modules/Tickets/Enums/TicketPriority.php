<?php

namespace App\Modules\Tickets\Enums;

enum TicketPriority: string
{
    case BAIXA = 'baixa';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case URGENTE = 'urgente';

    /**
     * Retorna label amigável
     */
    public function label(): string
    {
        return match($this) {
            self::BAIXA => 'Baixa',
            self::MEDIA => 'Média',
            self::ALTA => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    /**
     * Retorna cor para badge
     */
    public function color(): string
    {
        return match($this) {
            self::BAIXA => 'gray',
            self::MEDIA => 'blue',
            self::ALTA => 'orange',
            self::URGENTE => 'red',
        };
    }

    /**
     * Retorna ordem de prioridade (maior = mais urgente)
     */
    public function order(): int
    {
        return match($this) {
            self::BAIXA => 1,
            self::MEDIA => 2,
            self::ALTA => 3,
            self::URGENTE => 4,
        };
    }

    /**
     * Retorna SLA em horas
     */
    public function slaHours(): int
    {
        return match($this) {
            self::URGENTE => 4,
            self::ALTA => 24,
            self::MEDIA => 72,
            self::BAIXA => 168,
        };
    }

    /**
     * Retorna todos como array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna opções para select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($priority) => [$priority->value => $priority->label()])
            ->toArray();
    }
}
