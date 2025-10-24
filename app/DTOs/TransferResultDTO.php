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

    public function __get($name)
    {
        switch ($name) {
            case 'senderBalanceAfter':
                return $this->senderBalance->cents;
            case 'recipientBalanceAfter':
                return $this->receiverBalance->cents;
            case 'feeAmount':
                return $this->fee->cents;
        }

        throw new \InvalidArgumentException("Property {$name} does not exist");
    }
}
