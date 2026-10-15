<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PerumahanStatus: string implements HasColor, HasLabel
{
    case Aktif = 'aktif';
    case PreLaunch = 'pre_launch';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::PreLaunch => 'Pre-Launch',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Aktif => 'success',
            self::PreLaunch => 'warning',
        };
    }
}
