<?php

namespace App\Support;

class Phone
{
    /**
     * Normalize an Indonesian phone number to the local "08…" form so the
     * same person always maps to the same leads username.
     */
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * International "62…" form used for wa.me links.
     */
    public static function toWhatsApp(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $local = self::normalize($phone);

        return str_starts_with($local, '0') ? '62'.substr($local, 1) : $local;
    }
}
