<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['key', 'scope', 'user_id', 'request_hash', 'response_hash', 'response_body', 'status', 'created_at'];

    protected $casts = [
        'response_body' => 'array',
        'created_at' => 'datetime',
    ];
}
