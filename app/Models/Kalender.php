<?php

namespace App\Models;

use App\Enums\AgendaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kalender extends Model
{
    use HasFactory;

    protected $table = 'tb_kalender';

    protected $fillable = [
        'id_marketing',
        'id_leads',
        'judul',
        'jenis',
        'tanggal_kegiatan',
        'lokasi',
        'deskripsi_agenda',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kegiatan' => 'datetime',
            'jenis' => AgendaType::class,
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
