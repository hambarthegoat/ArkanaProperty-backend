<?php

namespace App\Models;

use App\Enums\KavlingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kavling extends Model
{
    use HasFactory;

    protected $table = 'tb_kavling';

    protected $fillable = [
        'id_tipe',
        'blok',
        'nomor_kavling',
        'status',
        'id_leads',
    ];

    /**
     * An available unit has no buyer, whichever client changes the status.
     */
    protected static function booted(): void
    {
        static::saving(function (Kavling $kavling) {
            if ($kavling->status === KavlingStatus::Tersedia) {
                $kavling->id_leads = null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => KavlingStatus::class,
        ];
    }

    public function tipeRumah(): BelongsTo
    {
        return $this->belongsTo(TipeRumah::class, 'id_tipe');
    }

    public function leads(): BelongsTo
    {
        return $this->belongsTo(Leads::class, 'id_leads');
    }
}
