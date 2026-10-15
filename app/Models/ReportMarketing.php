<?php

namespace App\Models;

use App\Enums\ReportActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMarketing extends Model
{
    use HasFactory;

    protected $table = 'tb_report_marketing';

    protected $fillable = [
        'id_marketing',
        'id_leads',
        'tanggal_report',
        'jenis_aktivitas',
        'deskripsi_progres',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_report' => 'date',
            'jenis_aktivitas' => ReportActivity::class,
        ];
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_marketing');
    }

    public function leads(): BelongsTo
    {
        return $this->belongsTo(Leads::class, 'id_leads');
    }
}
