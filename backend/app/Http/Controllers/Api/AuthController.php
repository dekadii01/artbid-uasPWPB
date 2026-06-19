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

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

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

    /**
     * Redirect the user to the Google authentication page.
     *
     * GET /api/auth/google/redirect
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Obtain the user information from Google and login/register.
     *
     * GET /api/auth/google/callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Pisahkan nama depan dan belakang dari nama lengkap Google
            $nameParts = explode(' ', $googleUser->getName(), 2);
            $firstName = $nameParts[0] ?? $googleUser->getName();
            $lastName  = $nameParts[1] ?? '';
            
            // Temukan atau buat user berdasarkan email dari Google.
            // Catatan:
            // - 'phone' wajib string kosong karena kolom NOT NULL di DB
            // - 'password' cukup plain random string karena User model sudah
            //   punya cast 'hashed' yang otomatis hash saat assign
            $user = User::firstOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => '',
                'password'   => Str::random(24),
                'role'       => 'user',
                'status'     => 'active',
            ]);

            if ($user->status === 'blocked') {
                return redirect('http://localhost:5173/login?error=blocked');
            }

            // Hapus token lama & buat token baru
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect ke halaman callback frontend untuk menyimpan token
            return redirect('http://localhost:5173/auth/google/callback?token=' . $token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('http://localhost:5173/login?error=oauth_failed');
        }
    }
}
