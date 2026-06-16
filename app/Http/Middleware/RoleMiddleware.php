<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $akun = $request->user();

        if (! $akun || ! in_array($akun->role, $roles, true)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Forbidden. You do not have access to this resource.',
            ], 403);
        }

        return $next($request);
    }
}
