<?php

// Mendefinisikan namespace untuk seeder di Laravel
namespace Database\Seeders;

// Mengimpor model Setting yang akan diisi datanya
use App\Models\Setting;
// Mengimpor kelas dasar Seeder
use Illuminate\Database\Seeder;

// Mendefinisikan kelas SettingSeeder yang mewarisi Seeder
class SettingSeeder extends Seeder
{
    /**
     * Menjalankan proses pengisian data awal (seeding) ke database.
     */
    public function run(): void
    {
        // Mendefinisikan array data default untuk seluruh tab pengaturan sistem
        $defaultSettings = [
            // Pengaturan Umum
            'umum' => [
                'platformName' => 'ArtBid Bali',
                'description' => 'Platform lelang daring untuk komunitas kolektor barang seni di Bali.',
                'adminEmail' => 'admin@artbidbali.com',
                'contact' => '+62 812 3456 7890',
            ],
            // Pengaturan Lelang
            'lelang' => [
                'defaultDuration' => '7',
                'minBidIncrement' => 100000,
                'maxPhotos' => 10,
                'requireApproval' => true,
                'antiSnipingEnabled' => true,
                'antiSnipingWindow' => 30,
                'antiSnipingExtension' => 2,
                'buyNowEnabled' => true,
                'sellerSetBuyNow' => true,
            ],
            // Pengaturan Layanan Realtime (Laravel Reverb)
            'realtime' => [
                'serverStatus' => 'Online',
                'activeConnections' => 42,
                'broadcastDriver' => 'Reverb',
                'presenceChannelEnabled' => true,
                'channels' => [
                    ['name' => 'Auction Channels', 'count' => 78, 'active' => true],
                    ['name' => 'Presence Channels', 'count' => 42, 'active' => true],
                    ['name' => 'Notification Channels', 'count' => 120, 'active' => true],
                ],
            ],
            // Pengaturan Pemicu Notifikasi
            'notifikasi' => [
                'outbid' => true,
                'winner' => true,
                'auctionEnded' => true,
                'adminAlert' => true,
                'methodInApp' => true,
                'methodEmail' => true,
            ],
            // Pengaturan Keamanan
            'keamanan' => [
                'sessionExpiry' => 7,
                'maxLoginAttempts' => 5,
                'allowPasswordReset' => true,
                'requireEmailVerification' => true,
            ],
            // Pengaturan Penyimpanan File
            'penyimpanan' => [
                'driver' => 'local',
                'maxFileSize' => 5,
                'allowedFormats' => ['jpg', 'jpeg', 'png', 'webp'],
            ],
            // Pengaturan Jadwal Cadangan (Backup)
            'backup' => [
                'autoBackup' => true,
                'frequency' => 'daily',
                'lastBackup' => '14 Juni 2026, 23.59 WITA',
            ],
        ];

        // Melakukan perulangan untuk menyimpan masing-masing grup pengaturan ke database
        foreach ($defaultSettings as $key => $value) {
            // Menggunakan method updateOrCreate agar seeder aman dijalankan berulang kali tanpa memicu duplikasi data
            Setting::updateOrCreate(
                ['key' => $key],     // Kunci pencarian berdasarkan kolom 'key'
                ['value' => $value]  // Nilai baru yang akan disimpan atau diperbarui
            );
        }
    }
}
