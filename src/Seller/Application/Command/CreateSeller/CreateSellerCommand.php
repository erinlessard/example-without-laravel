<?php

declare(strict_types=1);

namespace Example\Seller\Application\Command\CreateSeller;

readonly class CreateSellerCommand
{
    // Simple input into the command as it was *probably* created in a controller or somewhere closer to the user.
    // property typing covers simple input validation. We'd want to examine things closer to the domain objects.
    public function __construct(
        private string $name,
        private string $description,
        private bool $isActive,
        private string $soldProductTypes,
        private int $payoutAmount,
        private string $payoutCurrency,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getSoldProductTypes(): string
    {
        return $this->soldProductTypes;
    }

    public function getPayoutAmount(): int
    {
        return $this->payoutAmount;
    }

    public function getPayoutCurrency(): string
    {
        return $this->payoutCurrency;
    }
}
