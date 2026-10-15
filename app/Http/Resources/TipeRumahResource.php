<?php

namespace App\Http\Resources;

use App\Models\TipeRumah;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TipeRumah */
class TipeRumahResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_perumahan' => $this->id_perumahan,
            'nama_tipe' => $this->nama_tipe,
            'jenis_rumah' => $this->jenis_rumah?->value,
            'jenis_rumah_label' => $this->jenis_rumah?->label(),
            'harga' => $this->harga,
            'dp' => $this->dp,
            'cicilan' => $this->cicilan,
            'luas_tanah' => $this->luas_tanah,
            'luas_bangunan' => $this->luas_bangunan,
            'kamar_tidur' => $this->kamar_tidur,
            'kamar_mandi' => $this->kamar_mandi,
            'spesifikasi' => $this->spesifikasi,
            'gambar_url' => $this->gambar_url ?? $this->whenLoaded('perumahan', fn () => $this->perumahan->gambar_url),
            'unit_tersedia' => $this->whenCounted('kavling_tersedia'),
            'perumahan' => new PerumahanResource($this->whenLoaded('perumahan')),
            'kavling' => KavlingResource::collection($this->whenLoaded('kavling')),
        ];
    }
}
