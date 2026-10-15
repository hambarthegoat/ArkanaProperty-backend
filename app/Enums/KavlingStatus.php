<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum KavlingStatus: string implements HasColor, HasLabel
{
    case Tersedia = 'tersedia';
    case Booking = 'booking';
    case Terjual = 'terjual';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Booking => 'Booking',
            self::Terjual => 'Terjual',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Tersedia => 'gray',
            self::Booking => 'warning',
            self::Terjual => 'success',
        };
    }
}
