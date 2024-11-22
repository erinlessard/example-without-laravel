<?php

declare(strict_types=1);

namespace Example\Seller\Application\Query\ListSellers;

use Example\Seller\Domain\Seller;

class ListSellersDTO implements \JsonSerializable
{
    private array $sellers = [];
    public function __construct(Seller ...$sellers)
    {
        foreach ($sellers as $seller) {
            $this->sellers[] = $seller->jsonSerialize();
        }
    }

    public function jsonSerialize(): array
    {
        return $this->sellers;
    }
}