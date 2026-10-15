<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Enums\ReportActivity;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportMarketingResource;
use App\Models\Leads;
use App\Models\ReportMarketing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $reports = ReportMarketing::with('leads')
            ->where('id_marketing', $request->user()->id)
            ->latest('tanggal_report')
            ->latest()
            ->get();

        return $this->success(ReportMarketingResource::collection($reports), 'Daily reports retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_report' => 'required|date',
            'id_lead' => 'nullable|integer',
            'jenis_aktivitas' => ['nullable', Rule::enum(ReportActivity::class)],
            'deskripsi_progres' => 'required|string',
        ]);

        if ($request->filled('id_lead')
            && ! Leads::where('id', $request->id_lead)->where('id_marketing', $request->user()->id)->exists()) {
            return $this->error('Selected lead is not assigned to you', 422);
        }

        $report = ReportMarketing::create([
            'id_marketing' => $request->user()->id,
            'id_leads' => $validated['id_lead'] ?? null,
            'tanggal_report' => $validated['tanggal_report'],
            'jenis_aktivitas' => $validated['jenis_aktivitas'] ?? null,
            'deskripsi_progres' => $validated['deskripsi_progres'],
        ]);

        return $this->success(new ReportMarketingResource($report->load('leads')), 'Daily report submitted successfully', 201);
    }
}
