<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AkunResource;
use App\Models\Akun;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $akun = Akun::where('username', $request->username)->first();

        if (! $akun || ! Hash::check($request->password, $akun->password)) {
            return $this->error('Invalid username or password', 401);
        }

        $token = $akun->createToken('auth_token')->plainTextToken;

        return $this->success([
            'akun' => new AkunResource($akun),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function me(Request $request)
    {
        return $this->success(new AkunResource($request->user()), 'Authenticated account');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful');
    }
}
