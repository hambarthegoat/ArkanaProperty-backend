<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\JenisRumah;
use App\Http\Controllers\Controller;
use App\Http\Resources\PerumahanResource;
use App\Http\Resources\TipeRumahResource;
use App\Models\Perumahan;
use App\Models\TipeRumah;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerumahanController extends Controller
{
    use ApiResponse;

    /**
     * Catalog of projects. Tipe rumah filters (jenis, budget, cicilan) are
     * applied both to which projects match and to the tipe_rumah returned,
     * so the frontend can render one card per matching house type.
     */
    public function index(Request $request)
    {
        $request->validate([
            'lokasi' => 'nullable|string|max:255',
            'jenis_rumah' => ['nullable', Rule::enum(JenisRumah::class)],
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0',
            'max_cicilan' => 'nullable|numeric|min:0',
        ]);

        $tipeFilter = function ($q) use ($request) {
            $q->when($request->filled('jenis_rumah'), fn ($q) => $q->where('jenis_rumah', $request->jenis_rumah))
                ->when($request->filled('min_budget'), fn ($q) => $q->where('harga', '>=', $request->min_budget))
                ->when($request->filled('max_budget'), fn ($q) => $q->where('harga', '<=', $request->max_budget))
                ->when($request->filled('max_cicilan'), fn ($q) => $q->where('cicilan', '<=', $request->max_cicilan));
        };

        $hasTipeFilter = collect($request->only(['jenis_rumah', 'min_budget', 'max_budget', 'max_cicilan']))
            ->filter(fn ($value) => filled($value))
            ->isNotEmpty();

        $perumahan = Perumahan::query()
            ->when($request->filled('lokasi'), fn ($q) => $q->whereLike('lokasi', '%'.$request->lokasi.'%'))
            ->when($hasTipeFilter, fn ($q) => $q->whereHas('tipeRumah', $tipeFilter))
            ->with(['tipeRumah' => function ($q) use ($tipeFilter) {
                $tipeFilter($q);
                $q->withCount(['kavling as kavling_tersedia_count' => fn ($k) => $k->where('status', 'tersedia')])
                    ->orderBy('harga');
            }])
            ->orderBy('nama_perumahan')
            ->get();

        return $this->success(PerumahanResource::collection($perumahan), 'Perumahan catalog retrieved successfully');
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

    public function showTipe(string $id)
    {
        $tipe = TipeRumah::with(['perumahan', 'kavling' => fn ($q) => $q->orderBy('blok')->orderBy('nomor_kavling')])
            ->withCount(['kavling as kavling_tersedia_count' => fn ($k) => $k->where('status', 'tersedia')])
            ->find($id);

        if (! $tipe) {
            return $this->error('Tipe rumah not found', 404);
        }

        return $this->success(new TipeRumahResource($tipe), 'Tipe rumah detail retrieved successfully');
    }

    /**
     * Options for the catalog search bar.
     */
    public function filters()
    {
        return $this->success([
            'lokasi' => Perumahan::query()->orderBy('lokasi')->distinct()->pluck('lokasi')->values(),
            'jenis_rumah' => collect(JenisRumah::cases())->map(fn ($c) => ['value' => $c->value, 'label' => $c->label()])->values(),
            'harga' => [
                'min' => (int) TipeRumah::min('harga'),
                'max' => (int) TipeRumah::max('harga'),
            ],
        ], 'Catalog filters retrieved successfully');
    }
}
