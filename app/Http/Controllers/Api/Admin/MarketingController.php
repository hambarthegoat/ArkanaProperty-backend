<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MarketingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $marketing = Akun::where('role', 'marketing')->get();

        return $this->success($marketing, 'Marketing accounts retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:tb_akun,username',
            'password' => 'required|string|min:6',
            'nama' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $marketing = Akun::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'marketing',
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
        ]);

        return $this->success($marketing, 'Marketing account created successfully', 201);
    }
}
