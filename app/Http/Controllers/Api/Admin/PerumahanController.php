<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perumahan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PerumahanController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $perumahan = Perumahan::with('tipeRumah.kavling')->get();

        return $this->success($perumahan, 'Perumahan list retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_perumahan' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe_rumah' => 'nullable|array',
            'tipe_rumah.*.nama_tipe' => 'required_with:tipe_rumah|string|max:255',
            'tipe_rumah.*.jenis_rumah' => 'required_with:tipe_rumah|in:subsidi,premium',
            'tipe_rumah.*.harga' => 'required_with:tipe_rumah|numeric',
            'tipe_rumah.*.dp' => 'nullable|numeric',
            'tipe_rumah.*.cicilan' => 'nullable|numeric',
            'tipe_rumah.*.spesifikasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $perumahan = DB::transaction(function () use ($request) {
            $perumahan = Perumahan::create([
                'nama_perumahan' => $request->nama_perumahan,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
            ]);

            foreach ($request->input('tipe_rumah', []) as $tipe) {
                $perumahan->tipeRumah()->create($tipe);
            }

            return $perumahan->load('tipeRumah');
        });

        return $this->success($perumahan, 'Perumahan created successfully', 201);
    }
}
