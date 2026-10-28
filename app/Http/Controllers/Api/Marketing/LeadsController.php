<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeadsController extends Controller
{
    use ApiResponse;

    private const RELATIONS = ['perumahan', 'tipeRumah', 'akunLeads', 'kavling'];

    /**
     * Leads the admin assigned to this marketing, including pending ones
     * that still wait for a KTP.
     */
    public function index(Request $request)
    {
        $leads = Leads::with(self::RELATIONS)
            ->where('id_marketing', $request->user()->id)
            ->latest('updated_at')
            ->get();

        return $this->success(LeadsResource::collection($leads), 'Assigned leads retrieved successfully');
    }

    public function show(Request $request, string $id)
    {
        $leads = $this->findOwned($request, $id, [...self::RELATIONS, 'reports']);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        return $this->success(new LeadsResource($leads), 'Lead detail retrieved successfully');
    }

    /**
     * Marketing keeps its own follow-up state on an assigned lead.
     */
    public function update(Request $request, string $id)
    {
        $leads = $this->findOwned($request, $id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        $validated = $request->validate([
            'prioritas' => ['sometimes', 'nullable', Rule::enum(LeadPriority::class)],
            'catatan' => 'sometimes|nullable|string|max:2000',
        ]);

        $leads->update($validated);

        return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'Lead updated successfully');
    }

    /**
     * KTP is collected by the assigned marketing, not by the lead or admin.
     * Admins validate the lead once the KTP is in.
     */
    public function uploadKtp(Request $request, string $id)
    {
        $leads = $this->findOwned($request, $id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        if ($leads->status_keseriusan === LeadStatus::Rejected) {
            return $this->error('Lead sudah ditolak, KTP tidak dapat diunggah.', 422);
        }

        $request->validate([
            'file_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $old = $leads->file_ktp;
        $leads->update(['file_ktp' => $request->file('file_ktp')->store('ktp', 'local')]);

        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'KTP uploaded successfully');
    }

    private function findOwned(Request $request, string $id, array $with = []): ?Leads
    {
        return Leads::with($with)->where('id_marketing', $request->user()->id)->find($id);
    }
}
