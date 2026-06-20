<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
/**
 * Controller CategoryController
 * 
 * Mengelola API endpoint untuk CRUD kategori lelang.
 */
class CategoryController extends Controller
{
    //
    /**
     * @group Categories
     *
     * Menampilkan daftar semua kategori lelang (Publik).
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "name": "Lukisan",
     *     "slug": "lukisan",
     *     "description": "Koleksi lukisan seni Bali dan modern",
     *     "icon": "M4 6a2 2 0 012-2...",
     *     "image": "http://localhost:8000/storage/categories/lukisan.png",
     *     "totalAuctions": 125,
     *     "totalBids": 2350,
     *     "totalSellers": 58,
     *     "createdAt": "20 Jun 2026",
     *     "updatedAt": "20 Jun 2026",
     *     "recentItems": [
     *       {
     *         "name": "Lukisan Bali Klasik",
     *         "seller": "Ketut Wirawan",
     *         "status": "active",
     *         "price": 12500000
     *       }
     *     ]
     *   }
     * ]
     */
    public function index(Request $request): JsonResponse
    {
        // Ambil semua kategori dari database
        $categories = Category::all();
        // Cek apakah kolom 'category_id' sudah ada di tabel 'auctions' (untuk antisipasi di masa depan)
        $hasRelation = Schema::hasColumn('auctions', 'category_id');
        // Transformasikan data agar sesuai dengan kebutuhan frontend
        $data = $categories->map(function (Category $category) use ($hasRelation) {
            $totalAuctions = 0;
            $totalBids = 0;
            $totalSellers = 0;
            $recentItems = [];
            // Jika relasi tabel sudah terhubung, hitung data secara dinamis
            if ($hasRelation) {
                $totalAuctions = $category->auctions()->count();
                
                // Hitung total bid dari semua lelang dalam kategori ini
                $totalBids = $category->auctions()
                    ->join('bids', 'auctions.id', '=', 'bids.auction_id')
                    ->count();
                // Hitung jumlah penjual unik dalam kategori ini
                $totalSellers = $category->auctions()
                    ->distinct('seller_id')
                    ->count('seller_id');
                // Ambil 3 lelang terbaru dalam kategori ini
                $recentItems = $category->auctions()
                    ->with(['seller', 'mainImage'])
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(function ($auction) {
                        return [
                            'name'   => $auction->title,
                            'seller' => $auction->seller ? $auction->seller->full_name : 'Anonim',
                            'status' => $auction->status === 'active' ? 'active' : 'ended',
                            'price'  => (float) $auction->current_price,
                        ];
                    })
                    ->toArray();
            } else {
                // Mocking data statis bawaan awal jika relasi database belum diaktifkan
                // agar tampilan frontend tetap bekerja dengan baik
                if ($category->name === 'Lukisan') {
                    $totalAuctions = 125; $totalBids = 2350; $totalSellers = 58;
                    $recentItems = [
                        ['name' => 'Lukisan Bali Klasik', 'seller' => 'Ketut Wirawan', 'status' => 'active', 'price' => 12500000],
                        ['name' => 'Harmoni Semesta', 'seller' => 'Made Ayu Sari', 'status' => 'ended', 'price' => 9800000],
                    ];
                } elseif ($category->name === 'Patung') {
                    $totalAuctions = 87; $totalBids = 890; $totalSellers = 34;
                    $recentItems = [
                        ['name' => 'Patung Garuda Bali', 'seller' => 'I Putu Arya', 'status' => 'active', 'price' => 15000000],
                    ];
                } elseif ($category->name === 'Topeng Tradisional') {
                    $totalAuctions = 45; $totalBids = 520; $totalSellers = 22;
                    $recentItems = [
                        ['name' => 'Topeng Barong Antik', 'seller' => 'I Putu Arya', 'status' => 'ended', 'price' => 18500000],
                    ];
                } elseif ($category->name === 'Barang Antik') {
                    $totalAuctions = 68; $totalBids = 740; $totalSellers = 29;
                    $recentItems = [
                        ['name' => 'Keris Pusaka Jawa', 'seller' => 'Budi Santoso', 'status' => 'active', 'price' => 22000000],
                    ];
                }
            }
            return [
                'id'            => $category->id,
                'name'          => $category->name,
                'slug'          => $category->slug,
                'description'   => $category->description,
                'icon'          => $category->icon ?: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z',
                'image'         => $category->image_path ? Storage::url($category->image_path) : null,
                'totalAuctions' => $totalAuctions,
                'totalBids'     => $totalBids,
                'totalSellers'  => $totalSellers,
                'createdAt'     => $category->created_at ? $category->created_at->format('d M Y') : '—',
                'updatedAt'     => $category->updated_at ? $category->updated_at->format('d M Y') : '—',
                'recentItems'   => $recentItems,
            ];
        });
        return response()->json($data);
    }
    /**
     * @group Categories
     *
     * Menampilkan satu detail kategori lelang (Publik).
     *
     * @urlParam category integer required ID atau slug kategori. Example: 1
     *
     * @response 200 {
     *   "category": {
     *     "id": 1,
     *     "name": "Lukisan",
     *     "slug": "lukisan",
     *     "description": "Koleksi lukisan seni Bali dan modern",
     *     "icon": "M4 6a2 2 0 012-2...",
     *     "image": "http://localhost:8000/storage/categories/lukisan.png",
     *     "createdAt": "20 Jun 2026",
     *     "updatedAt": "20 Jun 2026"
     *   }
     * }
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'category' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
                'icon'        => $category->icon,
                'image'       => $category->image_path ? Storage::url($category->image_path) : null,
                'createdAt'   => $category->created_at ? $category->created_at->format('d M Y') : '—',
                'updatedAt'   => $category->updated_at ? $category->updated_at->format('d M Y') : '—',
            ]
        ]);
    }
    /**
     * @group Admin Categories
     * @authenticated
     *
     * Menyimpan kategori lelang baru ke database (Hanya Admin).
     *
     * @bodyParam name string required Nama kategori (harus unik). Example: Kerajinan Kayu
     * @bodyParam description string Deskripsi kategori. Example: Aneka seni ukir kayu khas Bali
     * @bodyParam icon string Ikon SVG. Example: M4 6a2 2 0...
     * @bodyParam image file Gambar thumbnail kategori (format jpg, jpeg, png, webp, max 2MB).
     *
     * @response 201 {
     *   "message": "Kategori berhasil ditambahkan.",
     *   "category": {
     *     "id": 5,
     *     "name": "Kerajinan Kayu",
     *     "slug": "kerajinan-kayu",
     *     "description": "Aneka seni ukir kayu khas Bali",
     *     "icon": "M4 6a2 2 0...",
     *     "image_path": "categories/kerajinan-kayu.png",
     *     "created_at": "2026-06-20T13:53:12.000000Z",
     *     "updated_at": "2026-06-20T13:53:12.000000Z"
     *   }
     * }
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Buat slug otomatis dari nama kategori
        $validated['slug'] = Str::slug($validated['name']);
        // Handle upload gambar jika ada
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $validated['image_path'] = $path;
        }
        // Simpan ke database
        $category = Category::create($validated);
        return response()->json([
            'message'  => 'Kategori berhasil ditambahkan.',
            'category' => $category
        ], 201);
    }
    /**
     * @group Admin Categories
     * @authenticated
     *
     * Memperbarui kategori lelang yang sudah ada (Hanya Admin).
     *
     * @urlParam category integer required ID kategori. Example: 1
     * @bodyParam name string required Nama kategori (harus unik). Example: Lukisan Bali
     * @bodyParam description string Deskripsi kategori. Example: Lukisan khas Bali dan sekitarnya
     * @bodyParam icon string Ikon SVG. Example: M4 6a2 2 0...
     * @bodyParam image file Gambar thumbnail kategori baru (format jpg, jpeg, png, webp, max 2MB).
     *
     * @response 200 {
     *   "message": "Kategori berhasil diperbarui.",
     *   "category": {
     *     "id": 1,
     *     "name": "Lukisan Bali",
     *     "slug": "lukisan-bali",
     *     "description": "Lukisan khas Bali dan sekitarnya",
     *     "icon": "M4 6a2 2 0...",
     *     "image_path": "categories/lukisan-bali.png",
     *     "created_at": "2026-06-20T13:53:12.000000Z",
     *     "updated_at": "2026-06-20T13:53:12.000000Z"
     *   }
     * }
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $validated = $request->validated();
        
        // Perbarui slug berdasarkan nama kategori yang baru
        $validated['slug'] = Str::slug($validated['name']);
        // Handle upload gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $path = $request->file('image')->store('categories', 'public');
            $validated['image_path'] = $path;
        }
        // Update di database
        $category->update($validated);
        return response()->json([
            'message'  => 'Kategori berhasil diperbarui.',
            'category' => $category
        ]);
    }
    /**
     * @group Admin Categories
     * @authenticated
     *
     * Menghapus kategori lelang dari database (Hanya Admin).
     *
     * @urlParam category integer required ID kategori. Example: 1
     *
     * @response 200 {
     *   "message": "Kategori berhasil dihapus."
     * }
     * @response 400 {
     *   "message": "Kategori tidak dapat dihapus karena sedang digunakan oleh 5 barang lelang."
     * }
     */
    public function destroy(Category $category): JsonResponse
    {
        // Cek jika kolom category_id sudah ada di tabel auctions
        $hasRelation = Schema::hasColumn('auctions', 'category_id');
        
        if ($hasRelation) {
            // Cek apakah kategori ini masih digunakan oleh barang lelang apa pun
            $auctionCount = $category->auctions()->count();
            if ($auctionCount > 0) {
                return response()->json([
                    'message' => "Kategori tidak dapat dihapus karena sedang digunakan oleh {$auctionCount} barang lelang."
                ], 400);
            }
        }
        // Hapus gambar dari storage disk jika ada
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }
        // Hapus kategori dari database
        $category->delete();
        return response()->json([
            'message' => 'Kategori berhasil dihapus.'
        ]);
    }
}
