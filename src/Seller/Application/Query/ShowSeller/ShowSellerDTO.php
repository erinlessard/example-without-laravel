<?php

declare(strict_types=1);

namespace Example\Seller\Application\Query\ShowSeller;

readonly class ShowSellerDTO implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        private \DateTimeImmutable $createdAt,
        private bool $isActive,
        private string $soldProductType,
        private int $payoutAmount,
        private string $payoutCurrency,
    ) {
    }

    // We could be selective on exactly which properties we return to this specific query.
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->createdAt->format(DATE_ISO8601_EXPANDED),
            'is_active' => $this->isActive,
            'sold_product_type' => $this->soldProductType,
            'payout_amount' => $this->payoutAmount,
            'payout_currency' => $this->payoutCurrency,
        ];
    }
}
