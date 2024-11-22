<?php

declare(strict_types=1);

namespace Tests\Traits;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use Example\Seller\Infrastructure\Persistence\StorageInterface;

trait CanSeedSellers
{
    private function seedSeller(StorageInterface $storage): string
    {
        $createSellerHandler = new CreateSellerCommandHandler($storage);
        $faker = \Faker\Factory::create();

        return $createSellerHandler->handle(new CreateSellerCommand(
            name: $faker->name(),
            description: $faker->sentence(),
            isActive: $faker->boolean(),
            soldProductTypes: 'digital',
            payoutAmount: $faker->randomNumber(8),
            payoutCurrency: 'GBP',
        ));
    }
}
