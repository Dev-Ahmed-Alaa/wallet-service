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
        $user = User::FirstOrcreate([
            'email' => 'menna.rateb@recapet.com',
        ], [
            'first_name' => 'Recapet',
            'last_name' => 'Company',
            'email' => 'menna.rateb@recapet.com',
            'password' => Hash::make('Recapet@123'),
        ]);

        $user->wallet()->create(
            [
                'balance' => 0,
                'status' => 'active',
                'pin_hash' => Hash::make('123456'),
            ]
        );
    }
}
