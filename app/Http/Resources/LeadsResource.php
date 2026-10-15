<?php

namespace App\Http\Resources;

use App\Models\Leads;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Leads */
class LeadsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_perumahan' => $this->id_perumahan,
            'id_tipe' => $this->id_tipe,
            'id_marketing' => $this->id_marketing,
            'id_akun_leads' => $this->id_akun_leads,
            'nama_calon' => $this->nama_calon,
            'no_telp' => $this->no_telp,
            'pesan' => $this->pesan,
            'status_keseriusan' => $this->status_keseriusan?->value,
            'status_label' => $this->status_keseriusan?->label(),
            'prioritas' => $this->prioritas?->value,
            'prioritas_label' => $this->prioritas?->label(),
            'catatan' => $this->catatan,
            'has_ktp' => $this->hasKtp(),
            'perumahan' => $this->whenLoaded('perumahan', fn () => $this->perumahan ? [
                'id' => $this->perumahan->id,
                'nama_perumahan' => $this->perumahan->nama_perumahan,
                'lokasi' => $this->perumahan->lokasi,
            ] : null),
            'tipe_rumah' => $this->whenLoaded('tipeRumah', fn () => $this->tipeRumah ? [
                'id' => $this->tipeRumah->id,
                'nama_tipe' => $this->tipeRumah->nama_tipe,
            ] : null),
            'marketing' => $this->whenLoaded('marketing', fn () => $this->marketing ? new AkunResource($this->marketing) : null),
            'akun_leads' => $this->whenLoaded('akunLeads', fn () => $this->akunLeads ? new AkunResource($this->akunLeads) : null),
            'kavling' => $this->whenLoaded('kavling', fn () => $this->kavling ? new KavlingResource($this->kavling) : null),
            'reports' => ReportMarketingResource::collection($this->whenLoaded('reports')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
