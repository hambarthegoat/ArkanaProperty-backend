<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlokResource;
use App\Models\Blok;
use App\Models\Perumahan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlokController extends Controller
{
    use ApiResponse;

    public function index(string $idPerumahan)
    {
        if (! Perumahan::whereKey($idPerumahan)->exists()) {
            return $this->error('Perumahan not found', 404);
        }

        $blok = Blok::withJumlahRumah()->where('id_perumahan', $idPerumahan)->orderBy('nama')->get();

        return $this->success(BlokResource::collection($blok), 'Blok list retrieved successfully');
    }

    /**
     * Creates an (empty) block in a project; houses are added to it later.
     */
    public function store(Request $request, string $idPerumahan)
    {
        $perumahan = Perumahan::find($idPerumahan);

        if (! $perumahan) {
            return $this->error('Perumahan not found', 404);
        }

        $request->merge(['nama' => trim((string) $request->input('nama'))]);

        $validated = $request->validate([
            'nama' => [
                'required', 'string', 'max:50',
                Rule::unique('tb_blok', 'nama')->where('id_perumahan', $perumahan->id),
            ],
        ]);

        $blok = $perumahan->blok()->create($validated);

        return $this->success(new BlokResource($blok), 'Blok berhasil dibuat', 201);
    }

    /**
     * Only an empty block can be removed; houses are never deleted with it.
     */
    public function destroy(string $id)
    {
        $blok = Blok::find($id);

        if (! $blok) {
            return $this->error('Blok not found', 404);
        }

        $jumlah = $blok->houses()->count();

        if ($jumlah > 0) {
            return $this->error("{$blok->nama} masih berisi {$jumlah} rumah sehingga tidak bisa dihapus.", 422);
        }

        $blok->delete();

        return $this->success(null, 'Blok berhasil dihapus');
    }
}
