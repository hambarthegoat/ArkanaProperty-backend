<?php

namespace App\Http\Controllers\Api\Leads;

use App\Http\Controllers\Controller;
use App\Http\Resources\KalenderResource;
use App\Models\Kalender;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    use ApiResponse;

    /**
     * Upcoming agenda by default; ?semua=1 also returns past items so the
     * portal can show completed reminders.
     */
    public function index(Request $request)
    {
        $agenda = Kalender::with('marketing')
            ->where('id_leads', $request->user()->id)
            ->unless($request->boolean('semua'), fn ($q) => $q->where('tanggal_kegiatan', '>=', now()))
            ->orderBy('tanggal_kegiatan')
            ->get();

        return $this->success(KalenderResource::collection($agenda), 'Upcoming agenda retrieved successfully');
    }
}
