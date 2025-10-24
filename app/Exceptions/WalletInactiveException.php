<?php

namespace App\Exceptions;

use Exception;

class WalletInactiveException extends Exception
{
    public function __construct(string $message = 'Wallet is inactive')
    {
        parent::__construct($message);
    }
}
