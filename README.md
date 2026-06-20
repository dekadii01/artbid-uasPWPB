# ArtBid Bali 🎨🎏

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Pest](https://img.shields.io/badge/Pest%20PHP-4.x-blue.svg)](https://pestphp.com)
[![Scribe](https://img.shields.io/badge/API%20Docs-Scribe-yellow.svg)](https://scribe.knuckles.wtf)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

ArtBid Bali adalah platform lelang karya seni online berbasis realtime yang dirancang khusus untuk komunitas seniman dan kolektor seni di Bali. Proyek ini dibangun sebagai proyek kolaboratif tugas kuliah (monorepo) yang terbagi menjadi backend berbasis Laravel 11 dan frontend SPA berbasis Vue 3.

Platform ini memungkinkan pengguna menawar karya seni secara langsung (realtime bidding) tanpa harus me-refresh halaman, dilengkapi dengan fitur Buy Now (Beli Instan), Watchlist, Sistem Notifikasi, serta Panel Manajemen Admin.

---

## 🚀 Fitur Utama

1. **Realtime Bidding Engine**: Menampilkan penawaran harga terbaru secara instan menggunakan WebSocket (Laravel Reverb & Laravel Echo client).
2. **Buy Now (Beli Instan)**: Opsi bagi pembeli untuk memenangkan karya seni secara langsung dengan harga yang ditentukan oleh penjual.
3. **Watchlist System**: Menyimpan dan memantau lelang favorit, lengkap dengan riwayat aktivitas penawaran terbaru.
4. **Sistem Notifikasi**: Notifikasi otomatis ketika penawaran tergeser (*outbid*), lelang berakhir (*auction ended*), atau lelang dimenangkan (*auction won*).
5. **Panel Admin & Modul Keamanan**: CRUD kategori karya seni, manajemen status pengguna (blokir/aktifkan), laporan data periodik, dan ekspor laporan ke format CSV.
6. **Dokumentasi API Interaktif**: Dokumentasi API lengkap menggunakan Scribe yang dapat dicoba secara langsung (Try It Out).
7. **Test Suite Komprehensif**: 40+ unit & feature tests menggunakan Pest PHP dengan database in-memory SQLite.

---

## 🛠️ Tech Stack

### Backend (Folder `/backend`)
*   **Framework**: Laravel 11
*   **Authentication**: Laravel Sanctum (Token-based SPA Authentication)
*   **Websocket Server**: Laravel Reverb
*   **Database**: MySQL 8 (local) / SQLite (testing)
*   **Testing**: Pest PHP & PHPUnit
*   **API Docs**: Scribe

### Frontend (Folder `/frontend`)
*   **Framework**: Vue 3 (Composition API / `<script setup>`)
*   **Build Tool**: Vite
*   **Router**: Vue Router
*   **WebSocket Client**: Laravel Echo & Pusher JS
*   **Styling**: Custom Vanilla CSS (Premium & modern UI)

---

## 📁 Struktur Proyek (Monorepo)

```text
ArtBid/
├── backend/            # Aplikasi Backend (Laravel 11 API)
│   ├── app/            # Controller, Model, Request, Events
│   ├── config/         # Konfigurasi aplikasi (services.php, scribe.php)
│   ├── database/       # Migrasi, Seeders, Factories
│   ├── routes/         # Definis api.php & channels.php
│   └── tests/          # Feature & Unit tests (Pest PHP)
├── frontend/           # Aplikasi Frontend (Vue 3 SPA)
│   ├── public/         # Aset statis frontend
│   ├── src/            # Komponen, Views, Router, koneksi API
│   └── index.html      # Halaman HTML utama
└── README.md           # Dokumentasi utama proyek ini
```

---

## 📦 Panduan Instalasi & Pengaturan

### Prasyarat
Pastikan Anda sudah menginstal:
*   PHP >= 8.3
*   Composer >= 2.x
*   Node.js >= 20.x & npm
*   MySQL Server

---

### 1. Pengaturan Backend (Laravel)

Masuk ke direktori backend:
```bash
cd backend
```

Pasang semua dependensi PHP:
```bash
composer install
```

Salin file konfigurasi environment:
```bash
cp .env.example .env
```

Atur konfigurasi database dan integrasi Google OAuth pada file `.env` Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_artbid
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Google OAuth (Socialite)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

Generate application key:
```bash
php artisan key:generate
```

Jalankan migrasi database dan pengisian data awal (seeder):
```bash
php artisan migrate --seed
```

Jalankan server websocket Laravel Reverb:
```bash
php artisan reverb:start
```

Jalankan Laravel queue worker untuk memproses notifikasi latar belakang:
```bash
php artisan queue:listen --tries=1
```

Jalankan server lokal backend:
```bash
php artisan serve
```
Backend sekarang berjalan di `http://localhost:8000`.

---

### 2. Pengaturan Frontend (Vue 3)

Buka terminal baru dan masuk ke direktori frontend:
```bash
cd ../frontend
```

Pasang dependensi Node:
```bash
npm install
```

Salin file konfigurasi environment frontend:
```bash
cp .env.example .env
```
Isi konfigurasi environment dengan URL backend Anda:
```env
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=your-reverb-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Jalankan server lokal frontend:
```bash
npm run dev
```
Frontend sekarang berjalan di `http://localhost:5173`.

---

## 📝 Dokumentasi API

ArtBid menggunakan Scribe untuk menghasilkan dokumentasi API interaktif yang diperbarui secara otomatis dari anotasi kode controller.

*   Untuk melihat dokumentasi API di browser, buka alamat:
    `http://localhost:8000/docs`
*   Jika Anda melakukan perubahan pada controller backend dan ingin memperbarui dokumentasi, jalankan perintah berikut di direktori `backend/`:
    ```bash
    php artisan scribe:generate
    ```

---

## 🧪 Menjalankan Pengujian (Testing)

Kami menulis pengujian unit & fitur lengkap menggunakan Pest PHP untuk menjamin reliabilitas alur bidding, penanganan saldo/transaksi, notifikasi, serta keamanan level admin.

Untuk menjalankan seluruh test suite, ketikkan perintah berikut di direktori `backend/`:
```bash
php artisan test
```

---

## 🤝 Penulis Proyek

Proyek tugas kuliah ini dikembangkan secara kolaboratif oleh:
*   **Developer Backend**: Pengembang API Laravel 11, Websocket, & Database.
*   **Developer Frontend**: Pengembang UI Vue 3 & Client-side Integration.

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT License](LICENSE).
