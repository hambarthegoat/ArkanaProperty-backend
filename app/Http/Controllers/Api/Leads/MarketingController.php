<?php

namespace App\Http\Controllers\Api\Leads;

use App\Http\Controllers\Controller;
use App\Models\Leads;
use App\Support\Phone;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    use ApiResponse;

    public function contact(Request $request)
    {
        $leads = Leads::with('marketing')
            ->where('id_akun_leads', $request->user()->id)
            ->latest()
            ->first();

        if (! $leads || ! $leads->marketing) {
            return $this->error('No marketing has been assigned to your account yet', 404);
        }

        $noWa = Phone::toWhatsApp($leads->marketing->no_telp);

        return $this->success([
            'nama' => $leads->marketing->nama ?? $leads->marketing->username,
            'no_wa' => $leads->marketing->no_telp,
            'wa_link' => $noWa ? 'https://wa.me/'.$noWa : null,
        ], 'Marketing contact retrieved successfully');
    }
}
