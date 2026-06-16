<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportMarketing;
use App\Traits\ApiResponse;

class ReportController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $reports = ReportMarketing::with('marketing')->latest('tanggal_report')->get();

        return $this->success($reports, 'Marketing reports retrieved successfully');
    }
}
