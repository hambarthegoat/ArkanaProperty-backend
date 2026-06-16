<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kavling;
use App\Models\TipeRumah;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KavlingController extends Controller
{
    use ApiResponse;

    public function store(Request $request, string $idTipe)
    {
        $tipe = TipeRumah::find($idTipe);

        if (! $tipe) {
            return $this->error('Tipe rumah tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'nomor_kavling' => 'required|string|max:50',
            'status' => 'sometimes|in:tersedia,terjual',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $kavling = Kavling::create([
            'id_tipe' => $tipe->id,
            'nomor_kavling' => $request->nomor_kavling,
            'status' => $request->input('status', 'tersedia'),
        ]);

        return $this->success($kavling, 'Kavling berhasil ditambahkan', 201);
    }

    public function update(Request $request, string $id)
    {
        $kavling = Kavling::find($id);

        if (! $kavling) {
            return $this->error('Kavling tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:tersedia,terjual',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $kavling->update(['status' => $request->status]);

        return $this->success($kavling, 'Status kavling berhasil diupdate');
    }
}
