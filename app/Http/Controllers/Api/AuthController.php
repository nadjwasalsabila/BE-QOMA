<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Register — opsional, bisa dipakai owner/super_admin buat tambah user
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'email'    => 'nullable|email',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'id'       => Str::uuid(),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email'    => $request->email,
            'role_id'  => $request->role_id,
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat',
            'user'    => $user->load('role'),
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'role'     => $user->role->name,
            ],
        ]);
    }

    // Logout
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logout berhasil']);
    }

    // Cek user yang sedang login
    public function me()
    {
        $user = auth()->user()->load('role');

        return response()->json([
            'id'       => $user->id,
            'username' => $user->username,
            'role'     => $user->role->name,
        ]);
    }
}