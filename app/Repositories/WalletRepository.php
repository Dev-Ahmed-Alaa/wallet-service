<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

class WalletRepository
{
    /**
     * Find a wallet by user ID
     *
     * @param int $userId The user ID
     * @return Wallet|null The wallet or null if not found
     */
    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }

    /**
     * Create a new wallet for a user
     *
     * @param User $user The user
     * @return Wallet The created wallet
     */
    public function createForUser(User $user): Wallet
    {
        return $user->wallet()->create([
            'balance' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Lock a wallet for update
     *
     * @param int $userId The user ID
     * @return Wallet|null The locked wallet or null if not found
     */
    public function lockForUpdate(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->lockForUpdate()->first();
    }

    /**
     * Update a wallet's balance
     *
     * @param Wallet $wallet The wallet to update
     * @param int $newBalance The new balance
     * @return Wallet The updated wallet
     */
    public function updateBalance(Wallet $wallet, int $newBalance): Wallet
    {
        $wallet->update(['balance' => $newBalance]);

        return $wallet->fresh();
    }

    /**
     * Get all active wallets
     *
     * @return Collection Collection of active wallets
     */
    public function getActiveWallets(): Collection
    {
        return Wallet::where('status', 'active')->get();
    }
}
