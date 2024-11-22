<?php

declare(strict_types=1);

namespace Example\Seller\Application\Command\CreateSeller;

use Brick\Money\Money;
use Example\Seller\Domain\Seller;
use Example\Seller\Infrastructure\Persistence\StorageInterface;

readonly class CreateSellerCommandHandler
{
    public function __construct(private StorageInterface $storage)
    {
    }

    public function handle(CreateSellerCommand $createSellerCommand): string
    {
        // It may be overkill to create a command object in this example project, but I much prefer strongly typed parameters
        $seller = Seller::create(
            name: $createSellerCommand->getName(),
            description: $createSellerCommand->getDescription(),
            isActive: $createSellerCommand->isActive(),
            soldProductType: $createSellerCommand->getSoldProductTypes(),
            payoutAmount: Money::ofMinor(
                minorAmount: $createSellerCommand->getPayoutAmount(),
                currency: $createSellerCommand->getPayoutCurrency(),
            ),
        );

        // could return the saved entity or just the ID. dealer's choice. going with ID so it's more CQRS-like
        return $this->storage->save(item: $seller);
    }
}
