<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kavling extends Model
{
    protected $table = 'tb_kavling';

    protected $fillable = [
        'id_tipe',
        'nomor_kavling',
        'status',
    ];

    public function tipeRumah(): BelongsTo
    {
        return $this->belongsTo(TipeRumah::class, 'id_tipe');
    }
}
