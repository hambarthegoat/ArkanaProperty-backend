<?php

namespace App\Enums;

enum LeadPriority: string
{
    case Tinggi = 'tinggi';
    case Sedang = 'sedang';
    case Rendah = 'rendah';

    public function label(): string
    {
        return match ($this) {
            self::Tinggi => 'Tinggi',
            self::Sedang => 'Sedang',
            self::Rendah => 'Rendah',
        };
    }
}
