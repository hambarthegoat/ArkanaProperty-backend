<?php

namespace App\Models;

use App\Enums\PerumahanStatus;
use App\Models\Concerns\HasGambar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Perumahan extends Model
{
    use HasFactory, HasGambar;

    protected $table = 'tb_perumahan';

    protected $fillable = [
        'nama_perumahan',
        'lokasi',
        'deskripsi',
        'gambar',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PerumahanStatus::class,
        ];
    }

    public function tipeRumah(): HasMany
    {
        return $this->hasMany(TipeRumah::class, 'id_perumahan');
    }

    public function kavling(): HasManyThrough
    {
        return $this->hasManyThrough(Kavling::class, TipeRumah::class, 'id_perumahan', 'id_tipe');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Leads::class, 'id_perumahan');
    }
}
