<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasGambar
{
    /**
     * Absolute URL for the "gambar" column, which holds either an uploaded
     * path on the public disk or an external image URL (seeded demo data).
     */
    /**
     * Delete an uploaded image from the public disk. External URLs and empty
     * values are left alone.
     */
    public static function deleteGambarFile(?string $gambar): void
    {
        if ($gambar && ! str_starts_with($gambar, 'http://') && ! str_starts_with($gambar, 'https://')) {
            Storage::disk('public')->delete($gambar);
        }
    }

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
