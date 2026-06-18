<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request StoreCategoryRequest
 * 
 * Digunakan untuk memvalidasi data saat menambahkan kategori lelang baru.
 */
class StoreCategoryRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diotorisasi untuk membuat request ini.
     * Sudah dilindungi oleh middleware auth:sanctum dan role admin di route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'], // Nama wajib diisi, unik di tabel categories
            'description' => ['nullable', 'string', 'max:1000'], // Deskripsi opsional
            'icon'        => ['nullable', 'string'], // Ikon SVG opsional
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Gambar thumbnail opsional (maks 2MB)
        ];
    }

    /**
     * Pesan kesalahan kustom untuk validasi yang gagal.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Nama kategori sudah terdaftar.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
            'image.image'   => 'Berkas harus berupa gambar.',
            'image.mimes'   => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'image.max'     => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
