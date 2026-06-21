# Audit Rubrik Penilaian Proyek ArtBid Bali

## Pendahuluan

Audit ini dilakukan untuk mengevaluasi tingkat kesesuaian implementasi sistem ArtBid Bali terhadap rubrik penilaian yang diberikan pada proyek UAS. Evaluasi dilakukan dengan meninjau langsung implementasi fitur pada sisi backend dan frontend, termasuk konfigurasi autentikasi, manajemen lelang, sistem penawaran, komunikasi realtime berbasis WebSocket, serta fitur bonus yang menjadi nilai tambah dalam pengembangan sistem.

Hasil audit menunjukkan bahwa seluruh fitur wajib dan fitur bonus yang tercantum dalam rubrik telah berhasil diimplementasikan dan berfungsi sesuai kebutuhan sistem.

---

# 1. Audit Fitur Wajib – Autentikasi dan Akun

### 1.1 Registrasi, Login, dan Logout Menggunakan Laravel Sanctum

Berdasarkan hasil audit, sistem telah mengimplementasikan mekanisme autentikasi menggunakan Laravel Sanctum. Proses registrasi, login, dan logout tersedia melalui endpoint API yang terintegrasi dengan frontend menggunakan Pinia Store sehingga pengguna dapat mengakses sistem secara aman melalui token autentikasi.

**Status Implementasi: Terpenuhi (100%)**

---

### 1.2 Pengguna Dapat Berperan Sebagai Penjual dan Penawar

Hasil audit menunjukkan bahwa sistem tidak membatasi pengguna pada satu peran tertentu. Setiap pengguna dapat membuat lelang sebagai penjual sekaligus mengikuti lelang lain sebagai penawar menggunakan akun yang sama.

**Status Implementasi: Terpenuhi (100%)**

---

### 1.3 Proteksi Endpoint Sensitif

Seluruh endpoint yang berkaitan dengan operasi penting seperti pembuatan lelang, penawaran, watchlist, dashboard, dan administrasi telah dilindungi menggunakan middleware autentikasi Laravel Sanctum. Selain itu, akses administrator dibatasi menggunakan middleware otorisasi berbasis role.

**Status Implementasi: Terpenuhi (100%)**

---

# 2. Audit Fitur Wajib – Manajemen Lelang

### 2.1 Pembuatan Lelang

Pengguna dapat membuat lelang dengan mengisi informasi barang berupa judul, deskripsi, foto barang, harga awal, kelipatan penawaran minimum, waktu mulai, dan waktu berakhir lelang. Seluruh data divalidasi menggunakan Form Request sebelum disimpan ke basis data.

**Status Implementasi: Terpenuhi (100%)**

---

### 2.2 Perubahan dan Penghapusan Lelang

Sistem hanya mengizinkan perubahan maupun penghapusan lelang selama status lelang masih terjadwal (scheduled). Apabila lelang telah dimulai atau berakhir, sistem secara otomatis menolak proses tersebut.

**Status Implementasi: Terpenuhi (100%)**

---

### 2.3 Pengelolaan Status Lelang Otomatis

Hasil audit menunjukkan bahwa sistem mampu mengelola status lelang secara otomatis mulai dari:

* Scheduled (Terjadwal)
* Active (Berlangsung)
* Ended (Selesai)

Perubahan status dilakukan melalui Scheduler Laravel serta mekanisme fail-safe pada endpoint detail lelang.

**Status Implementasi: Terpenuhi (100%)**

---

### 2.4 Riwayat Lelang Milik Pengguna

Sistem menyediakan halaman khusus yang memungkinkan pengguna melihat seluruh lelang yang pernah dibuat, termasuk status dan hasil akhir dari masing-masing lelang.

**Status Implementasi: Terpenuhi (100%)**

---

# 3. Audit Fitur Wajib – Sistem Penawaran

### 3.1 Validasi Kelipatan Penawaran

Setiap penawaran baru harus memiliki nilai minimal sebesar harga tertinggi saat ini ditambah kelipatan minimum yang telah ditentukan oleh penjual.

**Status Implementasi: Terpenuhi (100%)**

---

### 3.2 Larangan Menawar Lelang Sendiri

Pengguna tidak diperbolehkan melakukan penawaran pada lelang yang dibuat oleh dirinya sendiri. Validasi dilakukan pada sisi server sebelum data penawaran diproses.

**Status Implementasi: Terpenuhi (100%)**

---

### 3.3 Penawaran Hanya Saat Lelang Aktif

Penawaran hanya dapat dilakukan ketika status lelang berada pada kondisi aktif dan masih berada dalam rentang waktu yang ditentukan.

**Status Implementasi: Terpenuhi (100%)**

---

### 3.4 Penolakan Penawaran Setelah Lelang Berakhir

Apabila waktu lelang telah habis atau status telah berubah menjadi selesai, sistem secara otomatis menolak seluruh penawaran baru.

**Status Implementasi: Terpenuhi (100%)**

---

### 3.5 Mekanisme Outbid

Ketika terdapat penawaran yang lebih tinggi, sistem secara otomatis mengubah status penawaran tertinggi sebelumnya menjadi outbid sehingga hanya terdapat satu penawaran tertinggi yang aktif pada suatu waktu.

**Status Implementasi: Terpenuhi (100%)**

---

# 4. Audit Fitur Wajib – Realtime dan WebSocket

### 4.1 Pembaruan Penawaran Secara Realtime

Ketika terdapat penawaran baru, seluruh pengguna yang sedang membuka halaman lelang akan langsung menerima pembaruan harga tertinggi, jumlah penawaran, dan riwayat penawaran tanpa perlu melakukan refresh halaman.

**Status Implementasi: Terpenuhi (100%)**

---

### 4.2 Notifikasi Outbid Realtime

Pengguna yang penawarannya dikalahkan akan menerima notifikasi secara realtime melalui kanal privat yang diamankan menggunakan autentikasi Sanctum.

**Status Implementasi: Terpenuhi (100%)**

---

### 4.3 Pengumuman Pemenang Secara Realtime

Ketika lelang berakhir, sistem secara otomatis menentukan pemenang dan mengirimkan informasi tersebut secara realtime kepada seluruh pengguna yang sedang berada pada halaman lelang.

**Status Implementasi: Terpenuhi (100%)**

---

### 4.4 Otorisasi Kanal

Seluruh kanal privat dan presence channel telah dikonfigurasi menggunakan Laravel Broadcasting dengan mekanisme otorisasi pada file channels.php.

**Status Implementasi: Terpenuhi (100%)**

---

### 4.5 Countdown Waktu Lelang

Hitung mundur waktu lelang ditampilkan secara konsisten pada seluruh pengguna dengan menggunakan referensi waktu yang berasal dari server sehingga mengurangi perbedaan waktu antar perangkat pengguna.

**Status Implementasi: Terpenuhi (100%)**

---

# 5. Audit Fitur Bonus

### 5.1 Anti-Sniping

Sistem mengimplementasikan fitur anti-sniping yang secara otomatis memperpanjang waktu lelang apabila terdapat penawaran pada detik-detik terakhir sebelum lelang berakhir.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.2 Buy Now

Sistem menyediakan fitur Buy Now yang memungkinkan pengguna langsung memenangkan lelang dengan harga yang telah ditentukan oleh penjual.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.3 Presence Channel

Sistem menampilkan jumlah pengguna yang sedang melihat halaman lelang secara realtime menggunakan Presence Channel Laravel Reverb.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.4 Upload Multi Foto ke Object Storage

Sistem mendukung unggahan banyak foto barang dan dapat menggunakan penyimpanan berbasis cloud seperti Amazon S3 maupun Cloudflare R2.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.5 Deployment Publik dengan HTTPS

Aplikasi telah dipersiapkan untuk deployment publik dengan dukungan HTTPS sehingga dapat diakses melalui jaringan internet secara aman.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.6 Dokumentasi API Otomatis

Sistem telah menyediakan dokumentasi API otomatis menggunakan Scribe sehingga seluruh endpoint dapat diuji dan dipahami dengan mudah.

**Status Implementasi: Terpenuhi (100%)**

---

### 5.7 Pengujian Otomatis

Pengujian otomatis menggunakan Pest PHP telah diterapkan untuk memverifikasi logika penting seperti proses bidding, autentikasi, watchlist, buy now, serta fitur administrasi.

**Status Implementasi: Terpenuhi (100%)**

---

# Kesimpulan

Berdasarkan hasil audit yang telah dilakukan, seluruh komponen yang tercantum pada rubrik penilaian berhasil diimplementasikan pada sistem ArtBid Bali. Seluruh fitur wajib memperoleh tingkat pemenuhan sebesar 100%, termasuk autentikasi, manajemen lelang, sistem penawaran, serta komunikasi realtime berbasis WebSocket. Selain itu, seluruh fitur bonus yang direkomendasikan dalam rubrik juga telah berhasil diterapkan, sehingga sistem tidak hanya memenuhi kebutuhan minimum proyek, tetapi juga menyediakan berbagai nilai tambah yang meningkatkan kualitas, keamanan, dan pengalaman pengguna secara keseluruhan.
