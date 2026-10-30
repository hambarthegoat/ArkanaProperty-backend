<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\KavlingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\KavlingResource;
use App\Models\Kavling;
use App\Models\TipeRumah;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            ->orderByRaw('length(nomor_rumah)')
            ->orderBy('nomor_rumah')
            ->orderBy('id')
            ->get();

        return $this->success(KavlingResource::collection($kavling), 'Kavling list retrieved successfully');
    }

    /**
     * Adds one or more houses to a kavling. A kavling is identified by
     * perumahan + blok + nomor_kavling, so sending the same kavling again
     * adds more houses to it; houses are numbered 1, 2, 3... per kavling.
     */
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
            'jumlah_rumah' => 'sometimes|integer|min:1|max:20',
        ]);

        $blok = $validated['blok'] ?? null;
        $jumlah = (int) ($validated['jumlah_rumah'] ?? 1);
        $status = $validated['status'] ?? KavlingStatus::Tersedia;

        $created = DB::transaction(function () use ($tipe, $validated, $blok, $jumlah, $status) {
            $siblings = $this->sameKavling($tipe->id_perumahan, $blok, $validated['nomor_kavling'])
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            // First and only house: keep the plain single-house kavling.
            if ($siblings->isEmpty() && $jumlah === 1) {
                return collect([$tipe->kavling()->create([
                    'blok' => $blok,
                    'nomor_kavling' => $validated['nomor_kavling'],
                    'status' => $status,
                ])]);
            }

            // A kavling that used to hold one unnumbered house gets numbered
            // so every house on it has a number.
            $last = (int) $siblings->pluck('nomor_rumah')
                ->filter(fn ($nomor) => $nomor !== null && ctype_digit((string) $nomor))
                ->map(fn ($nomor) => (int) $nomor)
                ->max();

            foreach ($siblings->whereNull('nomor_rumah') as $sibling) {
                $sibling->update(['nomor_rumah' => (string) ++$last]);
            }

            $houses = collect();

            for ($i = 0; $i < $jumlah; $i++) {
                $houses->push($tipe->kavling()->create([
                    'blok' => $blok,
                    'nomor_kavling' => $validated['nomor_kavling'],
                    'nomor_rumah' => (string) ++$last,
                    'status' => $status,
                ]));
            }

            return $houses;
        });

        $first = $created->first()->load(['tipeRumah.perumahan', 'leads']);
        $message = $created->count() > 1
            ? "{$created->count()} rumah berhasil ditambahkan pada kavling {$first->nomor_kavling}"
            : 'Kavling berhasil ditambahkan';

        return $this->success(
            [...(new KavlingResource($first))->resolve($request), 'jumlah_dibuat' => $created->count()],
            $message,
            201,
        );
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
            'nomor_rumah' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validated['status'] === KavlingStatus::Tersedia->value) {
            $validated['id_leads'] = null;
        }

        $this->assertHouseIsFree($kavling, $validated);

        $kavling->update($validated);

        return $this->success(new KavlingResource($kavling->load(['tipeRumah.perumahan', 'leads'])), 'Status kavling berhasil diupdate');
    }

    /**
     * Houses on the same kavling: same perumahan, blok and nomor_kavling.
     */
    private function sameKavling(int $idPerumahan, ?string $blok, string $nomorKavling)
    {
        return Kavling::query()
            ->whereHas('tipeRumah', fn ($q) => $q->where('id_perumahan', $idPerumahan))
            ->where('nomor_kavling', $nomorKavling)
            ->when($blok === null, fn ($q) => $q->whereNull('blok'), fn ($q) => $q->where('blok', $blok));
    }

    /**
     * Renumbering or moving a house must not collide with another house of
     * the target kavling. Plain status updates are not checked.
     */
    private function assertHouseIsFree(Kavling $kavling, array $validated): void
    {
        if (! array_intersect(['blok', 'nomor_kavling', 'nomor_rumah'], array_keys($validated))) {
            return;
        }

        $blok = array_key_exists('blok', $validated) ? $validated['blok'] : $kavling->blok;
        $nomorKavling = $validated['nomor_kavling'] ?? $kavling->nomor_kavling;
        $nomorRumah = array_key_exists('nomor_rumah', $validated) ? $validated['nomor_rumah'] : $kavling->nomor_rumah;

        $taken = $this->sameKavling($kavling->tipeRumah->id_perumahan, $blok, $nomorKavling)
            ->where('id', '!=', $kavling->id)
            ->when($nomorRumah === null, fn ($q) => $q->whereNull('nomor_rumah'), fn ($q) => $q->where('nomor_rumah', $nomorRumah))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['nomor_rumah' => 'Nomor rumah ini sudah dipakai pada kavling tersebut.']);
        }
    }
}
