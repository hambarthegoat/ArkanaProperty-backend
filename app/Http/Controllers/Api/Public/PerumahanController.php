<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Perumahan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PerumahanController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Perumahan::query()->with('tipeRumah');

        if ($request->filled('lokasi')) {
            $query->where('lokasi', 'like', '%'.$request->lokasi.'%');
        }

        if ($request->filled('jenis_rumah') || $request->filled('min_budget') || $request->filled('max_budget') || $request->filled('lama_angsuran')) {
            $query->whereHas('tipeRumah', function ($q) use ($request) {
                if ($request->filled('jenis_rumah')) {
                    $q->where('jenis_rumah', $request->jenis_rumah);
                }

                if ($request->filled('min_budget')) {
                    $q->where('harga', '>=', $request->min_budget);
                }

                if ($request->filled('max_budget')) {
                    $q->where('harga', '<=', $request->max_budget);
                }

                if ($request->filled('lama_angsuran')) {
                    $q->where('cicilan', '<=', $request->lama_angsuran);
                }
            });
        }

        $perumahan = $query->get();

        return $this->success($perumahan, 'Perumahan catalog retrieved successfully');
    }

    public function show(string $id)
    {
        $perumahan = Perumahan::with(['tipeRumah.kavling'])->find($id);

        if (! $perumahan) {
            return $this->error('Perumahan not found', 404);
        }

        return $this->success($perumahan, 'Perumahan detail retrieved successfully');
    }
}
