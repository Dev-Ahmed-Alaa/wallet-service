<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'direction' => $this->direction,
            'type' => $this->type,
            'amount' => $this->amount,
            'amount_formatted' => '$'.number_format($this->amount / 100, 2),
            'balance_after' => $this->balance_after,
            'balance_after_formatted' => '$'.number_format($this->balance_after / 100, 2),
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
