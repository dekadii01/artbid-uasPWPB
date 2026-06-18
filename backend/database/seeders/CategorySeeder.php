<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder CategorySeeder
 * 
 * Digunakan untuk mengisi database dengan data kategori awal (default)
 * agar frontend dan sistem dapat langsung digunakan.
 */
class CategorySeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // Daftar kategori default lengkap dengan deskripsi dan ikon SVG bawaan
        $categories = [
            [
                'name'        => 'Lukisan',
                'description' => 'Kategori untuk berbagai karya seni lukis.',
                'icon'        => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
            [
                'name'        => 'Patung',
                'description' => 'Kategori untuk berbagai karya seni patung.',
                'icon'        => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18',
            ],
            [
                'name'        => 'Topeng Tradisional',
                'description' => 'Kategori untuk koleksi topeng tradisional Bali dan Nusantara.',
                'icon'        => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'name'        => 'Barang Antik',
                'description' => 'Kategori untuk barang koleksi dan benda antik.',
                'icon'        => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
            ],
            [
                'name'        => 'Ukiran Kayu',
                'description' => 'Kategori untuk berbagai ukiran dan relief kayu.',
                'icon'        => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            ],
            [
                'name'        => 'Kain Tradisional',
                'description' => 'Kategori untuk batik, tenun, dan kain tradisional Nusantara.',
                'icon'        => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                [
                    'slug'        => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'icon'        => $cat['icon'],
                ]
            );
        }
    }
}
