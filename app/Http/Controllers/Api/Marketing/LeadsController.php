<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Enums\LeadPriority;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeadsResource;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadsController extends Controller
{
    use ApiResponse;

    private const RELATIONS = ['perumahan', 'tipeRumah', 'akunLeads', 'kavling'];

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

    private function findOwned(Request $request, string $id, array $with = []): ?Leads
    {
        return Leads::with($with)->where('id_marketing', $request->user()->id)->find($id);
    }
}
