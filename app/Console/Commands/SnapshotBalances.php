<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotBalances extends Command
{
    protected $signature = 'wallet:snapshot-balances';

    protected $description = 'Create balance snapshots for all wallets';

    public function handle(): int
    {
        $now = now();
        $count = 0;
        Wallet::query()->chunk(500, function ($wallets) use ($now, &$count) {
            foreach ($wallets as $wallet) {
                DB::table('balance_snapshots')->insert([
                    'wallet_id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'snapshot_taken_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $count++;
            }
        });

        $this->info("Created {$count} snapshots");

        return self::SUCCESS;
    }
}
