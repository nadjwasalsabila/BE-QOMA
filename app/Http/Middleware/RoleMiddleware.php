<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token sudah expired. Gunakan endpoint /api/auth/refresh untuk mendapatkan token baru.',
                'code'    => 'TOKEN_EXPIRED',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token tidak valid.',
                'code'    => 'TOKEN_INVALID',
            ], 401);
        }

        return $next($request);
    }
}