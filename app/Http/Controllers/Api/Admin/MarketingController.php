<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AkunResource;
use App\Models\Akun;
use App\Support\Phone;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $marketing = Akun::where('role', 'marketing')->orderBy('nama')->get();

        return $this->success(AkunResource::collection($marketing), 'Marketing accounts retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:tb_akun,username',
            'password' => 'required|string|min:6',
            'nama' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
        ]);

        $marketing = Akun::create([
            ...$validated,
            'no_telp' => isset($validated['no_telp']) ? Phone::normalize($validated['no_telp']) : null,
            'role' => 'marketing',
        ]);

        return $this->success(new AkunResource($marketing), 'Marketing account created successfully', 201);
    }
}
