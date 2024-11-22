<?php

declare(strict_types=1);

namespace Example\Seller\Domain;

class PayoutValueObject
{
    public function __construct(
        private int $amount,
        private string $currency,
    ) {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
