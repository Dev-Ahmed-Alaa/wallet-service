<?php

namespace App\DTOs;

use App\Utils\MoneyUtil;

readonly class TransferResultDTO
{
    public function __construct(
        public MoneyUtil $senderBalance,
        public MoneyUtil $receiverBalance,
        public int $transferId,
        public MoneyUtil $fee
    ) {}

    public function toArray(): array
    {
        return [
            'sender_balance' => $this->senderBalance->cents,
            'sender_balance_formatted' => $this->senderBalance->toFormattedString(),
            'receiver_balance' => $this->receiverBalance->cents,
            'receiver_balance_formatted' => $this->receiverBalance->toFormattedString(),
            'transfer_id' => $this->transferId,
            'fee' => $this->fee->cents,
            'fee_formatted' => $this->fee->toFormattedString(),
        ];
    }
}
