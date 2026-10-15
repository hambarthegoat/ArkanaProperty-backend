<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\KavlingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\KavlingResource;
use App\Models\Kavling;
use App\Models\TipeRumah;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KavlingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'id_perumahan' => 'nullable|integer',
            'id_tipe' => 'nullable|integer',
            'blok' => 'nullable|string|max:50',
            'status' => ['nullable', Rule::enum(KavlingStatus::class)],
            'search' => 'nullable|string|max:100',
        ]);

        $kavling = Kavling::query()
            ->with(['tipeRumah.perumahan', 'leads'])
            ->when($request->filled('id_perumahan'), fn ($q) => $q->whereHas('tipeRumah', fn ($t) => $t->where('id_perumahan', $request->id_perumahan)))
            ->when($request->filled('id_tipe'), fn ($q) => $q->where('id_tipe', $request->id_tipe))
            ->when($request->filled('blok'), fn ($q) => $q->where('blok', $request->blok))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->whereLike('nomor_kavling', '%'.$request->search.'%')
                        ->orWhereHas('leads', fn ($l) => $l->whereLike('nama_calon', '%'.$request->search.'%'));
                });
            })
            ->orderBy('blok')
            ->orderBy('nomor_kavling')
            ->get();

        return $this->success(KavlingResource::collection($kavling), 'Kavling list retrieved successfully');
    }

    public function store(Request $request, string $idTipe)
    {
        $tipe = TipeRumah::find($idTipe);

        if (! $tipe) {
            return $this->error('Tipe rumah tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'blok' => 'nullable|string|max:50',
            'nomor_kavling' => 'required|string|max:50',
            'status' => ['sometimes', Rule::enum(KavlingStatus::class)],
        ]);

        $kavling = $tipe->kavling()->create([
            'blok' => $validated['blok'] ?? null,
            'nomor_kavling' => $validated['nomor_kavling'],
            'status' => $validated['status'] ?? KavlingStatus::Tersedia,
        ]);

        return $this->success(new KavlingResource($kavling->load(['tipeRumah.perumahan', 'leads'])), 'Kavling berhasil ditambahkan', 201);
    }

    /**
     * Update status, block/number or the buyer (lead) of a unit. Setting a
     * unit back to "tersedia" releases its buyer.
     */
    public function update(Request $request, string $id)
    {
        $kavling = Kavling::find($id);

        if (! $kavling) {
            return $this->error('Kavling tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(KavlingStatus::class)],
            'id_leads' => 'nullable|integer|exists:tb_leads,id',
            'blok' => 'sometimes|nullable|string|max:50',
            'nomor_kavling' => 'sometimes|string|max:50',
        ]);

        if ($validated['status'] === KavlingStatus::Tersedia->value) {
            $validated['id_leads'] = null;
        }

        $kavling->update($validated);

        return $this->success(new KavlingResource($kavling->load(['tipeRumah.perumahan', 'leads'])), 'Status kavling berhasil diupdate');
    }
}
