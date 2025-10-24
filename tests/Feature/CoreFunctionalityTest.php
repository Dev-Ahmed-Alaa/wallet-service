<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreFunctionalityTest extends TestCase
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
    public function user_registration_creates_wallet()
    {
        $response = $this->postJson('/api/v1/register', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'dev.ahmedalaa@gmail.com',
            'password' => 'Password123@',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.email', 'dev.ahmedalaa@gmail.com');

        // Verify wallet was created
        $userId = $response->json('data.user.id');
        $this->assertDatabaseHas('wallets', [
            'user_id' => $userId,
            'balance' => 0,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function user_can_deposit_funds()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/deposit', [
            'amount' => 5000,
            'idempotency_key' => 'test-deposit-' . uniqid(),
            'pin' => 123456
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.balance', 15000); // $100 + $50 = $150

        // Verify database was updated
        $this->assertEquals(15000, $this->user->wallet->fresh()->balance);
    }

    /** @test */
    public function user_can_withdraw_funds()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/withdraw', [
            'amount' => 2500,
            'idempotency_key' => 'test-withdraw-' . uniqid(),
            'pin' => 123456
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.balance', 7500); // $100 - $25 = $75

        // Verify database was updated
        $this->assertEquals(7500, $this->user->wallet->fresh()->balance);
    }

    /** @test */
    public function user_cannot_withdraw_more_than_balance()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/withdraw', [
            'amount' => 15000, // More than $100 balance
            'idempotency_key' => 'test-excessive-withdraw-' . uniqid(),
            'pin' => 123456
        ]);

        $response->assertStatus(422);

        // Verify balance remains unchanged
        $this->assertEquals(10000, $this->user->wallet->fresh()->balance);
    }

    /** @test */
    public function user_can_transfer_funds_to_another_user()
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/transfer', [
            'amount' => 2000, // Below fee threshold
            'to_user_id' => $recipient->id,
            'idempotency_key' => 'test-transfer-' . uniqid(),
            'pin' => 123456
        ]);

        $response->assertStatus(200);

        // Verify sender's balance was updated ($100 - $20 = $80)
        $this->assertEquals(8000, $this->user->wallet->fresh()->balance);

        // Verify recipient's balance was updated ($50 + $20 = $70)
        $this->assertEquals(7000, $recipient->wallet->fresh()->balance);
    }

    /** @test */
    public function transfer_above_threshold_incurs_fee()
    {
        // Create recipient user with wallet
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/wallet/transfer', [
            'amount' => 3000, // Above $25 fee threshold
            'to_user_id' => $recipient->id,
            'idempotency_key' => 'test-transfer-fee-' . uniqid(),
            'pin' => 123456
        ]);

        $response->assertStatus(200);

        // Calculate expected fee: $2.50 + 10% of $30 = $5.50
        $expectedFee = 550;

        // Verify sender's balance was updated ($100 - $30 - $5.50 = $64.50)
        $this->assertEquals(6450, $this->user->wallet->fresh()->balance);

        // Verify recipient's balance was updated ($50 + $30 = $80)
        $this->assertEquals(8000, $recipient->wallet->fresh()->balance);
    }
}
