<?php

namespace App\Enums;

enum ReportActivity: string
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
}
