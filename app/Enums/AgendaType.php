<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AgendaType: string implements HasColor, HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Survey => 'info',
            self::FollowUp => 'primary',
            self::Closing => 'success',
            self::Lainnya => 'gray',
        };
    }
}
