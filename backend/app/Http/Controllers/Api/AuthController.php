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
     * @group Authentication
     *
     * Register a new user.
     *
     * @bodyParam firstName string required Nama depan user. Example: Budi
     * @bodyParam lastName string required Nama belakang user. Example: Setiawan
     * @bodyParam email string required Email user (harus unik). Example: budi.setiawan@example.com
     * @bodyParam phone string required Nomor telepon. Example: 08123456789
     * @bodyParam password string required Password (minimal 8 karakter). Example: rahasia123
     * @bodyParam password_confirmation string required Konfirmasi password. Example: rahasia123
     *
     * @response 201 {
     *   "message": "Registrasi berhasil.",
     *   "user": {
     *     "id": 1,
     *     "first_name": "Budi",
     *     "last_name": "Setiawan",
     *     "email": "budi.setiawan@example.com",
     *     "phone": "08123456789",
     *     "role": "user",
     *     "status": "active",
     *     "created_at": "2026-06-20T13:53:12.000000Z",
     *     "updated_at": "2026-06-20T13:53:12.000000Z"
     *   },
     *   "token": "1|abcdef123456..."
     * }
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
     * @group Authentication
     *
     * Authenticate user and issue a Sanctum token.
     *
     * @bodyParam email string required Email user. Example: budi.setiawan@example.com
     * @bodyParam password string required Password. Example: rahasia123
     *
     * @response 200 {
     *   "message": "Login berhasil.",
     *   "user": {
     *     "id": 1,
     *     "first_name": "Budi",
     *     "last_name": "Setiawan",
     *     "email": "budi.setiawan@example.com",
     *     "phone": "08123456789",
     *     "role": "user",
     *     "status": "active",
     *     "created_at": "2026-06-20T13:53:12.000000Z",
     *     "updated_at": "2026-06-20T13:53:12.000000Z"
     *   },
     *   "token": "2|ghijk789012..."
     * }
     * @response 401 {
     *   "message": "Email atau password salah."
     * }
     * @response 403 {
     *   "message": "Akun Anda telah diblokir oleh administrator."
     * }
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
     * @group Authentication
     * @authenticated
     *
     * Revoke the current token (logout).
     *
     * @response 200 {
     *   "message": "Logout berhasil."
     * }
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
     * @group Authentication
     *
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * @group Authentication
     *
     * Obtain the user information from Google and login/register.
     */
    public function handleGoogleCallback()
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $nameParts = explode(' ', $googleUser->getName(), 2);
            $firstName = $nameParts[0] ?? $googleUser->getName();
            $lastName  = $nameParts[1] ?? '';
            
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
                return redirect($frontendUrl . '/login?error=blocked');
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect($frontendUrl . '/auth/google/callback?token=' . $token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect($frontendUrl . '/login?error=oauth_failed');
        }
    }
}
