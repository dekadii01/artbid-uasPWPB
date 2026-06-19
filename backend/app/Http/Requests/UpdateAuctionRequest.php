<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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

            // ── Pengaturan Lelang ─────────────────────────────────────
            'starting_price'  => ['required', 'numeric', 'min:1'],
            'bid_increment'   => ['required', 'numeric', 'min:1'],
            'buy_now_price'   => ['nullable', 'numeric', 'gt:starting_price'],

            // ── Jadwal ───────────────────────────────────────────────
            'starts_at'   => ['required', 'date', 'after_or_equal:starts_at_threshold'],
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

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->start_date && $this->start_time) {
            $merge['starts_at'] = $this->start_date . ' ' . $this->start_time . ':00';
        }

        if ($this->end_date && $this->end_time) {
            $merge['ends_at'] = $this->end_date . ' ' . $this->end_time . ':00';
        }

        $merge['starts_at_threshold'] = now()->subMinutes(5)->format('Y-m-d H:i:s');

        $this->merge($merge);
    }
}
