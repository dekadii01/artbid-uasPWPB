<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tampilkan semua notifikasi user saat ini.
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
     * Tandai satu notifikasi sebagai sudah dibaca.
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
     * Tandai semua notifikasi user sebagai sudah dibaca.
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
