<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Anti-sniping protection
    |--------------------------------------------------------------------------
    |
    | Jika ada bid masuk dalam N detik terakhir sebelum lelang berakhir,
    | waktu ends_at akan diperpanjang sebesar nilai ini (dalam detik).
    |
    | Set ke 0 untuk menonaktifkan fitur ini.
    | Rekomendasi: 30 atau 60 detik.
    |
    */
    'snipe_protection_seconds' => (int) env('AUCTION_SNIPE_SECONDS', 30),

];
