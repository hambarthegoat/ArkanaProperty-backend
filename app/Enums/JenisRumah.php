<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisRumah: string implements HasColor, HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Subsidi => 'info',
            self::Premium => 'warning',
        };
    }
}
