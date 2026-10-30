<?php

namespace App\Http\Resources;

use App\Models\Kavling;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kavling */
class KavlingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_tipe' => $this->id_tipe,
            'blok' => $this->blok,
            'nomor_kavling' => $this->nomor_kavling,
            'nomor_rumah' => $this->nomor_rumah,
            'label' => $this->label,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'id_leads' => $this->when($this->relationLoaded('leads'), $this->id_leads),
            'leads' => $this->whenLoaded('leads', fn () => $this->leads ? [
                'id' => $this->leads->id,
                'nama_calon' => $this->leads->nama_calon,
            ] : null),
            'tipe_rumah' => new TipeRumahResource($this->whenLoaded('tipeRumah')),
        ];
    }
}
