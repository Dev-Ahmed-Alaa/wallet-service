<?php

namespace App\Utils;

readonly class MoneyUtil
{
    public function __construct(
        public int $cents,
        public string $currency = 'USD'
    ) {}

    public function toDollars(): float
    {
        return $this->cents / 100;
    }

    public function toFormattedString(): string
    {
        return '$'.number_format($this->toDollars(), 2);
    }

    public function add(MoneyUtil $other): self
    {
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function subtract(MoneyUtil $other): self
    {
        return new self($this->cents - $other->cents, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->cents * $factor), $this->currency);
    }

    public function isGreaterThan(MoneyUtil $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function isLessThan(MoneyUtil $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public static function fromDollars(float $dollars): self
    {
        return new self((int) round($dollars * 100));
    }

    public static function zero(): self
    {
        return new self(0);
    }
}
