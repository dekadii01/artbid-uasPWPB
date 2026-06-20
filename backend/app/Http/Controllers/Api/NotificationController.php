<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * @group Notifications
     * @authenticated
     *
     * Tampilkan semua notifikasi user saat ini.
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 3,
     *       "type": "outbid",
     *       "channel": "private",
     *       "title": "Penawaran Anda tergeser!",
     *       "body": "Penawaran Anda sebesar Rp 12.500.000 pada lelang \"Lukisan Bali Klasik\" telah dilewati.",
     *       "read_at": null,
     *       "data": {
     *         "auction_id": 1,
     *         "auction_title": "Lukisan Bali Klasik",
     *         "amount": 13000000.0
     *       },
     *       "created_at": "2026-06-20T14:30:00.000000Z",
     *       "updated_at": "2026-06-20T14:30:00.000000Z"
     *     }
     *   ],
     *   "first_page_url": "http://localhost:8000/api/notifications?page=1",
     *   "from": 1,
     *   "last_page": 1,
     *   "last_page_url": "http://localhost:8000/api/notifications?page=1",
     *   "next_page_url": null,
     *   "path": "http://localhost:8000/api/notifications",
     *   "per_page": 15,
     *   "prev_page_url": null,
     *   "to": 1,
     *   "total": 1
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ambil notifikasi diurutkan dari yang terbaru
        $notifications = $user->notifications()
            ->orderByRaw('read_at IS NULL DESC') // Unread first
            ->latest()
            ->paginate(15);

        return response()->json($notifications);
    }

    /**
     * @group Notifications
     * @authenticated
     *
     * Tandai satu notifikasi sebagai sudah dibaca.
     *
     * @urlParam notification integer required ID notifikasi. Example: 1
     *
     * @response 200 {
     *   "message": "Notifikasi ditandai sebagai dibaca.",
     *   "notification": {
     *     "id": 1,
     *     "user_id": 3,
     *     "type": "outbid",
     *     "channel": "private",
     *     "title": "Penawaran Anda tergeser!",
     *     "body": "Penawaran Anda sebesar Rp 12.500.000 pada lelang \"Lukisan Bali Klasik\" telah dilewati.",
     *     "read_at": "2026-06-20T14:35:00.000000Z",
     *     "data": {
     *       "auction_id": 1,
     *       "auction_title": "Lukisan Bali Klasik",
     *       "amount": 13000000.0
     *     },
     *     "created_at": "2026-06-20T14:30:00.000000Z",
     *     "updated_at": "2026-06-20T14:35:00.000000Z"
     *   }
     * }
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        // Pastikan notifikasi milik user yang sedang login
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message'      => 'Notifikasi ditandai sebagai dibaca.',
            'notification' => $notification,
        ]);
    }

    /**
     * @group Notifications
     * @authenticated
     *
     * Tandai semua notifikasi user sebagai sudah dibaca.
     *
     * @response 200 {
     *   "message": "Semua notifikasi berhasil ditandai sebagai dibaca."
     * }
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->notifications()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'Semua notifikasi berhasil ditandai sebagai dibaca.',
        ]);
    }
}
