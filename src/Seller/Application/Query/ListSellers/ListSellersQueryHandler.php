<?php

declare(strict_types=1);

namespace Example\Seller\Application\Query\ListSellers;

use Example\Seller\Infrastructure\Persistence\StorageInterface;

readonly class ListSellersQueryHandler
{
    public function __construct(private StorageInterface $storage)
    {
    }

    // Kind of useless query object...
    public function handle(ListSellersQuery $listSellersQuery): ListSellersDTO
    {
        return new ListSellersDTO(...$this->storage->findAllSellers());
    }
}
