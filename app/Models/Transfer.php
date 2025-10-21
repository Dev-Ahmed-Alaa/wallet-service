<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_wallet_id',
        'receiver_wallet_id',
        'amount',
        'fee_amount',
        'status',
        'idempotency_key',
        'error',
    ];

    protected $casts = [
        'amount' => 'integer',
        'fee_amount' => 'integer',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function receiverWallet()
    {
        return $this->belongsTo(Wallet::class, 'receiver_wallet_id');
    }
}
