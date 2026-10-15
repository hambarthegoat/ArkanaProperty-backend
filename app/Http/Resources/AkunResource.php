<?php

namespace App\Http\Resources;

use App\Models\Akun;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Akun */
class AkunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'nama' => $this->nama ?? $this->username,
            'no_telp' => $this->no_telp,
            'no_wa' => Phone::toWhatsApp($this->no_telp),
        ];
    }
}
