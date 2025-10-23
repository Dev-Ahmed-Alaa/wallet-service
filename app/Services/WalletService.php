<?php

namespace App\Services;

use App\Contracts\Wallet\WalletServiceInterface;
use App\Utils\MoneyUtil;
use App\DTOs\TransferResultDTO;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\WalletInactiveException;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\LedgerRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WalletService implements WalletServiceInterface
{
    /**
     * Constructor
     *
     * @param WalletRepository $walletRepository The wallet repository
     * @param LedgerRepository $ledgerRepository The ledger repository
     */
    public function __construct(
        private WalletRepository $walletRepository,
        private LedgerRepository $ledgerRepository
    ) {}

    /**
     * Ensure a user has a wallet, creating one if it doesn't exist
     *
     * @param User $user The user to ensure wallet for
     * @return Wallet The user's wallet
     */
    public function ensureUserWallet(User $user): Wallet
    {
        $wallet = $this->walletRepository->findByUserId($user->id);
        if (! $wallet) {
            $wallet = $this->walletRepository->createForUser($user);
        }

        return $wallet;
    }

    /**
     * Deposit funds into a user's wallet
     *
     * @param User $user The user to deposit funds for
     * @param MoneyUtil $amount The amount to deposit
     * @param string|null $idempotencyKey Optional idempotency key for preventing duplicate operations
     * @return array The deposit result with updated balance
     * @throws \Exception If wallet not found
     * @throws InvalidAmountException If amount is invalid
     */
    public function deposit(User $user, MoneyUtil $amount, ?string $idempotencyKey = null): array
    {
        return $this->performIdempotent($user, $idempotencyKey, function () use ($user, $amount) {
            return DB::transaction(function () use ($user, $amount) {
                $wallet = $this->walletRepository->lockForUpdate($user->id);
                if (! $wallet) {
                    throw new \Exception('Wallet not found');
                }

                if ($amount->isZero() || $amount->cents < 0) {
                    throw new InvalidAmountException('Amount must be positive');
                }

                $newBalance = $wallet->balance + $amount->cents;
                $this->walletRepository->updateBalance($wallet, $newBalance);

                $this->ledgerRepository->createEntry([
                    'wallet_id' => $wallet->id,
                    'direction' => 'credit',
                    'type' => 'deposit',
                    'amount' => $amount->cents,
                    'balance_after' => $newBalance,
                    'created_at' => now(),
                ]);

                return ['balance' => $newBalance];
            });
        });
    }

    /**
     * Withdraw funds from a user's wallet
     *
     * @param User $user The user to withdraw funds from
     * @param MoneyUtil $amount The amount to withdraw
     * @param string|null $idempotencyKey Optional idempotency key for preventing duplicate operations
     * @return array The withdrawal result with updated balance
     * @throws \Exception If wallet not found
     * @throws InvalidAmountException If amount is invalid
     * @throws InsufficientBalanceException If balance is insufficient
     */
    public function withdraw(User $user, MoneyUtil $amount, ?string $idempotencyKey = null): array
    {
        return $this->performIdempotent($user, $idempotencyKey, function () use ($user, $amount) {
            return DB::transaction(function () use ($user, $amount) {
                $wallet = $this->walletRepository->lockForUpdate($user->id);
                if (! $wallet) {
                    throw new \Exception('Wallet not found');
                }

                if ($amount->isZero() || $amount->cents < 0) {
                    throw new InvalidAmountException('Amount must be positive');
                }

                if ($wallet->balance < $amount->cents) {
                    throw new InsufficientBalanceException('Insufficient balance');
                }

                $newBalance = $wallet->balance - $amount->cents;
                $this->walletRepository->updateBalance($wallet, $newBalance);

                $this->ledgerRepository->createEntry([
                    'wallet_id' => $wallet->id,
                    'direction' => 'debit',
                    'type' => 'withdrawal',
                    'amount' => $amount->cents,
                    'balance_after' => $newBalance,
                    'created_at' => now(),
                ]);

                return ['balance' => $newBalance];
            });
        });
    }

    /**
     * Transfer funds from one user to another
     *
     * @param User $sender The user sending funds
     * @param User $receiver The user receiving funds
     * @param MoneyUtil $amount The amount to transfer
     * @param string|null $idempotencyKey Optional idempotency key for preventing duplicate operations
     * @return TransferResultDTO The transfer result with updated balances
     * @throws \Exception If wallet not found
     * @throws WalletInactiveException If either wallet is inactive
     * @throws InvalidAmountException If amount is invalid
     * @throws InsufficientBalanceException If sender has insufficient balance
     */
    public function transfer(User $sender, User $receiver, MoneyUtil $amount, ?string $idempotencyKey = null): TransferResultDTO
    {
        return $this->performIdempotent($sender, $idempotencyKey, function () use ($sender, $receiver, $amount, $idempotencyKey) {
            return DB::transaction(function () use ($sender, $receiver, $amount, $idempotencyKey) {
                $senderWallet = $this->walletRepository->lockForUpdate($sender->id);
                $receiverWallet = $this->walletRepository->lockForUpdate($receiver->id);

                if (! $senderWallet || ! $receiverWallet) {
                    throw new \Exception('Wallet not found');
                }

                if ($senderWallet->status !== 'active' || $receiverWallet->status !== 'active') {
                    throw new WalletInactiveException('Wallet is inactive');
                }

                if ($amount->isZero() || $amount->cents < 0) {
                    throw new InvalidAmountException('Amount must be positive');
                }

                $fee = $this->calculateFee($amount);
                $totalDebit = $amount->add($fee);

                if ($senderWallet->balance < $totalDebit->cents) {
                    throw new InsufficientBalanceException('Insufficient balance');
                }

                $newSenderBalance = $senderWallet->balance - $totalDebit->cents;
                $newReceiverBalance = $receiverWallet->balance + $amount->cents;

                $this->walletRepository->updateBalance($senderWallet, $newSenderBalance);
                $this->walletRepository->updateBalance($receiverWallet, $newReceiverBalance);

                $transfer = Transfer::create([
                    'sender_wallet_id' => $senderWallet->id,
                    'receiver_wallet_id' => $receiverWallet->id,
                    'amount' => $amount->cents,
                    'fee_amount' => $fee->cents,
                    'status' => 'succeeded',
                    'idempotency_key' => $idempotencyKey ?: uniqid('tx_', true),
                ]);

                // Create ledger entries
                $this->createTransferLedgerEntries($senderWallet, $receiverWallet, $amount, $fee, $transfer, $newSenderBalance, $newReceiverBalance);

                return new TransferResultDTO(
                    new MoneyUtil($newSenderBalance),
                    new MoneyUtil($newReceiverBalance),
                    $transfer->id,
                    $fee
                );
            });
        });
    }

    /**
     * Calculate the fee for a transfer
     *
     * @param MoneyUtil $amount The transfer amount
     * @return MoneyUtil The calculated fee
     */
    private function calculateFee(MoneyUtil $amount): MoneyUtil
    {
        if ($amount->cents > 2500) { // > $25
            $baseFee = new MoneyUtil(250); // $2.50
            $percentageFee = $amount->multiply(0.10); // 10%

            return $baseFee->add($percentageFee);
        }

        return MoneyUtil::zero();
    }

    /**
     * Create ledger entries for a transfer
     *
     * @param Wallet $senderWallet The sender's wallet
     * @param Wallet $receiverWallet The receiver's wallet
     * @param MoneyUtil $amount The transfer amount
     * @param MoneyUtil $fee The fee amount
     * @param Transfer $transfer The transfer record
     * @param int $newSenderBalance The sender's new balance
     * @param int $newReceiverBalance The receiver's new balance
     * @return void
     */
    private function createTransferLedgerEntries(Wallet $senderWallet, Wallet $receiverWallet, MoneyUtil $amount, MoneyUtil $fee, Transfer $transfer, int $newSenderBalance, int $newReceiverBalance): void
    {
        // Sender transfer out
        $this->ledgerRepository->createEntry([
            'wallet_id' => $senderWallet->id,
            'amount' => -$amount->cents,
            'balance' => $newSenderBalance + $fee->cents,
            'balance_after' => $newSenderBalance, // Balance after fee'
            'type' => 'transfer_out',
            'direction' => 'debit',
            'reference_id' => $transfer->id,
            'reference_type' => Transfer::class,
            'created_at' => now(),
        ]);

        // Sender fee
        if (! $fee->isZero()) {
            $this->ledgerRepository->createEntry([
                'wallet_id' => $senderWallet->id,
                'amount' => -$fee->cents,
                'balance' => $newSenderBalance,
                'balance_after' => $newSenderBalance,
                'type' => 'fee',
                'direction' => 'debit',
                'reference_id' => $transfer->id,
                'reference_type' => Transfer::class,
                'created_at' => now(),
            ]);
        }

        // Receiver transfer in
        $this->ledgerRepository->createEntry([
            'wallet_id' => $receiverWallet->id,
            'amount' => $amount->cents,
            'balance' => $newReceiverBalance,
            'balance_after' => $newReceiverBalance,
            'type' => 'transfer_in',
            'direction' => 'credit',
            'reference_id' => $transfer->id,
            'reference_type' => Transfer::class,
            'created_at' => now(),
        ]);
    }

    /**
     * Perform an operation with idempotency guarantees
     *
     * @param User $user The user performing the operation
     * @param string|null $idempotencyKey The idempotency key
     * @param callable $operation The operation to perform
     * @return mixed The result of the operation
     */
    private function performIdempotent(User $user, ?string $idempotencyKey, callable $operation)
    {
        if (! $idempotencyKey) {
            return $operation();
        }

        $lock = Cache::lock("idempotency:{$user->id}:{$idempotencyKey}", 10);

        try {
            $lock->block(5);

            $cacheKey = "transaction:{$user->id}:{$idempotencyKey}";
            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult) {
                return $cachedResult;
            }

            $result = $operation();
            Cache::put($cacheKey, $result, now()->addDay());

            return $result;
        } finally {
            optional($lock)->release();
        }
    }
}
