<?php

namespace App\Http\Controllers\Api\Leads;

use App\Http\Controllers\Controller;
use App\Models\Kalender;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $agenda = Kalender::with('marketing')
            ->where('id_leads', $request->user()->id)
            ->where('tanggal_kegiatan', '>=', now())
            ->orderBy('tanggal_kegiatan')
            ->get();

        return $this->success($agenda, 'Upcoming agenda retrieved successfully');
    }
}
