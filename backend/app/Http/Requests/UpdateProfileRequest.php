<?php

namespace App\Http\Requests;

// Mengimpor FormRequest bawaan Laravel untuk menangani validasi request HTTP
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan melakukan request ini.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        // Mengembalikan nilai true karena route ini sudah dibatasi oleh middleware 'auth:sanctum'.
        // Siapapun yang terautentikasi (login) diperbolehkan mengirimkan request ini.
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Mendapatkan ID user yang sedang login saat ini agar kita bisa mengabaikan email miliknya
        // saat melakukan pengecekan keunikan (unique) email di database.
        $userId = $this->user()->id;

        return [
            // 'sometimes' berarti field ini hanya divalidasi jika ada dalam request body.
            // 'required' menjamin jika field dikirim, isinya tidak boleh kosong.
            // 'string' dan 'max:255' membatasi tipe data dan panjang karakter maksimal nama depan.
            'firstName' => ['sometimes', 'required', 'string', 'max:255'],
            
            // Aturan yang sama seperti nama depan, divalidasi jika disertakan.
            'lastName'  => ['sometimes', 'required', 'string', 'max:255'],
            
            // Email harus berupa email valid, maksimal 255 karakter.
            // unique:users,email,' . $userId memberitahu Laravel untuk mengecek kolom 'email' di tabel 'users',
            // tetapi kecualikan baris milik user yang sedang login agar dia bisa menyimpan email lamanya tanpa error keunikan.
            'email'     => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            
            // Nomor telepon dibatasi maksimal 30 karakter.
            'phone'     => ['sometimes', 'required', 'string', 'max:30'],
            
            // 'nullable' memperbolehkan password bernilai kosong/null jika user tidak ingin mengubah password.
            // 'min:8' mengharuskan password baru minimal 8 karakter.
            // 'confirmed' mengharuskan request menyertakan field 'password_confirmation' dengan nilai yang sama persis.
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            
            // 'avatar' harus berupa file gambar dengan format jpeg, png, jpg, atau gif.
            // 'max:2048' membatasi ukuran file maksimal sebesar 2048 Kilobytes (2 Megabytes).
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // 'role' hanya menerima nilai 'admin' atau 'user'.
            // Field ini divalidasi di sini, dan hak akses pengubahannya akan difilter di dalam controller.
            'role'      => ['sometimes', 'string', 'in:admin,user'],
        ];
    }

    /**
     * Kustomisasi pesan kesalahan (error messages) dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'firstName.required' => 'Nama depan wajib diisi jika disertakan.',
            'firstName.string'   => 'Nama depan harus berupa teks.',
            'firstName.max'      => 'Nama depan tidak boleh lebih dari 255 karakter.',
            
            'lastName.required'  => 'Nama belakang wajib diisi jika disertakan.',
            'lastName.string'    => 'Nama belakang harus berupa teks.',
            'lastName.max'       => 'Nama belakang tidak boleh lebih dari 255 karakter.',
            
            'email.required'     => 'Email wajib diisi jika disertakan.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan oleh pengguna lain.',
            
            'phone.required'     => 'Nomor telepon wajib diisi jika disertakan.',
            'phone.max'          => 'Nomor telepon tidak boleh lebih dari 30 karakter.',
            
            'password.min'       => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            
            'avatar.image'       => 'Berkas avatar harus berupa gambar.',
            'avatar.mimes'       => 'Format gambar avatar harus jpeg, png, jpg, atau gif.',
            'avatar.max'         => 'Ukuran gambar avatar tidak boleh lebih dari 2 MB.',
            
            'role.in'            => 'Role hanya boleh berisi admin atau user.',
        ];
    }
}
