<?php

namespace App\Repositories;

use App\Models\LedgerEntry;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

class LedgerRepository
{
    /**
     * Create a new ledger entry
     *
     * @param array $data The ledger entry data
     * @return LedgerEntry The created ledger entry
     */
    public function createEntry(array $data): LedgerEntry
    {
        return LedgerEntry::create($data);
    }

    /**
     * Get ledger entries for a wallet
     *
     * @param Wallet $wallet The wallet
     * @param int $limit Maximum number of entries to return
     * @return Collection Collection of ledger entries
     */
    public function getEntriesForWallet(Wallet $wallet, int $limit = 100): Collection
    {
        return $wallet->ledgerEntries()
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get ledger entries by reference
     *
     * @param string $type The reference type
     * @param int $id The reference ID
     * @return Collection Collection of ledger entries
     */
    public function getEntriesByReference(string $type, int $id): Collection
    {
        return LedgerEntry::where('reference_type', $type)
            ->where('reference_id', $id)
            ->get();
    }
}
