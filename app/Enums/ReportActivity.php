<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReportActivity: string implements HasColor, HasLabel
{
    case SurveyLokasi = 'survey_lokasi';
    case FollowUp = 'follow_up';
    case Penawaran = 'penawaran';
    case Booking = 'booking';
    case Closing = 'closing';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::SurveyLokasi => 'Survey Lokasi',
            self::FollowUp => 'Follow Up',
            self::Penawaran => 'Telepon Penawaran',
            self::Booking => 'Booking Unit',
            self::Closing => 'Closing / PPJB',
            self::Lainnya => 'Lainnya',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SurveyLokasi => 'info',
            self::FollowUp => 'primary',
            self::Penawaran => 'gray',
            self::Booking => 'warning',
            self::Closing => 'success',
            self::Lainnya => 'gray',
        };
    }
}
