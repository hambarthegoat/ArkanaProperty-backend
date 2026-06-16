<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $leads = Leads::with(['perumahan', 'akunLeads'])
            ->where('id_marketing', $request->user()->id)
            ->latest()
            ->get();

        return $this->success($leads, 'Assigned leads retrieved successfully');
    }

    public function show(Request $request, string $id)
    {
        $leads = Leads::with(['perumahan', 'akunLeads'])
            ->where('id_marketing', $request->user()->id)
            ->find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        return $this->success($leads, 'Lead detail retrieved successfully');
    }
}
