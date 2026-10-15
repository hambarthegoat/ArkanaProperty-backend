<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadPriority: string implements HasColor, HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Tinggi => 'danger',
            self::Sedang => 'warning',
            self::Rendah => 'gray',
        };
    }
}
