<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuctionImageController extends Controller
{
    /**
     * @group Auction Images
     * @authenticated
     *
     * Tambah foto baru ke lelang yang sudah ada (Seller).
     *
     * @urlParam auction integer required ID lelang. Example: 10
     * @bodyParam photos file[] required Array file foto tambahan (format jpg, jpeg, png, webp, max 10MB per file).
     *
     * @response 201 {
     *   "message": "Foto berhasil ditambahkan.",
     *   "images": [
     *     {
     *       "id": 16,
     *       "auction_id": 10,
     *       "image_path": "auctions/10/extra1.png",
     *       "storage_disk": "public",
     *       "sort_order": 1,
     *       "url": "http://localhost:8000/storage/auctions/10/extra1.png"
     *     }
     *   ]
     * }
     */
    public function store(Request $request, Auction $auction): JsonResponse
    {
        if ($auction->seller_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Kamu tidak berhak menambah foto pada lelang ini.',
            ], 403);
        }

        if (! $auction->isEditable()) {
            return response()->json([
                'message' => 'Foto hanya bisa ditambah selama lelang belum dimulai.',
            ], 422);
        }

        $request->validate([
            'photos'   => ['required', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $disk = config('filesystems.default');

        $nextOrder = $auction->images()->max('sort_order') + 1;

        $newImages = [];

        foreach ($request->file('photos') as $index => $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = "auctions/{$auction->id}/{$filename}";

            // Sama seperti di AuctionController: pakai $file->get() +
            // visibility public, supaya konsisten dan foto bisa diakses.
            Storage::disk($disk)->put($path, $file->get(), [
                'visibility' => 'public',
            ]);

            $newImages[] = AuctionImage::create([
                'auction_id'   => $auction->id,
                'image_path'   => $path,
                'storage_disk' => $disk,
                'sort_order'   => $nextOrder + $index,
            ]);
        }

        return response()->json([
            'message' => 'Foto berhasil ditambahkan.',
            'images'  => $newImages,
        ], 201);
    }

    /**
     * @group Auction Images
     * @authenticated
     *
     * Hapus satu foto dari lelang (Seller).
     *
     * @urlParam auction integer required ID lelang. Example: 10
     * @urlParam image integer required ID foto lelang. Example: 16
     *
     * @response 200 {
     *   "message": "Foto berhasil dihapus."
     * }
     */
    public function destroy(Request $request, Auction $auction, AuctionImage $image): JsonResponse
    {
        if ($auction->seller_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Kamu tidak berhak menghapus foto ini.',
            ], 403);
        }

        if ($image->auction_id !== $auction->id) {
            return response()->json([
                'message' => 'Foto tidak ditemukan pada lelang ini.',
            ], 404);
        }

        if (! $auction->isEditable()) {
            return response()->json([
                'message' => 'Foto hanya bisa dihapus selama lelang belum dimulai.',
            ], 422);
        }

        Storage::disk($image->storage_disk)->delete($image->image_path);
        $image->delete();

        return response()->json([
            'message' => 'Foto berhasil dihapus.',
        ]);
    }
}
