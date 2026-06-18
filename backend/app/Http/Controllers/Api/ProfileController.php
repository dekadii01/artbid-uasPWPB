<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// Mengimpor UpdateProfileRequest yang mengurus validasi input request
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// Mengimpor Hash untuk mengenkripsi password baru
use Illuminate\Support\Facades\Hash;
// Mengimpor Storage untuk mengelola penyimpanan berkas/avatar
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Ambil data profil user yang sedang login.
     *
     * GET /api/profile
     * Requires: auth:sanctum
     */
    public function show(Request $request): JsonResponse
    {
        // Mengembalikan data user yang sedang login saat ini (diambil dari token Sanctum) dalam format JSON
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update data profil user yang sedang login (bisa dipanggil oleh admin maupun user biasa).
     *
     * PUT /api/profile
     * Requires: auth:sanctum
     * 
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        // Mendapatkan instance user yang sedang login (terautentikasi via token Sanctum)
        $user = $request->user();

        // 1. Mengubah nama depan jika dikirimkan di dalam request body (firstName)
        if ($request->has('firstName')) {
            $user->first_name = $request->firstName;
        }

        // 2. Mengubah nama belakang jika dikirimkan di dalam request body (lastName)
        if ($request->has('lastName')) {
            $user->last_name = $request->lastName;
        }

        // 3. Mengubah email jika dikirimkan di dalam request body (email)
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // 4. Mengubah nomor telepon jika dikirimkan di dalam request body (phone)
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        // 5. Mengubah password jika diisi (filled() mengecek parameter ada dan tidak kosong)
        if ($request->filled('password')) {
            // Mengenkripsi password baru menggunakan bcrypt/argon melalui Hash::make sebelum disimpan
            $user->password = Hash::make($request->password);
        }

        // 6. Mengubah role (Hanya untuk Admin)
        if ($request->has('role')) {
            // Memeriksa apakah user yang sedang login memiliki hak akses admin.
            // isAdmin() didefinisikan sebagai helper di Model User (memeriksa jika role === 'admin').
            if ($user->isAdmin()) {
                $user->role = $request->role;
            }
            // Jika user biasa mengirim request parameter role, parameter tersebut diabaikan demi keamanan (Privilege Escalation Protection).
        }

        // 7. Menangani upload file foto profil / avatar baru
        if ($request->hasFile('avatar')) {
            // Jika user sebelumnya sudah memiliki foto avatar, hapus file lama tersebut agar tidak memenuhi storage
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Menyimpan file avatar ke folder 'avatars' di storage disk public (storage/app/public/avatars)
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // Menyimpan path file baru tersebut ke kolom database 'avatar'
            $user->avatar = $path;
        }

        // Menyimpan semua perubahan atribut di atas ke tabel 'users' di database
        $user->save();

        // Mengembalikan respons sukses berisi pesan konfirmasi dan data user terbaru
        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $user,
        ]);
    }
}
