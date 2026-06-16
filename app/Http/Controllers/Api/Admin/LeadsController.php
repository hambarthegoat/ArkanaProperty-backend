<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Leads;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LeadsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $leads = Leads::with(['perumahan', 'marketing', 'akunLeads'])->latest()->get();

        return $this->success($leads, 'Leads retrieved successfully');
    }

    public function validateLead(Request $request, string $id)
    {
        $leads = Leads::find($id);

        if (! $leads) {
            return $this->error('Lead not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'is_valid' => 'required|boolean',
            'id_marketing' => 'required_if:is_valid,true|integer|exists:tb_akun,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        if (! $request->boolean('is_valid')) {
            $leads->update(['status_keseriusan' => 'rejected']);

            return $this->success($leads, 'Lead has been marked as rejected');
        }

        $marketing = Akun::where('id', $request->id_marketing)->where('role', 'marketing')->first();

        if (! $marketing) {
            return $this->error('Selected account is not a valid marketing account', 422);
        }

        $leads = DB::transaction(function () use ($leads, $marketing) {
            $username = 'leads_'.Str::slug($leads->nama_calon).'_'.$leads->id;

            $akunLeads = Akun::create([
                'username' => $username,
                'password' => Hash::make($leads->no_telp),
                'role' => 'leads',
            ]);

            $leads->update([
                'id_akun_leads' => $akunLeads->id,
                'id_marketing' => $marketing->id,
                'status_keseriusan' => 'validated',
            ]);

            return $leads->load(['perumahan', 'marketing', 'akunLeads']);
        });

        return $this->success($leads, 'KTP validated, leads account created and marketing assigned successfully');
    }
}
