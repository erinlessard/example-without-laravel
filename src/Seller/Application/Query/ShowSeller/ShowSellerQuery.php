<?php

declare(strict_types=1);

namespace Example\Seller\Application\Query\ShowSeller;

readonly class ShowSellerQuery
{
    public function __construct(private string $sellerId)
    {
    }

    public function getSellerId(): string
    {
        return $this->sellerId;
    }
}
