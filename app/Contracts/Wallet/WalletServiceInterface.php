<?php

namespace App\Contracts\Wallet;

use App\Utils\MoneyUtil;
use App\DTOs\TransferResultDTO;
use App\Models\User;
use App\Models\Wallet;

interface WalletServiceInterface
{
    /**
     * Ensure user has a wallet, create if not exists
     *
     * @param User $user
     * @return Wallet
     */
    public function ensureUserWallet(User $user): Wallet;

    /**
     * Deposit funds to user's wallet
     *
     * @param User $user
     * @param MoneyUtil $amount
     * @param string|null $idempotencyKey
     * @return array
     */
    public function deposit(User $user, MoneyUtil $amount, ?string $idempotencyKey = null): array;

    /**
     * Withdraw funds from user's wallet
     *
     * @param User $user
     * @param MoneyUtil $amount
     * @param string|null $idempotencyKey
     * @return array
     */
    public function withdraw(User $user, MoneyUtil $amount, ?string $idempotencyKey = null): array;

    /**
     * Transfer funds between users
     *
     * @param User $sender
     * @param User $receiver
     * @param MoneyUtil $amount
     * @param string|null $idempotencyKey
     * @return TransferResultDTO
     */
    public function transfer(User $sender, User $receiver, MoneyUtil $amount, ?string $idempotencyKey = null): TransferResultDTO;
}
