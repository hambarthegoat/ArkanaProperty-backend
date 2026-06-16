<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $akun = Akun::where('username', $request->username)->first();

        if (! $akun || ! Hash::check($request->password, $akun->password)) {
            return $this->error('Invalid username or password', 401);
        }

        $token = $akun->createToken('auth_token')->plainTextToken;

        return $this->success([
            'akun' => [
                'id' => $akun->id,
                'username' => $akun->username,
                'role' => $akun->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful');
    }
}
