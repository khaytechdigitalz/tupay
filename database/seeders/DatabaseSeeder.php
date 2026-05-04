<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a Sample User
        $user = User::updateOrCreate(
            ['email' => 'tester@tupay.com'], // Prevent duplicates if run twice
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'two_factor_secret' => null, // Ready for 2FA testing
            ]
        );

        // 2. Create an NGN Wallet with 50,000 balance (stored as 5,000,000 subunits)
        Wallet::updateOrCreate(
            ['user_id' => $user->id, 'currency' => 'NGN'],
            ['balance' => 5000000]
        );

        // 3. Create a CNY Wallet with 1,000 balance (stored as 100,000 subunits)
        Wallet::updateOrCreate(
            ['user_id' => $user->id, 'currency' => 'CNY'],
            ['balance' => 100000]
        );

        $this->command->info('Test user and wallets created successfully!');
    }
}
