<?php

namespace App\Enums;

enum PerumahanStatus: string
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
}
