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
        'nomor_rumah',
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

        // A house in a block that has no record yet (API, panel or seeder)
        // creates the block, so the block list always includes it.
        static::saved(function (Kavling $kavling) {
            if (blank($kavling->blok)) {
                return;
            }

            $idPerumahan = $kavling->tipeRumah()->value('id_perumahan');

            if ($idPerumahan) {
                Blok::createOrFirst(['id_perumahan' => $idPerumahan, 'nama' => $kavling->blok]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => KavlingStatus::class,
        ];
    }

    /**
     * "A-01" for a single-house kavling, "A-01 · Rumah 2" when the kavling
     * holds several houses.
     */
    public function getLabelAttribute(): string
    {
        return $this->nomor_rumah !== null && $this->nomor_rumah !== ''
            ? "{$this->nomor_kavling} · Rumah {$this->nomor_rumah}"
            : $this->nomor_kavling;
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
