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
use Illuminate\Validation\ValidationException;

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
     * is_valid=true validates the KTP (uploaded by marketing), creates (or
     * reuses) the leads portal account and assigns marketing, defaulting to
     * the marketing that uploaded the KTP; is_valid=false rejects the lead.
     */
    public function validateLead(Request $request, string $id, LeadValidationService $service)
    {
        $leads = Leads::find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        $request->validate([
            'is_valid' => 'required|boolean',
            'id_marketing' => 'nullable|integer|exists:tb_akun,id',
        ]);

        $marketingId = $request->input('id_marketing') ?? $leads->id_marketing;

        if ($request->boolean('is_valid') && $marketingId === null) {
            throw ValidationException::withMessages(['id_marketing' => 'Marketing wajib dipilih.']);
        }

        try {
            if (! $request->boolean('is_valid')) {
                $service->reject($leads);

                return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'Lead has been marked as rejected');
            }

            $result = $service->validate($leads, Akun::findOrFail($marketingId));
        } catch (LeadValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'lead' => new LeadsResource($result['lead']->load(self::RELATIONS)),
            'kredensial' => $result['kredensial'],
        ], 'KTP validated, leads account ready and marketing assigned successfully');
    }

    /**
     * Hand a pending lead to a marketing account. Marketing then uploads the
     * KTP and the admin validates.
     */
    public function assign(Request $request, string $id, LeadValidationService $service)
    {
        $leads = Leads::find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        $request->validate([
            'id_marketing' => 'required|integer|exists:tb_akun,id',
        ]);

        try {
            $service->assign($leads, Akun::findOrFail($request->id_marketing));
        } catch (LeadValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new LeadsResource($leads->load(self::RELATIONS)), 'Lead assigned to marketing successfully');
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
