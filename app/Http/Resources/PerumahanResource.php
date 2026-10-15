<?php

namespace App\Http\Resources;

use App\Models\Perumahan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Perumahan */
class PerumahanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_perumahan' => $this->nama_perumahan,
            'lokasi' => $this->lokasi,
            'deskripsi' => $this->deskripsi,
            'gambar_url' => $this->gambar_url,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'harga_mulai' => $this->whenLoaded('tipeRumah', fn () => $this->tipeRumah->min('harga')),
            'unit' => $this->when(isset($this->total_unit), fn () => [
                'total' => (int) $this->total_unit,
                'tersedia' => (int) $this->unit_tersedia,
                'booking' => (int) $this->unit_booking,
                'terjual' => (int) $this->unit_terjual,
            ]),
            'tipe_rumah' => TipeRumahResource::collection($this->whenLoaded('tipeRumah')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
