<?php

namespace App\Enums;

enum JenisRumah: string
{
    case Subsidi = 'subsidi';
    case Premium = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::Subsidi => 'Subsidi',
            self::Premium => 'Premium',
        };
    }
}
