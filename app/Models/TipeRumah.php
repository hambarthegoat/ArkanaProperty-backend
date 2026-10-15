<?php

namespace App\Models;

use App\Enums\JenisRumah;
use App\Models\Concerns\HasGambar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipeRumah extends Model
{
    use HasFactory, HasGambar;

    protected $table = 'tb_tipe_rumah';

    protected $fillable = [
        'id_perumahan',
        'nama_tipe',
        'jenis_rumah',
        'harga',
        'dp',
        'cicilan',
        'luas_tanah',
        'luas_bangunan',
        'kamar_tidur',
        'kamar_mandi',
        'spesifikasi',
        'gambar',
    ];

    protected function casts(): array
    {
        return [
            'jenis_rumah' => JenisRumah::class,
            'harga' => 'integer',
            'dp' => 'integer',
            'cicilan' => 'integer',
            'luas_tanah' => 'integer',
            'luas_bangunan' => 'integer',
            'kamar_tidur' => 'integer',
            'kamar_mandi' => 'integer',
        ];
    }

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_perumahan');
    }

    public function kavling(): HasMany
    {
        return $this->hasMany(Kavling::class, 'id_tipe');
    }
}
