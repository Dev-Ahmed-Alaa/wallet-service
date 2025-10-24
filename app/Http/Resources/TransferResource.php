<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_wallet_id' => $this->sender_wallet_id,
            'receiver_wallet_id' => $this->receiver_wallet_id,
            'amount' => $this->amount,
            'amount_formatted' => '$'.number_format($this->amount / 100, 2),
            'fee_amount' => $this->fee_amount,
            'fee_amount_formatted' => '$'.number_format($this->fee_amount / 100, 2),
            'status' => $this->status,
            'idempotency_key' => $this->idempotency_key,
            'error' => $this->error,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
