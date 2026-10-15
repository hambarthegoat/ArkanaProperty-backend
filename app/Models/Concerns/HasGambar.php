<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasGambar
{
    /**
     * Absolute URL for the "gambar" column, which holds either an uploaded
     * path on the public disk or an external image URL (seeded demo data).
     */
    public function getGambarUrlAttribute(): ?string
    {
        $gambar = $this->gambar;

        if (! $gambar) {
            return null;
        }

        if (str_starts_with($gambar, 'http://') || str_starts_with($gambar, 'https://')) {
            return $gambar;
        }

        return Storage::disk('public')->url($gambar);
    }
}
