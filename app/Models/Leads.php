<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leads extends Model
{
    protected $table = 'tb_leads';

    protected $fillable = [
        'id_perumahan',
        'id_marketing',
        'id_akun_leads',
        'nama_calon',
        'no_telp',
        'status_keseriusan',
        'file_ktp',
    ];

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_perumahan');
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_marketing');
    }

    public function akunLeads(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun_leads');
    }
}
