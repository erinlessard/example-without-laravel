<?php

declare(strict_types=1);

namespace Tests\Seller\Application;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use Example\Seller\Application\Query\ShowSeller\ShowSellerDTO;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQuery;
use Example\Seller\Application\Query\ShowSeller\ShowSellerQueryHandler;
use Example\Seller\Domain\Seller;
use Example\Seller\Infrastructure\Persistence\StorageInterface;
use PHPUnit\Framework\TestCase;
use Tests\Traits\UsesContainer;

class SellerQueryHandlerTest extends TestCase
{
    use UsesContainer;

    public function test_show_returns_seller_if_found(): void
    {
        $storage = $this->app->getContainer()->get(StorageInterface::class);
        $sellerId = $this->seedSeller($storage);
        $handler = new ShowSellerQueryHandler($storage);
        $dto = $handler->handle(new ShowSellerQuery(sellerId: $sellerId));

        /** @var Seller $sellerFromStorage */
        $sellerFromStorage = $storage->findSellerById($sellerId);
        $this->assertInstanceOf(ShowSellerDTO::class, $dto);

        // technically it's been sent over the wire so it should of been json encoded/decoded in a real use case
        $sellerFromDto = json_decode(json_encode($dto), true);
        $this->assertEquals($sellerFromDto['id'], $sellerFromStorage->getId());
        $this->assertEquals($sellerFromDto['name'], $sellerFromStorage->getName());
        $this->assertEquals($sellerFromDto['description'], $sellerFromStorage->getDescription());
        $this->assertEquals($sellerFromDto['created_at'], $sellerFromStorage->getCreatedAt()->format(DATE_ISO8601_EXPANDED));
        $this->assertEquals($sellerFromDto['is_active'], $sellerFromStorage->isActive());
        $this->assertEquals($sellerFromDto['sold_product_type'], $sellerFromStorage->getSoldProductType()->value);
        $this->assertEquals($sellerFromDto['payout_amount'], $sellerFromStorage->getPayoutAmount()->getMinorAmount()->toInt());
        $this->assertEquals($sellerFromDto['payout_currency'], $sellerFromStorage->getPayoutAmount()->getCurrency()->getCurrencyCode());
    }

    public function test_show_returns_null_if_seller_not_found(): void
    {
        $handler = $this->app->getContainer()->get(ShowSellerQueryHandler::class);
        $this->assertNull($handler->handle(new ShowSellerQuery('fake-id-that-doesnt-exist')));
    }

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