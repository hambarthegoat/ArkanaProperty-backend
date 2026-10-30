<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blok extends Model
{
    protected $table = 'tb_blok';

    protected $fillable = [
        'id_perumahan',
        'nama',
    ];

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_perumahan');
    }

    /**
     * Houses of this block: kavling rows of the same perumahan whose blok
     * column carries the block name.
     */
    public function houses(): Builder
    {
        return Kavling::query()
            ->whereHas('tipeRumah', fn ($q) => $q->where('id_perumahan', $this->id_perumahan))
            ->where('blok', $this->nama);
    }

    /**
     * Adds jumlah_rumah (number of houses in the block) to each row.
     */
    public function scopeWithJumlahRumah(Builder $query): Builder
    {
        return $query->select('tb_blok.*')->addSelect([
            'jumlah_rumah' => Kavling::query()
                ->selectRaw('count(*)')
                ->join('tb_tipe_rumah', 'tb_tipe_rumah.id', '=', 'tb_kavling.id_tipe')
                ->whereColumn('tb_tipe_rumah.id_perumahan', 'tb_blok.id_perumahan')
                ->whereColumn('tb_kavling.blok', 'tb_blok.nama'),
        ]);
    }
}
