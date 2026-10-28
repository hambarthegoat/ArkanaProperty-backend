<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Exceptions\LeadValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Models\Akun;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeadsController extends Controller
{
    use ApiResponse;

    private const RELATIONS = ['perumahan', 'tipeRumah', 'akunLeads', 'kavling'];

    /**
     * Own leads plus the queue of new (pending, unclaimed) leads, so
     * marketing can pick one up by collecting and uploading its KTP.
     */
    public function index(Request $request)
    {
        $leads = Leads::with(self::RELATIONS)
            ->where(fn ($q) => $q
                ->where('id_marketing', $request->user()->id)
                ->orWhere(fn ($q) => $q->whereNull('id_marketing')->where('status_keseriusan', LeadStatus::Pending)))
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
     * KTP is collected by marketing, not by the lead. Uploading to a lead
     * from the queue claims it; admins then validate it.
     */
    public function uploadKtp(Request $request, string $id)
    {
        $request->validate([
            'file_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $marketing = $request->user();
        $path = $request->file('file_ktp')->store('ktp', 'local');

        try {
            $result = DB::transaction(function () use ($id, $marketing, $path) {
                $leads = Leads::lockForUpdate()->find($id);

                if (! $leads || ! $this->canUploadKtp($leads, $marketing)) {
                    return null;
                }

                if ($leads->status_keseriusan === LeadStatus::Rejected) {
                    throw new LeadValidationException('Lead sudah ditolak, KTP tidak dapat diunggah.');
                }

                $old = $leads->file_ktp;
                $leads->update([
                    'file_ktp' => $path,
                    'id_marketing' => $leads->id_marketing ?? $marketing->id,
                ]);

                return [$leads, $old];
            });
        } catch (LeadValidationException $e) {
            Storage::disk('local')->delete($path);

            return $this->error($e->getMessage(), 422);
        }

        if (! $result) {
            Storage::disk('local')->delete($path);

            return $this->error('Lead not found', 404);
        }

        [$leads, $old] = $result;

        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'KTP uploaded successfully');
    }

    private function canUploadKtp(Leads $leads, Akun $marketing): bool
    {
        if ($leads->id_marketing === null) {
            return $leads->status_keseriusan === LeadStatus::Pending;
        }

        return (int) $leads->id_marketing === (int) $marketing->id;
    }

    private function findOwned(Request $request, string $id, array $with = []): ?Leads
    {
        return Leads::with($with)->where('id_marketing', $request->user()->id)->find($id);
    }
}
