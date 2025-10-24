<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimplifiedConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
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
    }

    /** @test */
    public function it_prevents_double_spending_during_concurrent_transfers()
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
            'balance' => 0, // $0.00
            'status' => 'active',
            'pin_hash' => Hash::make('123456') // Set a PIN hash for testing
        ]);

        // Initial sender balance: $100.00
        // We'll try to transfer $60 multiple times concurrently
        // Only one should succeed, others should fail due to insufficient funds

        // Create a token for authentication
        $token = $this->user->createToken('test-token')->plainTextToken;

        // Number of concurrent transfer attempts
        $concurrentRequests = 3;

        // Each transfer is $60
        $transferAmount = 6000;

        // Prepare concurrent requests
        $promises = [];
        $client = new \GuzzleHttp\Client([
            'base_uri' => config('app.url'),
            'http_errors' => false,
        ]);

        // Launch concurrent transfer requests
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $promises[] = $client->postAsync('/api/v1/wallet/transfer', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'amount' => $transferAmount,
                    'to_user_id' => $recipient->id,
                    'idempotency_key' => 'transfer-test-' . $i . '-' . uniqid(),
                    'pin' => 123456
                ],
            ]);
        }

        // Wait for all requests to complete
        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

        // Count successful and failed transfers
        $successfulTransfers = 0;
        $failedTransfers = 0;

        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled') {
                $statusCode = $response['value']->getStatusCode();
                if ($statusCode === 200) {
                    $successfulTransfers++;
                } else {
                    $failedTransfers++;
                }
            } else {
                $failedTransfers++;
            }
        }

        // Refresh wallets from database
        $this->wallet->refresh();
        $recipientWallet->refresh();

        // Verify only one transfer succeeded (or none if balance was insufficient)
        $this->assertLessThanOrEqual(1, $successfulTransfers);

        // If a transfer succeeded, verify the balances are correct
        if ($successfulTransfers === 1) {
            // Sender balance should be $100 - $60 = $40 (or less if fees were applied)
            $this->assertLessThanOrEqual(4000, $this->wallet->balance);

            // Recipient balance should be $0 + $60 = $60
            $this->assertEquals(6000, $recipientWallet->balance);
        }

        // Verify the total money in the system remains consistent
        $totalMoneyBefore = 10000; // $100.00 (sender) + $0.00 (recipient)
        $totalMoneyAfter = $this->wallet->balance + $recipientWallet->balance;

        // If fees were charged, they should be deducted from the total
        if ($successfulTransfers === 1 && $transferAmount > 25.00) {
            // Fee: $2.50 + 10% of $60 = $8.50
            $expectedFee = 850;
            $this->assertEquals($totalMoneyBefore - $expectedFee, $totalMoneyAfter);
        } else {
            $this->assertEquals($totalMoneyBefore, $totalMoneyAfter);
        }
    }
}
