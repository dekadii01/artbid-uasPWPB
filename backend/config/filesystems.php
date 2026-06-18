<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Diambil dari .env (FILESYSTEM_DISK=r2)
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        /*
        |----------------------------------------------------------------
        | Cloudflare R2 (S3-compatible)
        |----------------------------------------------------------------
        | Kosongkan AWS_DEFAULT_REGION jika pakai R2 (set 'auto').
        | use_path_style_endpoint WAJIB true untuk R2.
        */
        'r2' => [
            'driver' => 's3',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'auto'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'url'    => env('AWS_URL'),   // ← tambahkan ini
        ],

        /*
        |----------------------------------------------------------------
        | AWS S3 asli (kalau suatu saat pindah ke S3 beneran, bukan R2)
        |----------------------------------------------------------------
        */
        's3' => [
            'driver' => 's3',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'auto'),
            'bucket' => env('AWS_BUCKET'),
            'url'    => env('AWS_URL'),         // ← pastikan ada ini
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,  // ← wajib untuk R2
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
