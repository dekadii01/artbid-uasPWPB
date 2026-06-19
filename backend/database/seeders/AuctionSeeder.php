<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\AuctionWinner;
use App\Models\Bid;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan atau buat seller (Lab Sky)
        $seller = User::where('email', 'labs@gmail.com')->first();
        if (!$seller) {
            $seller = User::create([
                'first_name' => 'Lab',
                'last_name'  => 'Sky',
                'email'      => 'labs@gmail.com',
                'phone'      => '081234567890',
                'password'   => Hash::make('labs123'),
                'role'       => 'user',
            ]);
        }

        // 2. Dapatkan atau buat bidders
        $bidder1 = User::where('email', 'budi@gmail.com')->first();
        if (!$bidder1) {
            $bidder1 = User::create([
                'first_name' => 'Budi',
                'last_name'  => 'Collector',
                'email'      => 'budi@gmail.com',
                'phone'      => '08111222333',
                'password'   => Hash::make('budi123'),
                'role'       => 'user',
            ]);
        }

        $bidder2 = User::where('email', 'siti@gmail.com')->first();
        if (!$bidder2) {
            $bidder2 = User::create([
                'first_name' => 'Siti',
                'last_name'  => 'Art Lover',
                'email'      => 'siti@gmail.com',
                'phone'      => '08222333444',
                'password'   => Hash::make('siti123'),
                'role'       => 'user',
            ]);
        }

        // 3. Pastikan kategori-kategori ada
        $categories = [];
        $catNames = ['Lukisan', 'Patung', 'Topeng Tradisional', 'Barang Antik', 'Ukiran Kayu', 'Kain Tradisional'];
        
        foreach ($catNames as $name) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug'        => Str::slug($name),
                    'description' => "Kategori untuk karya seni {$name}.",
                    'icon'        => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                ]
            );
        }

        // 4. Data 11 lelang
        $auctionData = [
            [
                'title'          => 'Penari Bali Klasik',
                'category'       => 'Lukisan',
                'condition'      => 'Sangat Baik',
                'artist'         => 'I Nyoman Meja',
                'year'           => 1998,
                'starting_price' => 7500000,
                'bid_increment'  => 250000,
                'buy_now_price'  => 12000000,
                'description'    => 'Lukisan cat minyak di atas kanvas menggambarkan keindahan gerakan penari Bali tradisional dengan detail goresan yang legendaris dan estetis.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subDays(2),
                'ends_at'        => Carbon::now()->addDays(5),
                'image'          => 'auctions/art1.png',
                'bids'           => [8000000, 8250000, 8500000],
            ],
            [
                'title'          => 'Patung Garuda Wisnu Kencana Kuningan',
                'category'       => 'Patung',
                'condition'      => 'Baru',
                'artist'         => 'Nyoman Nuarta Studio',
                'year'           => 2015,
                'starting_price' => 15000000,
                'bid_increment'  => 500000,
                'buy_now_price'  => 25000000,
                'description'    => 'Replika miniatur patung Garuda Wisnu Kencana berbahan kuningan solid berkualitas tinggi, dipoles dengan presisi tinggi oleh seniman profesional Bali.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subDays(1),
                'ends_at'        => Carbon::now()->addDays(6),
                'image'          => 'auctions/art2.png',
                'bids'           => [15500000, 16000000],
            ],
            [
                'title'          => 'Topeng Barong Ket Sakral',
                'category'       => 'Topeng Tradisional',
                'condition'      => 'Sangat Baik',
                'artist'         => 'Cokorda Raka',
                'year'           => 2010,
                'starting_price' => 5000000,
                'bid_increment'  => 200000,
                'buy_now_price'  => 8000000,
                'description'    => 'Topeng Barong Ket tradisional Bali yang diukir dari kayu pule pilihan dengan rambut serat kelapa alami dan hiasan kulit sapi tatah emas murni.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subHours(12),
                'ends_at'        => Carbon::now()->addDays(4),
                'image'          => 'auctions/art3.png',
                'bids'           => [5200000],
            ],
            [
                'title'          => 'Keris Pusaka Singo Barong Mataram',
                'category'       => 'Barang Antik',
                'condition'      => 'Bekas (Koleksi Tua)',
                'artist'         => 'Empu Supodriyo (Est.)',
                'year'           => 1750,
                'starting_price' => 25000000,
                'bid_increment'  => 1000000,
                'buy_now_price'  => 45000000,
                'description'    => 'Keris pusaka asli peninggalan era Mataram dengan luk 7 dan pamor Singo Barong yang berlapis emas tipis di bagian pangkal bilahnya. Sangat langka.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subDays(3),
                'ends_at'        => Carbon::now()->addDays(3),
                'image'          => 'auctions/art4.png',
                'bids'           => [26000000, 27000000, 28000000, 29000000],
            ],
            [
                'title'          => 'Ukiran Relief Ramayana Kayu Jati',
                'category'       => 'Ukiran Kayu',
                'condition'      => 'Sangat Baik',
                'artist'         => 'Made Ada',
                'year'           => 2002,
                'starting_price' => 8500000,
                'bid_increment'  => 250000,
                'buy_now_price'  => 14000000,
                'description'    => 'Ukiran kayu jati solid 3D detail tinggi yang menceritakan kisah epik penculikan Dewi Sita dalam epos Ramayana. Ukuran panel 120cm x 60cm.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subDays(2),
                'ends_at'        => Carbon::now()->addDays(2),
                'image'          => 'auctions/art5.png',
                'bids'           => [],
            ],
            [
                'title'          => 'Kain Songket Jembrana Benang Emas',
                'category'       => 'Kain Tradisional',
                'condition'      => 'Baru',
                'artist'         => 'Ni Ketut Asri',
                'year'           => 2021,
                'starting_price' => 4000000,
                'bid_increment'  => 150000,
                'buy_now_price'  => 6500000,
                'description'    => 'Kain songket khas Jembrana yang ditenun secara manual (ATBM) menggunakan benang emas premium dengan motif bunga jepun Bali tradisional.',
                'status'         => 'active',
                'starts_at'      => Carbon::now()->subHours(6),
                'ends_at'        => Carbon::now()->addDays(4),
                'image'          => 'auctions/art6.png',
                'bids'           => [],
            ],
            [
                'title'          => 'Lukisan Pemandangan Ubud Klasik',
                'category'       => 'Lukisan',
                'condition'      => 'Baik',
                'artist'         => 'Ida Bagus Made',
                'year'           => 1975,
                'starting_price' => 18500000,
                'bid_increment'  => 500000,
                'buy_now_price'  => null,
                'description'    => 'Karya lukis gaya pita-maha legendaris menggambarkan pemandangan alam dan kehidupan agraris masyarakat Ubud tempo dulu di lereng bukit hijau.',
                'status'         => 'scheduled',
                'starts_at'      => Carbon::now()->addDays(2),
                'ends_at'        => Carbon::now()->addDays(9),
                'image'          => 'auctions/art7.png',
                'bids'           => [],
            ],
            [
                'title'          => 'Patung Dewi Saraswati Marmer Putih',
                'category'       => 'Patung',
                'condition'      => 'Baru',
                'artist'         => 'I Wayan Wana',
                'year'           => 2018,
                'starting_price' => 12500000,
                'bid_increment'  => 500000,
                'buy_now_price'  => 20000000,
                'description'    => 'Patung Dewi Saraswati pembawa pengetahuan, dipahat dengan sangat halus dari bahan batu marmer putih pilihan asal Tulungagung.',
                'status'         => 'scheduled',
                'starts_at'      => Carbon::now()->addDays(1),
                'ends_at'        => Carbon::now()->addDays(8),
                'image'          => 'auctions/art8.png',
                'bids'           => [],
            ],
            [
                'title'          => 'Topeng Rangda Seram Tradisional',
                'category'       => 'Topeng Tradisional',
                'condition'      => 'Sangat Baik',
                'artist'         => 'Ketut Liyer',
                'year'           => 2012,
                'starting_price' => 4500000,
                'bid_increment'  => 200000,
                'buy_now_price'  => 7000000,
                'description'    => 'Topeng Rangda yang merepresentasikan kekuatan mistis Bali, diukir presisi dengan taring panjang dan lidah api kulit yang dilukis indah.',
                'status'         => 'ended',
                'starts_at'      => Carbon::now()->subDays(10),
                'ends_at'        => Carbon::now()->subDays(3),
                'image'          => 'auctions/art9.png',
                'bids'           => [4700000, 4900000, 5100000],
            ],
            [
                'title'          => 'Gamelan Gong Kebyar Gilded Antik',
                'category'       => 'Barang Antik',
                'condition'      => 'Bekas',
                'artist'         => 'Pande Ketut',
                'year'           => 1945,
                'starting_price' => 35000000,
                'bid_increment'  => 1500000,
                'buy_now_price'  => 60000000,
                'description'    => 'Satu set instrumen Gong Kebyar antik buatan tahun 1945 dengan ukiran kayu jati berlapis prada emas asli. Suara gong masih sangat nyaring.',
                'status'         => 'ended',
                'starts_at'      => Carbon::now()->subDays(12),
                'ends_at'        => Carbon::now()->subDays(5),
                'image'          => 'auctions/art10.png',
                'bids'           => [36500000, 38000000],
            ],
            [
                'title'          => 'Kain Batik Tulis Klasik Jogja Sogan',
                'category'       => 'Kain Tradisional',
                'condition'      => 'Baru',
                'artist'         => 'Hj. Kartini',
                'year'           => 2019,
                'starting_price' => 3500000,
                'bid_increment'  => 100000,
                'buy_now_price'  => 5500000,
                'description'    => 'Kain batik tulis motif parang rusak sogan klasik Yogyakarta, dibuat dengan canting lilin malam tradisional secara teliti selama 6 bulan pengerjaan.',
                'status'         => 'ended',
                'starts_at'      => Carbon::now()->subDays(8),
                'ends_at'        => Carbon::now()->subDays(2),
                'image'          => 'auctions/art11.png',
                'bids'           => [3600000, 3700000, 3800000, 3900000],
            ],
        ];

        // 5. Ambil 10 data user dummy dari DatabaseSeeder
        $dummyUsers = [];
        for ($i = 1; $i <= 10; $i++) {
            $u = User::where('email', "user{$i}@gmail.com")->first();
            if ($u) {
                $dummyUsers[] = $u;
            }
        }

        // 6. Masukkan data ke Database
        foreach ($auctionData as $key => $data) {
            $catId = $categories[$data['category']]->id;
            
            // Tentukan current price (harga tertinggi dari list bids, atau starting_price)
            $currentPrice = $data['starting_price'];
            if (!empty($data['bids'])) {
                $currentPrice = max($data['bids']);
            }

            // Pilih seller dari 10 dummy users secara berurutan
            $currentSeller = $seller; // default fallback ke Labs Sky
            if (!empty($dummyUsers)) {
                $currentSeller = $dummyUsers[$key % count($dummyUsers)];
            }

            $auction = Auction::create([
                'seller_id'      => $currentSeller->id,
                'title'          => $data['title'],
                'description'    => $data['description'],
                'category_id'    => $catId,
                'condition'      => $data['condition'],
                'artist'         => $data['artist'],
                'year'           => $data['year'],
                'starting_price' => $data['starting_price'],
                'current_price'  => $currentPrice,
                'bid_increment'  => $data['bid_increment'],
                'buy_now_price'  => $data['buy_now_price'],
                'status'         => $data['status'],
                'starts_at'      => $data['starts_at'],
                'ends_at'        => $data['ends_at'],
            ]);

            // Tambah gambar lelang
            AuctionImage::create([
                'auction_id'   => $auction->id,
                'image_path'   => $data['image'],
                'storage_disk' => 'r2', // Menunjuk ke Supabase bucket
                'sort_order'   => 0,
            ]);

            // Tambah bid history jika ada
            $insertedBids = [];
            foreach ($data['bids'] as $index => $amount) {
                // Bergantian antara bidder 1 dan bidder 2
                $bidder = ($index % 2 === 0) ? $bidder1 : $bidder2;
                
                $bidStatus = 'outbid';
                // Jika ini adalah bid terakhir dalam list
                if ($index === count($data['bids']) - 1) {
                    $bidStatus = ($data['status'] === 'ended') ? 'won' : 'active';
                }

                $bid = Bid::create([
                    'auction_id' => $auction->id,
                    'bidder_id'  => $bidder->id,
                    'amount'     => $amount,
                    'status'     => $bidStatus,
                    'placed_at'  => $data['starts_at']->copy()->addHours($index + 1),
                ]);

                $insertedBids[] = $bid;
            }

            // Jika status ended dan memiliki bid, buat entri pemenang
            if ($data['status'] === 'ended' && !empty($insertedBids)) {
                $winningBid = end($insertedBids);
                AuctionWinner::create([
                    'auction_id'     => $auction->id,
                    'winner_id'      => $winningBid->bidder_id,
                    'winning_bid_id' => $winningBid->id,
                    'final_price'    => $winningBid->amount,
                ]);
            }
        }
    }
}
