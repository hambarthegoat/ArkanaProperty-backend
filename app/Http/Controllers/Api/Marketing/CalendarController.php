<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Kalender;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_leads' => 'required|integer|exists:tb_akun,id',
            'tanggal_kegiatan' => 'required|date',
            'deskripsi_agenda' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $leadAkun = Akun::where('id', $request->id_leads)->where('role', 'leads')->first();

        if (! $leadAkun) {
            return $this->error('Selected account is not a valid leads account', 422);
        }

        $kalender = Kalender::create([
            'id_marketing' => $request->user()->id,
            'id_leads' => $request->id_leads,
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'deskripsi_agenda' => $request->deskripsi_agenda,
        ]);

        return $this->success($kalender, 'Calendar agenda created successfully', 201);
    }
}
