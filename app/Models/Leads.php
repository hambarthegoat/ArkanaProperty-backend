<?php

namespace App\Models;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Leads extends Model
{
    use HasFactory;

    protected $table = 'tb_leads';

    protected $fillable = [
        'id_perumahan',
        'id_tipe',
        'id_marketing',
        'id_akun_leads',
        'nama_calon',
        'no_telp',
        'pesan',
        'status_keseriusan',
        'prioritas',
        'catatan',
        'file_ktp',
    ];

    protected $attributes = [
        'status_keseriusan' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status_keseriusan' => LeadStatus::class,
            'prioritas' => LeadPriority::class,
        ];
    }

    public function perumahan(): BelongsTo
    {
        return $this->belongsTo(Perumahan::class, 'id_perumahan');
    }

    public function tipeRumah(): BelongsTo
    {
        return $this->belongsTo(TipeRumah::class, 'id_tipe');
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_marketing');
    }

    public function akunLeads(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_akun_leads');
    }

    public function kavling(): HasOne
    {
        return $this->hasOne(Kavling::class, 'id_leads')->latestOfMany();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReportMarketing::class, 'id_leads');
    }

    public function hasKtp(): bool
    {
        return filled($this->file_ktp);
    }
}
