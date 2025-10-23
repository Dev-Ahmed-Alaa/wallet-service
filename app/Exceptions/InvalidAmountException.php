<?php

namespace App\Exceptions;

use Exception;

class InvalidAmountException extends Exception
{
    public function __construct(string $message = 'Invalid amount')
    {
        parent::__construct($message);
    }
}
