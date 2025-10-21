<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Recapet',
            'last_name' => 'Company',
            'email' => 'menna.rateb@recapet.com',
            'password' => Hash::make('Recapet@123'),
        ]);
    }
}
