<?php

namespace App\Enums;

enum AgendaType: string
{
    case Survey = 'survey';
    case FollowUp = 'follow_up';
    case Closing = 'closing';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Survey => 'Survey',
            self::FollowUp => 'Follow Up',
            self::Closing => 'Closing',
            self::Lainnya => 'Lainnya',
        };
    }
}
