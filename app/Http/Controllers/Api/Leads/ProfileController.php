<?php

namespace App\Http\Controllers\Api\Leads;

use App\Enums\KavlingStatus;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AkunResource;
use App\Http\Resources\LeadsResource;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Consumer portal summary: the account, its latest lead (project, unit,
     * marketing) and a purchase timeline derived from that data.
     */
    public function show(Request $request)
    {
        $lead = Leads::with(['perumahan', 'tipeRumah', 'marketing', 'kavling.tipeRumah'])
            ->where('id_akun_leads', $request->user()->id)
            ->latest()
            ->first();

        return $this->success([
            'akun' => new AkunResource($request->user()),
            'lead' => $lead ? new LeadsResource($lead) : null,
            'timeline' => $lead ? $this->timeline($lead) : [],
        ], 'Profile retrieved successfully');
    }

    private function timeline(Leads $lead): array
    {
        $kavlingStatus = $lead->kavling?->status;

        $steps = [
            ['key' => 'pengajuan', 'judul' => 'Form Minat Dikirim', 'deskripsi' => 'Data minat dan kontak Anda sudah kami terima.', 'selesai' => true, 'tanggal' => $lead->created_at],
            ['key' => 'validasi', 'judul' => 'Verifikasi KTP', 'deskripsi' => 'Tim admin memverifikasi data identitas Anda.', 'selesai' => $lead->status_keseriusan === LeadStatus::Validated, 'tanggal' => null],
            ['key' => 'marketing', 'judul' => 'Pendampingan Marketing', 'deskripsi' => 'Marketing ditugaskan untuk survey lokasi dan konsultasi.', 'selesai' => $lead->id_marketing !== null, 'tanggal' => null],
            ['key' => 'booking', 'judul' => 'Booking Unit', 'deskripsi' => 'Pembayaran tanda jadi dan pemilihan kavling.', 'selesai' => in_array($kavlingStatus, [KavlingStatus::Booking, KavlingStatus::Terjual], true), 'tanggal' => null],
            ['key' => 'terjual', 'judul' => 'Akad & Serah Terima', 'deskripsi' => 'Akad kredit/pelunasan dan serah terima kunci.', 'selesai' => $kavlingStatus === KavlingStatus::Terjual, 'tanggal' => null],
        ];

        $currentAssigned = false;

        return array_map(function (array $step) use (&$currentAssigned) {
            $status = $step['selesai'] ? 'completed' : ($currentAssigned ? 'upcoming' : 'current');
            $currentAssigned = $currentAssigned || ! $step['selesai'];

            return [
                'key' => $step['key'],
                'judul' => $step['judul'],
                'deskripsi' => $step['deskripsi'],
                'status' => $status,
                'tanggal' => $step['tanggal']?->toIso8601String(),
            ];
        }, $steps);
    }
}
