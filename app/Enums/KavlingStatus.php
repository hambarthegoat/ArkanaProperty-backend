<?php

namespace App\Enums;

enum KavlingStatus: string
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
}
