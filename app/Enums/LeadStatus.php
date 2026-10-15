<?php

namespace App\Enums;

enum LeadStatus: string
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
}
