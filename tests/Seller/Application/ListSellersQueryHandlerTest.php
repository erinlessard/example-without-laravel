<?php

declare(strict_types=1);

namespace Tests\Seller\Application;

use Example\Seller\Application\Query\ListSellers\ListSellersQuery;
use Example\Seller\Application\Query\ListSellers\ListSellersQueryHandler;
use Example\Seller\Infrastructure\Persistence\StorageInterface;
use PHPUnit\Framework\TestCase;
use Tests\Traits\CanSeedSellers;
use Tests\Traits\UsesContainer;

class ListSellersQueryHandlerTest extends TestCase
{
    use UsesContainer;
    use CanSeedSellers;

    public function test_list_sellers_query_returns_all_sellers(): void
    {
        /** @var StorageInterface $storage */
        $storage = $this->app->getContainer()->get(StorageInterface::class);
        // seed 5 sellers into storage
        for ($i = 0; $i < 5; ++$i) {
            $this->seedSeller($storage);
        }

        $handler = new ListSellersQueryHandler($storage);

        $sellers = $handler->handle(new ListSellersQuery());
        // technically it's been sent over the wire so it should of been json encoded/decoded in a real use case
        $sellers = json_decode(json_encode($sellers), true);
        $sellersFromStorage = $storage->findAllSellers();

        // Honestly, not a huge fan of doing this in a test, I prefer to hardcode things so the test will fail if -anything- changes
        // but this is a quick fun example project :)
        foreach ($sellers as $sellerFromDto) {
            $this->assertArrayHasKey($sellerFromDto['id'], $sellersFromStorage);
            $sellerFromStorage = $sellersFromStorage[$sellerFromDto['id']];
            $this->assertEquals($sellerFromDto['id'], $sellerFromStorage->getId());
            $this->assertEquals($sellerFromDto['name'], $sellerFromStorage->getName());
            $this->assertEquals($sellerFromDto['description'], $sellerFromStorage->getDescription());
            $this->assertEquals($sellerFromDto['created_at'], $sellerFromStorage->getCreatedAt()->format(DATE_ISO8601_EXPANDED));
            $this->assertEquals($sellerFromDto['is_active'], $sellerFromStorage->isActive());
            $this->assertEquals($sellerFromDto['sold_product_type'], $sellerFromStorage->getSoldProductType()->value);
            $this->assertEquals($sellerFromDto['payout_amount'], $sellerFromStorage->getPayoutAmount()->getMinorAmount()->toInt());
            $this->assertEquals($sellerFromDto['payout_currency'], $sellerFromStorage->getPayoutAmount()->getCurrency()->getCurrencyCode());
        }
        $this->assertCount(5, $sellers);
    }

    public function test_list_sellers_query_returns_empty_array_if_no_sellers_found(): void
    {
        $handler = $this->app->getContainer()->get(ListSellersQueryHandler::class);
        $dto = $handler->handle(new ListSellersQuery());
        $dto = json_decode(json_encode($dto), true);
        $this->assertEmpty($dto);
    }
}
