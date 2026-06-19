<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Admin',
            'last_name'  => 'Bid',
            'email'      => 'admin@gmail.com',
            'phone'      => '081234567890',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
        ]);

        User::create([
            'first_name' => 'Lab',
            'last_name'  => 'Sky',
            'email'      => 'labs@gmail.com',
            'phone'      => '081234567891',
            'password'   => Hash::make('labs123'),
        ]);

        // Membuat 10 data user dummy
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'first_name' => 'User',
                'last_name'  => (string) $i,
                'email'      => "user{$i}@gmail.com",
                'phone'      => '0812345678' . str_pad(
                    (string) $i,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'password'   => Hash::make('user123'),
                'role'       => 'user',
            ]);
        }

        $this->call([
            CategorySeeder::class,
            SettingSeeder::class,
            AuctionSeeder::class,
        ]);
    }
}