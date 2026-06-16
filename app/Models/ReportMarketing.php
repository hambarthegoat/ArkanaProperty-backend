<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMarketing extends Model
{
    protected $table = 'tb_report_marketing';

    protected $fillable = [
        'id_marketing',
        'tanggal_report',
        'deskripsi_progres',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_report' => 'date',
        ];
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'id_marketing');
    }
}
