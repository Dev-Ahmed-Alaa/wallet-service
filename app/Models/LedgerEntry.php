<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    public $timestamps = false; // immutable created_at only

    protected $fillable = [
        'wallet_id',
        'direction',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
