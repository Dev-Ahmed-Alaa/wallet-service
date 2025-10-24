<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidAmountException;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\LedgerRepository;
use App\Repositories\WalletRepository;
use App\Services\WalletService;
use App\Utils\MoneyUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimplifiedWalletServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $walletRepository;
    protected $ledgerRepository;
    protected $walletService;
    protected $user;
    protected $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and wallet
        $this->user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password123@')
        ]);

        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 10000, // $100.00
            'status' => 'active',
            'pin_hash' => Hash::make('123456') // Set a PIN hash for testing
        ]);

        // Set up real repositories for integration testing
        $this->walletRepository = app(WalletRepository::class);
        $this->ledgerRepository = app(LedgerRepository::class);
        $this->walletService = new WalletService(
            $this->walletRepository,
            $this->ledgerRepository
        );
    }

    /** @test */
    public function it_ensures_user_has_wallet()
    {
        // Test with existing wallet
        $wallet = $this->walletService->ensureUserWallet($this->user);
        $this->assertEquals($this->wallet->id, $wallet->id);

        // Test with new user that doesn't have a wallet
        $newUser = User::factory()->create([
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'new@example.com',
            'password' => bcrypt('Password123@')
        ]);

        $newWallet = $this->walletService->ensureUserWallet($newUser);
        $this->assertEquals($newUser->id, $newWallet->user_id);
    }

    /** @test */
    public function it_deposits_funds_into_wallet()
    {
        $amount = new MoneyUtil(5000); // $50.00
        $result = $this->walletService->deposit($this->user, $amount);

        $this->assertEquals(15000, $result['balance']); // $100 + $50 = $150.00

        // Verify ledger entry was created
        $entries = $this->ledgerRepository->getEntriesForWallet($this->wallet);
        $this->assertCount(1, $entries);
        $this->assertEquals('credit', $entries[0]->direction);
        $this->assertEquals('deposit', $entries[0]->type);
        $this->assertEquals(5000, $entries[0]->amount); // $50.00
    }

    /** @test */
    public function it_withdraws_funds_from_wallet()
    {
        $amount = new MoneyUtil(2500); // $25.00
        $result = $this->walletService->withdraw($this->user, $amount);

        $this->assertEquals(7500, $result['balance']); // $100 - $25 = $75.00

        // Verify ledger entry was created
        $entries = $this->ledgerRepository->getEntriesForWallet($this->wallet);
        $this->assertCount(1, $entries);
        $this->assertEquals('debit', $entries[0]->direction);
        $this->assertEquals('withdrawal', $entries[0]->type);
        $this->assertEquals(2500, $entries[0]->amount); // $25.00
    }

    /** @test */
    public function it_transfers_funds_between_wallets()
    {
        // Create recipient user and wallet
        $recipient = User::factory()->create([
            'first_name' => 'Recipient',
            'last_name' => 'User',
            'email' => 'recipient@example.com',
            'password' => bcrypt('Password123@')
        ]);

        $recipientWallet = Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 5000, // $50.00
            'status' => 'active',
            'pin_hash' => Hash::make('123456') // Set a PIN hash for testing
        ]);

        // Transfer $20 (below fee threshold)
        $amount = new MoneyUtil(2000);
        $result = $this->walletService->transfer($this->user, $recipient, $amount);

        // Verify sender's balance ($100 - $20 = $80)
        $this->assertEquals(8000, $result->senderBalanceAfter);

        // Verify recipient's balance ($50 + $20 = $70)
        $this->assertEquals(7000, $result->recipientBalanceAfter);

        // Verify no fee was charged
        $this->assertEquals(0, $result->feeAmount);
    }

    /** @test */
    public function it_charges_fee_for_transfers_above_threshold()
    {
        // Create recipient user and wallet
        $recipient = User::factory()->create([
            'first_name' => 'Recipient',
            'last_name' => 'User',
            'email' => 'recipient2@example.com',
            'password' => bcrypt('Password123@')
        ]);

        $recipientWallet = Wallet::factory()->create([
            'user_id' => $recipient->id,
            'balance' => 5000, // $50.00
            'status' => 'active',
            'pin_hash' => Hash::make('123456') // Set a PIN hash for testing
        ]);

        // Transfer $30 (above $25 fee threshold)
        $amount = new MoneyUtil(3000);
        $result = $this->walletService->transfer($this->user, $recipient, $amount);

        // Calculate expected fee: $2.50 + 10% of $30 = $5.50
        $expectedFee = 550;

        // Verify fee was charged
        $this->assertEquals($expectedFee, $result->feeAmount);

        // Verify sender's balance ($100 - $30 - $5.50 = $64.50)
        $this->assertEquals(6450, $result->senderBalanceAfter);

        // Verify recipient's balance ($50 + $30 = $80)
        $this->assertEquals(8000, $result->recipientBalanceAfter);
    }
}
