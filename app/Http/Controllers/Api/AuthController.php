<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\HasPagination;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class UsahaController extends Controller
{
    use HasPagination;

    public function index(Request $request)
    {
        $usahas = Usaha::where('owner_id', auth()->id())
                       ->withCount('tenants')
                       ->paginate($this->getPerPage($request));

        return response()->json(
            $this->paginateResponse($usahas, 'Daftar usaha')
        );
    }
}

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

        $user  = User::where('username', $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Username atau password salah'], 401);
    }

    // ✅ Cek apakah akun aktif
    if (!$user->is_active) {
        return response()->json(['message' => 'Akun Anda telah dinonaktifkan. Hubungi admin.'], 403);
    }

    $token = JWTAuth::fromUser($user);

    return response()->json([
        'message'      => 'Login berhasil',
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => config('jwt.ttl') * 60, // detik, contoh: 3600 = 1 jam
        'user'         => [
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

    /**
 * Refresh access token
 *
 * Client kirim token LAMA (yang sudah expired) → dapat token BARU
 * Token lama otomatis di-blacklist
 *
 * Cara hit: POST /api/auth/refresh
 * Header: Authorization: Bearer {token_lama_yang_expired}
 */
public function refresh()
{
    try {
        $newToken = JWTAuth::parseToken()->refresh();

        return response()->json([
            'message'      => 'Token berhasil direfresh',
            'access_token' => $newToken,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60, // dalam detik
        ]);
    } catch (TokenExpiredException $e) {
        // Refresh token juga sudah expired (lewat 7 hari)
        return response()->json([
            'message' => 'Refresh token sudah expired. Silakan login ulang.',
        ], 401);
    } catch (TokenInvalidException $e) {
        return response()->json([
            'message' => 'Token tidak valid.',
        ], 401);
    }
}
}