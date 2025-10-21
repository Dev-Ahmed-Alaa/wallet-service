<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'balance', 'snapshot_taken_at'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
