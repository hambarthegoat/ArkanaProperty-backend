<?php

namespace App\Http\Resources;

use App\Models\ReportMarketing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportMarketing */
class ReportMarketingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_marketing' => $this->id_marketing,
            'id_leads' => $this->id_leads,
            'tanggal_report' => $this->tanggal_report?->toDateString(),
            'jenis_aktivitas' => $this->jenis_aktivitas?->value,
            'jenis_aktivitas_label' => $this->jenis_aktivitas?->label(),
            'deskripsi_progres' => $this->deskripsi_progres,
            'marketing' => $this->whenLoaded('marketing', fn () => $this->marketing ? new AkunResource($this->marketing) : null),
            'leads' => $this->whenLoaded('leads', fn () => $this->leads ? [
                'id' => $this->leads->id,
                'nama_calon' => $this->leads->nama_calon,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
