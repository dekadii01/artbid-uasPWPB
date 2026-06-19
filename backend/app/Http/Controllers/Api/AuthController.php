<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->firstName,
            'last_name'  => $request->lastName,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    /**
     * Authenticate user and issue a Sanctum token.
     *
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Cek kredensial — Auth::attempt() tidak dipakai agar tidak bergantung
        // pada session; kita query langsung dan verifikasi hash secara manual.
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if ($user->status === 'blocked') {
            return response()->json([
                'message' => 'Akun Anda telah diblokir oleh administrator.',
            ], 403);
        }

        // Hapus semua token lama agar tidak menumpuk (opsional, bisa
        // dihapus jika ingin mendukung multi-device login).
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    /**
     * Revoke the current token (logout).
     *
     * POST /api/auth/logout
     * Requires: auth:sanctum
     */
    public function logout(Request $request): JsonResponse
    {
        // Hanya cabut token yang sedang dipakai, bukan semua token milik user.
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
