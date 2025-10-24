<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'status',
        'pin_hash'
    ];

    protected $casts = [
        'balance' => 'integer', // Always store as cents
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
