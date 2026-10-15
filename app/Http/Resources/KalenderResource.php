<?php

namespace App\Http\Resources;

use App\Models\Kalender;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kalender */
class KalenderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_marketing' => $this->id_marketing,
            'id_leads' => $this->id_leads,
            'judul' => $this->judul ?? $this->jenis?->label(),
            'jenis' => $this->jenis?->value,
            'jenis_label' => $this->jenis?->label(),
            'tanggal_kegiatan' => $this->tanggal_kegiatan?->toIso8601String(),
            'lokasi' => $this->lokasi,
            'deskripsi_agenda' => $this->deskripsi_agenda,
            'marketing' => $this->whenLoaded('marketing', fn () => $this->marketing ? new AkunResource($this->marketing) : null),
            'leads' => $this->whenLoaded('leadsAkun', fn () => $this->leadsAkun ? new AkunResource($this->leadsAkun) : null),
        ];
    }
}
