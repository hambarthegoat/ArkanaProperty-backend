<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadsController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_perumahan' => 'required|integer|exists:tb_perumahan,id',
            'nama_calon' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'file_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $path = $request->file('file_ktp')->store('ktp', 'public');

        $leads = Leads::create([
            'id_perumahan' => $request->id_perumahan,
            'nama_calon' => $request->nama_calon,
            'no_telp' => $request->no_telp,
            'file_ktp' => $path,
            'status_keseriusan' => 'pending',
        ]);

        return $this->success($leads, 'Form ketertarikan submitted successfully', 201);
    }
}
