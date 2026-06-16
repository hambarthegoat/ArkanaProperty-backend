<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perumahan extends Model
{
    protected $table = 'tb_perumahan';

    protected $fillable = [
        'nama_perumahan',
        'lokasi',
        'deskripsi',
    ];

    public function tipeRumah(): HasMany
    {
        return $this->hasMany(TipeRumah::class, 'id_perumahan');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Leads::class, 'id_perumahan');
    }
}
