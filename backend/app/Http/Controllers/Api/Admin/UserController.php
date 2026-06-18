<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
// Mengimpor Model User untuk berinteraksi dengan tabel users
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// Mengimpor Hash untuk mengenkripsi password baru
use Illuminate\Support\Facades\Hash;
// Mengimpor Storage untuk mengelola file avatar
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Update data profil pengguna lain.
     * Hanya dapat diakses oleh user dengan role 'admin'.
     *
     * PUT /api/admin/users/{user}
     * Requires: auth:sanctum, admin (EnsureUserIsAdmin)
     *
     * @param Request $request
     * @param User $user Model Binding otomatis berdasarkan ID user di URL
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Melakukan validasi input secara dinamis untuk user yang diubah
        $request->validate([
            // Nama depan dan nama belakang opsional, jika disertakan harus berupa teks
            'firstName' => ['sometimes', 'required', 'string', 'max:255'],
            'lastName'  => ['sometimes', 'required', 'string', 'max:255'],
            
            // Email harus valid dan unik, kecuali untuk ID user yang sedang diubah (agar tidak error jika email tetap sama)
            'email'     => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            
            // Nomor telepon opsional
            'phone'     => ['sometimes', 'required', 'string', 'max:30'],
            
            // Password opsional, jika diisi minimal 8 karakter
            'password'  => ['nullable', 'string', 'min:8'],
            
            // Avatar opsional, harus berupa gambar valid, ukuran maksimal 2MB
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // Role opsional, hanya boleh bernilai 'admin' atau 'user'
            'role'      => ['sometimes', 'string', 'in:admin,user'],
        ], [
            // Pesan error kustom berbahasa Indonesia
            'firstName.required' => 'Nama depan wajib diisi.',
            'lastName.required'  => 'Nama belakang wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'phone.required'     => 'Nomor telepon wajib diisi.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'avatar.image'       => 'Avatar harus berupa berkas gambar.',
            'avatar.max'         => 'Ukuran avatar maksimal 2MB.',
            'role.in'            => 'Role harus berupa admin atau user.',
        ]);

        // 1. Mengubah nama depan jika dikirimkan
        if ($request->has('firstName')) {
            $user->first_name = $request->firstName;
        }

        // 2. Mengubah nama belakang jika dikirimkan
        if ($request->has('lastName')) {
            $user->last_name = $request->lastName;
        }

        // 3. Mengubah email jika dikirimkan
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // 4. Mengubah nomor telepon jika dikirimkan
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        // 5. Mengubah password jika diisi
        if ($request->filled('password')) {
            // Hash password baru sebelum disimpan ke database
            $user->password = Hash::make($request->password);
        }

        // 6. Mengubah role user (Karena route ini dilindungi middleware 'admin',
        // maka hanya admin yang bisa memicu perubahan role pengguna lain)
        if ($request->has('role')) {
            $user->role = $request->role;
        }

        // 7. Menangani perubahan avatar
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama milik user tersebut jika ada
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Unggah avatar baru ke folder 'avatars' di disk public
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Simpan seluruh perubahan data user ke database
        $user->save();

        // Mengembalikan respons JSON berisi pesan sukses beserta model user terbaru
        return response()->json([
            'message' => 'Profil pengguna berhasil diperbarui oleh Admin.',
            'user'    => $user,
        ]);
    }
}
