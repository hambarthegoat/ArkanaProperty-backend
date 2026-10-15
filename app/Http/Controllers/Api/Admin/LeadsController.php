<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\LeadStatus;
use App\Exceptions\LeadValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Models\Akun;
use App\Models\Leads;
use App\Services\LeadValidationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeadsController extends Controller
{
    use ApiResponse;

    private const RELATIONS = ['perumahan', 'tipeRumah', 'marketing', 'akunLeads', 'kavling'];

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(LeadStatus::class)],
            'search' => 'nullable|string|max:100',
        ]);

        $leads = Leads::with(self::RELATIONS)
            ->when($request->filled('status'), fn ($q) => $q->where('status_keseriusan', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->whereLike('nama_calon', '%'.$request->search.'%')
                        ->orWhereLike('no_telp', '%'.$request->search.'%');
                });
            })
            ->latest()
            ->get();

        return $this->success(LeadsResource::collection($leads), 'Leads retrieved successfully');
    }

    public function show(string $id)
    {
        $leads = Leads::with([...self::RELATIONS, 'reports.marketing'])->find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        return $this->success(new LeadsResource($leads), 'Lead detail retrieved successfully');
    }

    /**
     * is_valid=true validates the KTP, creates (or reuses) the leads portal
     * account and assigns marketing; is_valid=false rejects the lead.
     */
    public function validateLead(Request $request, string $id, LeadValidationService $service)
    {
        $leads = Leads::find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        $request->validate([
            'is_valid' => 'required|boolean',
            'id_marketing' => 'required_if:is_valid,true|nullable|integer|exists:tb_akun,id',
        ]);

        try {
            if (! $request->boolean('is_valid')) {
                $service->reject($leads);

                return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'Lead has been marked as rejected');
            }

            $result = $service->validate($leads, Akun::findOrFail($request->id_marketing));
        } catch (LeadValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'lead' => new LeadsResource($result['lead']->load(self::RELATIONS)),
            'kredensial' => $result['kredensial'],
        ], 'KTP validated, leads account ready and marketing assigned successfully');
    }

    public function uploadKtp(Request $request, string $id)
    {
        $leads = Leads::find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
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

    public function downloadKtp(string $id)
    {
        $leads = Leads::find($id);

        if (! $leads || ! $leads->hasKtp() || ! Storage::disk('local')->exists($leads->file_ktp)) {
            return $this->error('KTP not found', 404);
        }

        return Storage::disk('local')->response($leads->file_ktp);
    }
}
