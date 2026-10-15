<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Enums\AgendaType;
use App\Http\Controllers\Controller;
use App\Http\Resources\KalenderResource;
use App\Models\Akun;
use App\Models\Kalender;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $agenda = Kalender::with('leadsAkun')
            ->where('id_marketing', $request->user()->id)
            ->when($request->filled('from'), fn ($q) => $q->where('tanggal_kegiatan', '>=', $request->date('from')->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('tanggal_kegiatan', '<=', $request->date('to')->endOfDay()))
            ->orderBy('tanggal_kegiatan')
            ->get();

        return $this->success(KalenderResource::collection($agenda), 'Calendar agenda retrieved successfully');
    }

    /**
     * The agenda target can be given as id_lead (tb_leads.id, preferred) or
     * as id_leads (the lead's tb_akun.id, original contract). Either way it
     * must be a validated lead assigned to the logged-in marketing.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_lead' => 'required_without:id_leads|nullable|integer',
            'id_leads' => 'required_without:id_lead|nullable|integer',
            'judul' => 'nullable|string|max:255',
            'jenis' => ['nullable', Rule::enum(AgendaType::class)],
            'tanggal_kegiatan' => 'required|date',
            'lokasi' => 'nullable|string|max:255',
            'deskripsi_agenda' => 'nullable|string',
        ]);

        $lead = Leads::where('id_marketing', $request->user()->id)
            ->whereNotNull('id_akun_leads')
            ->when($request->filled('id_lead'),
                fn ($q) => $q->where('id', $request->id_lead),
                fn ($q) => $q->where('id_akun_leads', $request->id_leads))
            ->first();

        if (! $lead || ! Akun::where('id', $lead->id_akun_leads)->where('role', 'leads')->exists()) {
            return $this->error('Selected lead is not a validated lead assigned to you', 422);
        }

        $kalender = Kalender::create([
            'id_marketing' => $request->user()->id,
            'id_leads' => $lead->id_akun_leads,
            'judul' => $validated['judul'] ?? null,
            'jenis' => $validated['jenis'] ?? AgendaType::Lainnya,
            'tanggal_kegiatan' => $validated['tanggal_kegiatan'],
            'lokasi' => $validated['lokasi'] ?? null,
            'deskripsi_agenda' => $validated['deskripsi_agenda'] ?? null,
        ]);

        return $this->success(new KalenderResource($kalender->load('leadsAkun')), 'Calendar agenda created successfully', 201);
    }
}
