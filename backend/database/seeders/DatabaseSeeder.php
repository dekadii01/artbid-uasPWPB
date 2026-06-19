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
            'phone'      => '081234567890',
            'password'   => Hash::make('labs123'),
        ]);

        DatabaseSeeder::call([
            CategorySeeder::class,
        ]);
    }
}
