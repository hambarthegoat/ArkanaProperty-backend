<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Models\Leads;
use App\Models\TipeRumah;
use App\Support\Phone;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    use ApiResponse;

    /**
     * Interest form from the landing / property pages. KTP is optional
     * here; admins can attach it later before validating the lead.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_perumahan' => 'required_without:id_tipe|nullable|integer|exists:tb_perumahan,id',
            'id_tipe' => 'nullable|integer|exists:tb_tipe_rumah,id',
            'nama_calon' => 'required|string|max:255',
            'no_telp' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'pesan' => 'nullable|string|max:2000',
            'file_ktp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $tipe = $request->filled('id_tipe') ? TipeRumah::find($request->id_tipe) : null;

        $leads = Leads::create([
            'id_perumahan' => $tipe?->id_perumahan ?? $request->id_perumahan,
            'id_tipe' => $tipe?->id,
            'nama_calon' => $request->nama_calon,
            'no_telp' => Phone::normalize($request->no_telp),
            'pesan' => $request->pesan,
            'file_ktp' => $request->file('file_ktp')?->store('ktp', 'local'),
            'status_keseriusan' => 'pending',
        ]);

        return $this->success(new LeadsResource($leads), 'Form ketertarikan submitted successfully', 201);
    }
}
