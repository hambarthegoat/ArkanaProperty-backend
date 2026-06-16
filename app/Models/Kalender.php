<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kalender extends Model
{
    protected $table = 'tb_kalender';

    protected $fillable = [
        'id_marketing',
        'id_leads',
        'tanggal_kegiatan',
        'deskripsi_agenda',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kegiatan' => 'datetime',
        ];
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_marketing');
    }

    public function leadsAkun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_leads');
    }
}
