<?php

declare(strict_types=1);

namespace Example\Seller\Application\Query\ShowSeller;

use Example\Seller\Infrastructure\Persistence\StorageInterface;

readonly class ShowSellerQueryHandler
{
    public function __construct(
        private StorageInterface $storage,
    ) {
    }

    public function handle(ShowSellerQuery $sellerQuery): ?ShowSellerDTO
    {
        if ($seller = $this->storage->findSellerById(id: $sellerQuery->getSellerId())) {
            return new ShowSellerDTO(
                id: $seller->getId(),
                name: $seller->getName(),
                description: $seller->getDescription(),
                createdAt: $seller->getCreatedAt(),
                isActive: $seller->isActive(),
                soldProductType: $seller->getSoldProductType()->value,
                payoutAmount: $seller->getPayoutAmount()->getMinorAmount()->toInt(),
                payoutCurrency: $seller->getPayoutAmount()->getCurrency()->getCurrencyCode(),
            );
        }

        return null;
    }
}
