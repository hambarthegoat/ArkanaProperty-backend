<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipeRumah extends Model
{
    protected $table = 'tb_tipe_rumah';

    protected $fillable = [
        'id_perumahan',
        'nama_tipe',
        'jenis_rumah',
        'harga',
        'dp',
        'cicilan',
        'spesifikasi',
    ];

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_perumahan');
    }

    public function kavling(): HasMany
    {
        return $this->hasMany(Kavling::class, 'id_tipe');
    }
}
