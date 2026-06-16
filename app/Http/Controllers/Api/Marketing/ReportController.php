<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ReportMarketing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_report' => 'required|date',
            'deskripsi_progres' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $report = ReportMarketing::create([
            'id_marketing' => $request->user()->id,
            'tanggal_report' => $request->tanggal_report,
            'deskripsi_progres' => $request->deskripsi_progres,
        ]);

        return $this->success($report, 'Daily report submitted successfully', 201);
    }
}
