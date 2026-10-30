<?php

namespace App\Http\Resources;

use App\Models\Blok;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Blok */
class BlokResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_perumahan' => $this->id_perumahan,
            'nama' => $this->nama,
            'jumlah_rumah' => $this->when(isset($this->jumlah_rumah), fn () => (int) $this->jumlah_rumah),
        ];
    }
}
