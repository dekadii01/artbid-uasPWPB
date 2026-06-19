<?php

// Mendefinisikan namespace untuk controller admin
namespace App\Http\Controllers\Api\Admin;

// Mengimpor kelas dasar Controller
use App\Http\Controllers\Controller;
// Mengimpor model Setting yang akan dikelola
use App\Models\Setting;
// Mengimpor kelas pendukung response JSON
use Illuminate\Http\JsonResponse;
// Mengimpor kelas pendukung HTTP Request
use Illuminate\Http\Request;

// Mendefinisikan kelas SettingController yang mewarisi Controller
class SettingController extends Controller
{
    /**
     * Mengambil seluruh pengaturan sistem dari database.
     * Mengembalikan response JSON berisi daftar pasangan key dan value.
     */
    public function index(): JsonResponse
    {
        // Mengambil seluruh data pengaturan dari database
        $settings = Setting::all();

        // Menyusun ulang data pengaturan agar terformat sebagai key-value map sederhana
        $formattedSettings = [];
        foreach ($settings as $setting) {
            // Memasukkan nilai pengaturan ke dalam array berdasarkan kunci (key) grupnya
            $formattedSettings[$setting->key] = $setting->value;
        }

        // Mengembalikan response sukses HTTP 200 berisi data pengaturan sistem terformat
        return response()->json([
            'message' => 'Pengaturan sistem berhasil dimuat.',
            'settings' => $formattedSettings,
        ]);
    }

    /**
     * Memperbarui atau menyimpan data pengaturan sistem ke database.
     * Mendukung pembaruan satu grup maupun pembaruan massal (simpan semua).
     */
    public function update(Request $request): JsonResponse
    {
        // Mengambil seluruh data input dari request HTTP
        $data = $request->all();

        // Memeriksa apakah input memiliki kunci 'key' dan 'value' (berarti pembaruan satu grup tertentu)
        if ($request->has('key') && $request->has('value')) {
            // Mengambil kunci grup pengaturan (misalnya 'umum')
            $key = $request->input('key');
            // Mengambil nilai pengaturan grup tersebut
            $value = $request->input('value');

            // Menyimpan atau memperbarui data grup pengaturan tunggal di database
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } else {
            // Jika input berupa objek berpasangan (misalnya { umum: {...}, lelang: {...} })
            // Melakukan perulangan untuk memproses pembaruan massal
            foreach ($data as $key => $value) {
                // Memastikan nilai value berbentuk array/objek sebelum disimpan
                if (is_array($value)) {
                    // Menyimpan atau memperbarui data masing-masing grup pengaturan di database
                    Setting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            }
        }

        // Mengembalikan response sukses HTTP 200 setelah berhasil menyimpan perubahan
        return response()->json([
            'message' => 'Pengaturan sistem berhasil disimpan.',
        ]);
    }
}
