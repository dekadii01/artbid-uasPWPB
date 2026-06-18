<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dilindungi middleware auth:sanctum di route
    }

    public function rules(): array
    {
        return [
            // ── Informasi Barang ──────────────────────────────────────
            'title'         => ['required', 'string', 'max:255'],
            'category'      => ['required', 'string', 'max:100'],
            'description'   => ['required', 'string', 'min:30'],
            'condition'     => ['required', 'string', 'max:100'],
            'artist'        => ['nullable', 'string', 'max:255'],
            'year'          => ['nullable', 'integer', 'min:1000', 'max:2099'],

            // ── Foto ─────────────────────────────────────────────────
            // main_photo wajib, extra_photos opsional (maks 5 file)
            'main_photo'          => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'extra_photos'        => ['nullable', 'array', 'max:5'],
            'extra_photos.*'      => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],

            // ── Pengaturan Lelang ─────────────────────────────────────
            'starting_price'  => ['required', 'numeric', 'min:1'],
            'bid_increment'   => ['required', 'numeric', 'min:1'],
            'buy_now_price'   => ['nullable', 'numeric', 'gt:starting_price'],

            // ── Jadwal ───────────────────────────────────────────────
            'starts_at'   => ['required', 'date', 'after_or_equal:now'],
            'ends_at'     => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'Nama barang wajib diisi.',
            'category.required'       => 'Kategori wajib dipilih.',
            'description.required'    => 'Deskripsi wajib diisi.',
            'description.min'         => 'Deskripsi minimal 30 karakter.',
            'condition.required'      => 'Kondisi barang wajib dipilih.',
            'main_photo.required'     => 'Foto utama wajib diunggah.',
            'main_photo.image'        => 'File harus berupa gambar.',
            'main_photo.max'          => 'Ukuran foto maksimal 10MB.',
            'extra_photos.max'        => 'Maksimal 5 foto tambahan.',
            'starting_price.required' => 'Harga awal wajib diisi.',
            'starting_price.min'      => 'Harga awal harus lebih dari 0.',
            'bid_increment.required'  => 'Kelipatan minimum wajib diisi.',
            'buy_now_price.gt'        => 'Harga beli sekarang harus lebih tinggi dari harga awal.',
            'starts_at.required'      => 'Waktu mulai wajib diisi.',
            'starts_at.after_or_equal' => 'Waktu mulai tidak boleh di masa lalu.',
            'ends_at.required'        => 'Waktu berakhir wajib diisi.',
            'ends_at.after'           => 'Waktu berakhir harus setelah waktu mulai.',
        ];
    }

    /**
     * Gabungkan startDate + startTime dan endDate + endTime dari frontend
     * menjadi format datetime sebelum validasi dijalankan.
     *
     * Frontend mengirim: start_date, start_time, end_date, end_time (terpisah)
     * Kita butuh: starts_at, ends_at (datetime gabungan)
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->start_date && $this->start_time) {
            $merge['starts_at'] = $this->start_date . ' ' . $this->start_time . ':00';
        }

        if ($this->end_date && $this->end_time) {
            $merge['ends_at'] = $this->end_date . ' ' . $this->end_time . ':00';
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
