<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Http\Resources\ReportMarketingResource;
use App\Models\Akun;
use App\Models\Kavling;
use App\Models\Leads;
use App\Models\Perumahan;
use App\Models\ReportMarketing;
use App\Traits\ApiResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $leads = Leads::query()->selectRaw('status_keseriusan, count(*) as total')->groupBy('status_keseriusan')->pluck('total', 'status_keseriusan');
        $unit = Kavling::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return $this->success([
            'leads' => [
                'total' => (int) $leads->sum(),
                'pending' => (int) ($leads['pending'] ?? 0),
                'validated' => (int) ($leads['validated'] ?? 0),
                'rejected' => (int) ($leads['rejected'] ?? 0),
            ],
            'unit' => [
                'total' => (int) $unit->sum(),
                'tersedia' => (int) ($unit['tersedia'] ?? 0),
                'booking' => (int) ($unit['booking'] ?? 0),
                'terjual' => (int) ($unit['terjual'] ?? 0),
            ],
            'perumahan' => Perumahan::count(),
            'marketing' => Akun::where('role', 'marketing')->count(),
            'leads_terbaru' => LeadsResource::collection(
                Leads::with(['perumahan', 'tipeRumah', 'marketing'])->latest()->limit(5)->get()
            ),
            'report_terbaru' => ReportMarketingResource::collection(
                ReportMarketing::with(['marketing', 'leads'])->latest('tanggal_report')->latest()->limit(5)->get()
            ),
        ], 'Dashboard summary retrieved successfully');
    }
}
