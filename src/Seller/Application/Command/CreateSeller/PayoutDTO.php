<?php

declare(strict_types=1);

namespace Example\Seller\Application\Command\CreateSeller;

class PayoutDTO
{
    public function __construct(
        private readonly int $amount,
        private string $currencyCode,
    ) {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
