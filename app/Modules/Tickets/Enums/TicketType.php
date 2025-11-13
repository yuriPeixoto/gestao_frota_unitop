<?php

namespace App\Modules\Tickets\Enums;

enum TicketType: string
{
    case BUG = 'bug';
    case MELHORIA = 'melhoria';
    case DUVIDA = 'duvida';
    case SUPORTE = 'suporte';

    /**
     * Retorna label amigável
     */
    public function label(): string
    {
        return match($this) {
            self::BUG => 'Bug/Erro',
            self::MELHORIA => 'Melhoria/Feature',
            self::DUVIDA => 'Dúvida',
            self::SUPORTE => 'Suporte Técnico',
        };
    }

    /**
     * Retorna cor para badge
     */
    public function color(): string
    {
        return match($this) {
            self::BUG => 'red',
            self::MELHORIA => 'yellow',
            self::DUVIDA => 'blue',
            self::SUPORTE => 'green',
        };
    }

    /**
     * Retorna ícone
     */
    public function icon(): string
    {
        return match($this) {
            self::BUG => 'bug',
            self::MELHORIA => 'lightbulb',
            self::DUVIDA => 'question-circle',
            self::SUPORTE => 'headset',
        };
    }

    /**
     * Verifica se precisa passar pela qualidade
     */
    public function requiresQualityReview(): bool
    {
        return $this === self::MELHORIA;
    }

    /**
     * Retorna todos os tipos como array
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
            ->mapWithKeys(fn($type) => [$type->value => $type->label()])
            ->toArray();
    }
}
