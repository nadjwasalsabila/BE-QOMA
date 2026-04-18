<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token tidak valid atau sudah expired'], 401);
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role->name ?? null;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Akses ditolak. Role kamu: ' . $userRole . '. Role yang diizinkan: ' . implode(', ', $roles),
            ], 403);
        }

        return $next($request);
    }
}