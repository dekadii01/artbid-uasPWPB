<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request UpdateCategoryRequest
 * 
 * Digunakan untuk memvalidasi data saat mengubah/memperbarui kategori lelang yang sudah ada.
 */
class UpdateCategoryRequest extends FormRequest
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
        // Mendapatkan instance kategori dari parameter route untuk mengabaikan ID yang sama saat validasi unik
        $category = $this->route('category');
        $categoryId = is_object($category) ? $category->id : $category;

        return [
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $categoryId], // Unik kecuali untuk kategori itu sendiri
            'description' => ['nullable', 'string', 'max:1000'],
            'icon'        => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
