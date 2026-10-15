<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Validasi',
            self::Validated => 'Tervalidasi',
            self::Rejected => 'Ditolak',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Validated => 'success',
            self::Rejected => 'danger',
        };
    }
}
