<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\JenisRumah;
use App\Enums\PerumahanStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PerumahanResource;
use App\Models\Perumahan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PerumahanController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $perumahan = Perumahan::with(['tipeRumah' => fn ($q) => $q->orderBy('harga')])
            ->withUnitCounts()
            ->orderBy('nama_perumahan')
            ->get();

        return $this->success(PerumahanResource::collection($perumahan), 'Perumahan list retrieved successfully');
    }

    public function show(string $id)
    {
        $perumahan = Perumahan::with(['tipeRumah' => fn ($q) => $q->orderBy('harga'), 'tipeRumah.kavling'])
            ->withUnitCounts()
            ->find($id);

        if (! $perumahan) {
            return $this->error('Perumahan not found', 404);
        }

        return $this->success(new PerumahanResource($perumahan), 'Perumahan detail retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perumahan' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => ['nullable', Rule::enum(PerumahanStatus::class)],
            'gambar' => 'nullable|image|max:5120',
            'tipe_rumah' => 'nullable|array',
            'tipe_rumah.*.nama_tipe' => 'required_with:tipe_rumah|string|max:255',
            'tipe_rumah.*.jenis_rumah' => ['required_with:tipe_rumah', Rule::enum(JenisRumah::class)],
            'tipe_rumah.*.harga' => 'required_with:tipe_rumah|numeric|min:0',
            'tipe_rumah.*.dp' => 'nullable|numeric|min:0',
            'tipe_rumah.*.cicilan' => 'nullable|numeric|min:0',
            'tipe_rumah.*.luas_tanah' => 'nullable|integer|min:0',
            'tipe_rumah.*.luas_bangunan' => 'nullable|integer|min:0',
            'tipe_rumah.*.kamar_tidur' => 'nullable|integer|min:0',
            'tipe_rumah.*.kamar_mandi' => 'nullable|integer|min:0',
            'tipe_rumah.*.spesifikasi' => 'nullable|string',
        ]);

        $perumahan = DB::transaction(function () use ($request, $validated) {
            $perumahan = Perumahan::create([
                'nama_perumahan' => $validated['nama_perumahan'],
                'lokasi' => $validated['lokasi'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $validated['status'] ?? PerumahanStatus::Aktif,
                'gambar' => $request->file('gambar')?->store('perumahan', 'public'),
            ]);

            foreach ($validated['tipe_rumah'] ?? [] as $tipe) {
                $perumahan->tipeRumah()->create($tipe);
            }

            return Perumahan::with('tipeRumah')->withUnitCounts()->find($perumahan->id);
        });

        return $this->success(new PerumahanResource($perumahan), 'Perumahan created successfully', 201);
    }

    /**
     * Edit project details. File uploads must be sent as POST with
     * `_method=PUT` because PHP does not parse multipart PUT bodies.
     */
    public function update(Request $request, string $id)
    {
        $perumahan = Perumahan::find($id);

        if (! $perumahan) {
            return $this->error('Perumahan not found', 404);
        }

        $validated = $request->validate([
            'nama_perumahan' => 'sometimes|required|string|max:255',
            'lokasi' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|nullable|string',
            'status' => ['sometimes', 'required', Rule::enum(PerumahanStatus::class)],
            'gambar' => 'nullable|image|max:5120',
        ]);

        $old = $perumahan->gambar;
        $data = collect($validated)->except('gambar')->all();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('perumahan', 'public');
        }

        $perumahan->update($data);

        if (isset($data['gambar'])) {
            Perumahan::deleteGambarFile($old);
        }

        return $this->success(
            new PerumahanResource(Perumahan::with(['tipeRumah' => fn ($q) => $q->orderBy('harga')])->withUnitCounts()->find($perumahan->id)),
            'Perumahan updated successfully',
        );
    }

    /**
     * Deleting also removes the project's house types and available units,
     * so it is refused while leads or booked/sold units exist.
     */
    public function destroy(string $id)
    {
        $perumahan = Perumahan::find($id);

        if (! $perumahan) {
            return $this->error('Perumahan not found', 404);
        }

        if ($blocker = $perumahan->deletionBlocker()) {
            return $this->error($blocker, 422);
        }

        $gambar = $perumahan->gambar;
        $perumahan->delete();
        Perumahan::deleteGambarFile($gambar);

        return $this->success(null, 'Perumahan deleted successfully');
    }
}
