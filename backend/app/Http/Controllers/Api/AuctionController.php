<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Models\Auction;
use App\Models\AuctionImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuctionController extends Controller
{
    /**
     * Daftar lelang aktif (publik, tidak perlu login).
     */
    public function index(): JsonResponse
    {
        $auctions = Auction::with(['seller', 'mainImage'])
            ->active()
            ->latest()
            ->paginate(12);

        return response()->json($auctions);
    }

    /**
     * Buat lelang baru beserta upload foto.
     * Disk penyimpanan ditentukan oleh FILESYSTEM_DISK di .env (harus 'r2').
     */
    public function store(StoreAuctionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // PENTING: pastikan FILESYSTEM_DISK=r2 di .env, baru disk ini
        // benar-benar mengarah ke bucket R2. Kalau masih 'local', foto
        // akan tersimpan di server, bukan di R2.
        $disk = config('filesystems.default');

        $auction = DB::transaction(function () use ($validated, $request, $disk) {

            // ── Simpan data lelang ──────────────────────────────────
            $auction = Auction::create([
                'seller_id'      => $request->user()->id,
                'title'          => $validated['title'],
                'description'    => $validated['description'],
                'starting_price' => $validated['starting_price'],
                'current_price'  => $validated['starting_price'],
                'bid_increment'  => $validated['bid_increment'],
                'buy_now_price'  => $validated['buy_now_price'] ?? null,
                'status'         => 'scheduled',
                'starts_at'      => $validated['starts_at'],
                'ends_at'        => $validated['ends_at'],
            ]);

            // ── Upload foto utama (sort_order = 0) ──────────────────
            $this->storeImage($request->file('main_photo'), $auction, $disk, 0);

            // ── Upload foto tambahan (sort_order = 1, 2, 3, ...) ────
            if ($request->hasFile('extra_photos')) {
                foreach ($request->file('extra_photos') as $index => $file) {
                    $this->storeImage($file, $auction, $disk, $index + 1);
                }
            }

            return $auction;
        });

        $auction->load('images');

        return response()->json([
            'message' => 'Lelang berhasil dibuat.',
            'auction' => $auction,
        ], 201);
    }

    /**
     * Detail satu lelang (publik).
     */
    public function show(Auction $auction): JsonResponse
    {
        $auction->load(['seller', 'images', 'bids.bidder']);

        return response()->json(['auction' => $auction]);
    }

    /**
     * Lelang milik seller yang sedang login.
     */
    public function myAuctions(): JsonResponse
    {
        $auctions = auth()->user()
            ->auctions()
            ->with(['mainImage', 'winner.winner'])
            ->latest()
            ->paginate(12);

        return response()->json($auctions);
    }

    /**
     * Helper: upload satu file foto ke disk aktif dan simpan record-nya.
     *
     * PERBAIKAN dari versi sebelumnya:
     * 1. Pakai $file->get() bukan file_get_contents($file) — method
     *    bawaan UploadedFile, lebih aman & efisien.
     * 2. Tambah ['visibility' => 'public'] — WAJIB untuk R2, supaya file
     *    bisa diakses lewat URL publik (r2.dev / custom domain). Tanpa ini,
     *    file ter-upload tapi browser akan dapat 403 saat mengakses URL-nya.
     */
    private function storeImage($file, Auction $auction, string $disk, int $sortOrder): AuctionImage
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "auctions/{$auction->id}/{$filename}";

        Storage::disk($disk)->put($path, $file->get(), [
            'visibility' => 'public',
        ]);

        return AuctionImage::create([
            'auction_id'   => $auction->id,
            'image_path'   => $path,
            'storage_disk' => $disk,
            'sort_order'   => $sortOrder,
        ]);
    }
}
