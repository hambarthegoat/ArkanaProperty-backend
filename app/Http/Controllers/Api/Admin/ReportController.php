<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportMarketingResource;
use App\Models\ReportMarketing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'id_marketing' => 'nullable|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $reports = ReportMarketing::with(['marketing', 'leads'])
            ->when($request->filled('id_marketing'), fn ($q) => $q->where('id_marketing', $request->id_marketing))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('tanggal_report', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('tanggal_report', '<=', $request->to))
            ->latest('tanggal_report')
            ->latest()
            ->get();

        return $this->success(ReportMarketingResource::collection($reports), 'Marketing reports retrieved successfully');
    }
}
