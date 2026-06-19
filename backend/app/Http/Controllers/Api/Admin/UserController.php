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
            $roleLabel = 'Kolektor';
            if ($user->role === 'admin') {
                $roleLabel = 'Administrator';
            } elseif ($user->auctions->count() > 0) {
                $roleLabel = 'Seniman';
            }

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
}
