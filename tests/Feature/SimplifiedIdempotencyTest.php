<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SimplifiedIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $wallet;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user with wallet
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

        // Create authentication token
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function repeated_deposit_with_same_idempotency_key_is_idempotent()
    {
        $idempotencyKey = 'test-idempotent-deposit-' . uniqid();

        // First deposit
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/deposit', [
            'amount' => 5000,
            'idempotency_key' => $idempotencyKey,
            'pin' => 123456
        ]);

        $response1->assertStatus(201)
            ->assertJsonPath('data.balance', 15000); // $100 + $50 = $150

        // Second deposit with same idempotency key
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/deposit', [
            'amount' => 5000, // Same amount
            'idempotency_key' => $idempotencyKey, // Same key
            'pin' => 123456
        ]);

        $response2->assertStatus(201)
            ->assertJsonPath('data.balance', 15000); // Should still be $150

        // Verify database was updated only once
        $this->assertEquals(15000, $this->user->wallet->fresh()->balance);

        // Verify only one ledger entry was created
        $ledgerCount = DB::table('ledger_entries')
            ->where('wallet_id', $this->wallet->id)
            ->where('type', 'deposit')
            ->where('amount', 5000)
            ->count();

        $this->assertEquals(1, $ledgerCount);
    }

    /** @test */
    public function repeated_transfer_with_same_idempotency_key_is_idempotent()
    {
        // Create recipient user with wallet
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

        $idempotencyKey = 'test-idempotent-transfer-' . uniqid();

        // First transfer
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/transfer', [
            'amount' => 3000,
            'to_user_id' => $recipient->id,
            'idempotency_key' => $idempotencyKey,
            'pin' => 123456
        ]);

        $response1->assertStatus(200);

        // Second transfer with same idempotency key
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/transfer', [
            'amount' => 3000, // Same amount
            'to_user_id' => $recipient->id, // Same recipient
            'idempotency_key' => $idempotencyKey, // Same key
            'pin' => 123456
        ]);

        $response2->assertStatus(200);

        // Get updated balances
        $senderBalance = $this->user->wallet->fresh()->balance;
        $recipientBalance = $recipient->wallet->fresh()->balance;

        // Verify balances are correct after both requests
        $this->assertEquals(6450, $senderBalance); // $100 - $30 - $5.50 fee = $64.50
        $this->assertEquals(8000, $recipientBalance); // $50 + $30 = $80

        // Verify only one transfer record was created
        $transferCount = DB::table('transfers')
            ->where('sender_wallet_id', $this->wallet->id)
            ->where('receiver_wallet_id', $recipientWallet->id)
            ->where('amount', 3000)
            ->count();

        $this->assertEquals(1, $transferCount);
    }
}
