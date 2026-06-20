<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * @group Admin Users
     * @authenticated
     *
     * Tampilkan daftar seluruh pengguna beserta statistiknya (Admin).
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with([
            'auctions',
            'bids.auction',
            'wins.auction',
            'watchlists',
        ])->get();

        $mappedUsers = $users->map(function ($user) {
            // Determine role display:
            $roleLabel = $user->role === 'admin' ? 'Admin' : 'User';

            // Won auctions count
            $wonCount = $user->wins->count();

            // Watchlist count
            $watchlistCount = $user->watchlists->count();

            // Auction History: listing of auctions created by the user
            $auctionHistory = $user->auctions->map(function ($auc) {
                return [
                    'name'       => $auc->title,
                    'finalPrice' => (float) $auc->current_price,
                    'totalBids'  => $auc->bids()->count(),
                    'status'     => $auc->status,
                ];
            })->values();

            // Bid History: listing of bids placed by the user
            $bidHistory = $user->bids->map(function ($bid) {
                $bidStatus = 'outbid';
                if ($bid->status === 'won') {
                    $bidStatus = 'won';
                } elseif ($bid->status === 'active') {
                    $bidStatus = 'leading';
                }

                return [
                    'name'      => $bid->auction?->title ?? 'Lelang',
                    'amount'    => (float) $bid->amount,
                    'time'      => $bid->created_at->setTimezone('Asia/Makassar')->format('d M Y'),
                    'bidStatus' => $bidStatus,
                ];
            })->values();

            // Dynamic Recent Activities
            $activities = [];

            // 1. Auctions created
            foreach ($user->auctions->sortByDesc('created_at')->take(5) as $auc) {
                $activities[] = [
                    'text' => 'Membuat lelang baru "' . $auc->title . '"',
                    'time' => $auc->created_at->diffForHumans(),
                    'timestamp' => $auc->created_at->timestamp,
                    'dark' => false,
                    'icon' => 'M12 4v16m8-8H4',
                ];
            }

            // 2. Bids placed
            foreach ($user->bids->sortByDesc('created_at')->take(5) as $bid) {
                $activities[] = [
                    'text' => 'Melakukan penawaran Rp ' . number_format($bid->amount, 0, ',', '.') . ' pada lelang "' . ($bid->auction?->title ?? 'Lelang') . '"',
                    'time' => $bid->created_at->diffForHumans(),
                    'timestamp' => $bid->created_at->timestamp,
                    'dark' => true,
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ];
            }

            // 3. Wins
            foreach ($user->wins->sortByDesc('created_at')->take(5) as $win) {
                $activities[] = [
                    'text' => 'Memenangkan lelang "' . ($win->auction?->title ?? 'Lelang') . '"',
                    'time' => $win->created_at->diffForHumans(),
                    'timestamp' => $win->created_at->timestamp,
                    'dark' => false,
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                ];
            }

            // Sort all activities by timestamp descending
            usort($activities, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
            // Limit to top 5 recent activities
            $activities = array_slice($activities, 0, 5);

            return [
                'id'             => $user->id,
                'name'           => $user->full_name,
                'email'          => $user->email,
                'phone'          => $user->phone ?? '—',
                'role'           => $roleLabel,
                'joinedAt'       => $user->created_at->setTimezone('Asia/Makassar')->format('d M Y'),
                'status'         => $user->status,
                'totalAuctions'  => $user->auctions->count(),
                'totalBids'      => $user->bids->count(),
                'wonAuctions'    => $wonCount,
                'watchlist'      => $watchlistCount,
                'recentActivity' => $activities,
                'auctionHistory' => $auctionHistory,
                'bidHistory'     => $bidHistory,
            ];
        });

        // Computed totals for dashboard overview cards
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $blockedUsers = User::where('status', 'blocked')->count();
        $newUsersMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        return response()->json([
            'users' => $mappedUsers,
            'stats' => [
                'total'   => $totalUsers,
                'active'  => $activeUsers,
                'blocked' => $blockedUsers,
                'new'     => $newUsersMonth,
            ],
        ]);
    }

    /**
     * @group Admin Users
     * @authenticated
     *
     * Update data profil pengguna lain (Admin).
     *
     * @urlParam user integer required ID user yang akan diupdate. Example: 3
     * @bodyParam firstName string Nama depan. Example: Wayan
     * @bodyParam lastName string Nama belakang. Example: Sudiarta
     * @bodyParam email string Email user. Example: wayan@example.com
     * @bodyParam phone string Nomor telepon. Example: 08123456788
     * @bodyParam password string Password baru. Example: rahasia123
     * @bodyParam role string Role ('admin' atau 'user'). Example: user
     * @bodyParam status string Status ('active' atau 'blocked'). Example: active
     * @bodyParam avatar file File avatar baru (max 2MB).
     *
     * @response 200 {
     *   "message": "Profil pengguna berhasil diperbarui oleh Admin.",
     *   "user": {
     *     "id": 3,
     *     "first_name": "Wayan",
     *     "last_name": "Sudiarta",
     *     "email": "wayan@example.com",
     *     "phone": "08123456788",
     *     "role": "user",
     *     "status": "active"
     *   }
     * }
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
            'role'      => ['sometimes', 'required', 'string', 'in:admin,user'],

            // Status opsional, hanya boleh bernilai 'active' atau 'blocked'
            'status'    => ['sometimes', 'required', 'string', 'in:active,blocked'],
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
            'status.in'          => 'Status harus berupa active atau blocked.',
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

        // 6b. Mengubah status user
        if ($request->has('status')) {
            $user->status = $request->status;
            // Jika diblokir, hapus semua token login aktif
            if ($request->status === 'blocked') {
                $user->tokens()->delete();
            }
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

    /**
     * @group Admin Users
     * @authenticated
     *
     * Tampilkan detail pengguna beserta seluruh riwayat aktivitasnya (Admin).
     *
     * @urlParam user integer required ID user. Example: 3
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'auctions',
            'bids.auction',
            'wins.auction',
            'watchlists.auction.seller',
            'watchlists.auction.category',
        ]);

        // Map role label
        $roleLabel = $user->role === 'admin' ? 'Admin' : 'User';

        // Auction History
        $auctionHistory = $user->auctions->map(function ($auc) {
            return [
                'id'         => $auc->id,
                'name'       => $auc->title,
                'image'      => $auc->primary_image_url ?? ($auc->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=200&q=80'),
                'startPrice' => (float) $auc->starting_price,
                'finalPrice' => (float) $auc->current_price,
                'status'     => $auc->status,
            ];
        })->values();

        // Bid History
        $bidHistory = $user->bids->map(function ($bid) {
            $bidStatus = 'outbid';
            if ($bid->status === 'won') {
                $bidStatus = 'won';
            } elseif ($bid->status === 'active') {
                $bidStatus = 'leading';
            }

            return [
                'id'        => $bid->id,
                'name'      => $bid->auction?->title ?? 'Lelang',
                'amount'    => (float) $bid->amount,
                'status'    => $bidStatus,
                'time'      => $bid->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
            ];
        })->values();

        // Watchlist Items
        $watchlistItems = $user->watchlists->map(function ($wl) {
            $auc = $wl->auction;
            if (!$auc) return null;
            return [
                'id'           => $auc->id,
                'name'         => $auc->title,
                'image'        => $auc->primary_image_url ?? ($auc->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=200&q=80'),
                'category'     => $auc->category?->name ?? 'Kategori',
                'currentPrice' => (float) $auc->current_price,
                'status'       => $auc->status,
            ];
        })->filter()->values();

        // Stats
        $totalBidsVal = (float) $user->bids->sum('amount');
        $totalSalesVal = (float) $user->auctions()->where('status', 'ended')->sum('current_price');
        $wonCount = $user->wins->count();
        $soldCount = $user->auctions()->where('status', 'ended')->whereHas('winner')->count();

        // Dynamic Activities
        $activities = [];
        foreach ($user->auctions->sortByDesc('created_at')->take(5) as $auc) {
            $activities[] = [
                'text' => 'Membuat lelang baru "' . $auc->title . '"',
                'time' => $auc->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $auc->created_at->timestamp,
                'dark' => false,
                'icon' => 'M12 4v16m8-8H4',
            ];
        }
        foreach ($user->bids->sortByDesc('created_at')->take(5) as $bid) {
            $activities[] = [
                'text' => 'Melakukan penawaran Rp ' . number_format($bid->amount, 0, ',', '.') . ' pada lelang "' . ($bid->auction?->title ?? 'Lelang') . '"',
                'time' => $bid->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $bid->created_at->timestamp,
                'dark' => true,
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
        }
        foreach ($user->wins->sortByDesc('created_at')->take(5) as $win) {
            $activities[] = [
                'text' => 'Memenangkan lelang "' . ($win->auction?->title ?? 'Lelang') . '"',
                'time' => $win->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $win->created_at->timestamp,
                'dark' => false,
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
        }
        usort($activities, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
        $activities = array_slice($activities, 0, 5);

        // System Timeline Activities
        $systemActivities = [];
        $systemActivities[] = [
            'text' => 'Akun berhasil dibuat',
            'time' => $user->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
            'timestamp' => $user->created_at->timestamp,
            'dark' => false,
            'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        ];
        $firstAuc = $user->auctions->sortBy('created_at')->first();
        if ($firstAuc) {
            $systemActivities[] = [
                'text' => 'Membuat lelang pertama',
                'time' => $firstAuc->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $firstAuc->created_at->timestamp,
                'dark' => false,
                'icon' => 'M12 4v16m8-8H4',
            ];
        }
        $firstWin = $user->wins->sortBy('created_at')->first();
        if ($firstWin) {
            $systemActivities[] = [
                'text' => 'Memenangkan lelang pertama',
                'time' => $firstWin->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $firstWin->created_at->timestamp,
                'dark' => true,
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
        }
        if ($user->bids->count() >= 50) {
            $fiftyBid = $user->bids->sortBy('created_at')->skip(49)->first();
            $systemActivities[] = [
                'text' => 'Total 50 penawaran tercapai',
                'time' => $fiftyBid->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'timestamp' => $fiftyBid->created_at->timestamp,
                'dark' => false,
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            ];
        }
        usort($systemActivities, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        // Mock Login logs
        $loginHistory = [
            [
                'device' => 'Windows 11',
                'browser' => 'Google Chrome',
                'ip' => '103.152.112.5',
                'time' => $user->updated_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'device' => 'Android',
                'browser' => 'Chrome Mobile',
                'ip' => '103.152.112.18',
                'time' => $user->created_at->addHours(2)->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
            ]
        ];

        // Alerts / Notifications
        $alerts = [];
        $notifications = [];
        if ($user->status === 'blocked') {
            $alerts[] = [
                'text' => 'Akun ini telah dinonaktifkan oleh administrator.',
                'dark' => true,
                'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
            $notifications[] = [
                'text' => 'Akun ini telah dinonaktifkan oleh administrator.',
                'dark' => true,
                'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
        } else {
            $alerts[] = [
                'text' => 'Tidak ditemukan aktivitas mencurigakan pada akun ini.',
                'dark' => false,
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ];
            $notifications[] = [
                'text' => 'Tidak ditemukan aktivitas mencurigakan pada akun ini.',
                'dark' => false,
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ];
            $notifications[] = [
                'text' => 'Pengguna memiliki reputasi baik di komunitas platform.',
                'dark' => false,
                'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            ];
        }

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->full_name,
                'username'        => explode('@', $user->email)[0],
                'email'           => $user->email,
                'phone'           => $user->phone ?? '—',
                'address'         => 'Denpasar, Bali',
                'joinedAt'        => $user->created_at->setTimezone('Asia/Makassar')->format('d M Y'),
                'lastLogin'       => $user->updated_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA',
                'role'            => $roleLabel,
                'rawRole'         => $user->role,
                'status'          => $user->status,
                'totalAuctions'   => $user->auctions->count(),
                'totalBids'       => $user->bids->count(),
                'wonAuctions'     => $wonCount,
                'watchlist'       => $user->watchlists->count(),
                'totalBidVal'     => $totalBidsVal,
                'totalSalesVal'   => $totalSalesVal,
                'ach_won'         => $wonCount,
                'ach_sold'        => $soldCount,
                'ach_bids'        => $user->bids->count(),
                'alerts'          => $alerts,
                'notifications'   => $notifications,
                'recentActivities'=> $activities,
                'auctionHistory'  => $auctionHistory,
                'bidHistory'      => $bidHistory,
                'watchlistItems'  => $watchlistItems,
                'loginHistory'    => $loginHistory,
                'systemActivity'  => $systemActivities,
            ]
        ]);
    }

    /**
     * @group Admin Users
     * @authenticated
     *
     * Hapus akun pengguna secara permanen (Admin).
     *
     * @urlParam user integer required ID user. Example: 3
     *
     * @response 200 {
     *   "message": "Akun pengguna berhasil dihapus secara permanen."
     * }
     * @response 400 {
     *   "message": "Anda tidak dapat menghapus akun Anda sendiri."
     * }
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Akun pengguna berhasil dihapus secara permanen.'
        ]);
    }
}
