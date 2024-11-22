<?php

declare(strict_types=1);

namespace Tests\Seller\Application;

use Example\Seller\Application\Command\CreateSeller\CreateSellerCommand;
use Example\Seller\Application\Command\CreateSeller\CreateSellerCommandHandler;
use Example\Seller\Domain\Seller;
use Example\Seller\Infrastructure\Persistence\StorageInterface;
use PHPUnit\Framework\TestCase;
use Tests\Traits\UsesContainer;

class CreateSellerCommandHandlerTest extends TestCase
{
    use UsesContainer;

    public function test_create_stores_seller_and_returns_id(): void
    {
        /** @var StorageInterface $storage */
        $storage = $this->app->getContainer()->get(StorageInterface::class);
        $handler = new CreateSellerCommandHandler($storage);
        $sellerId = $handler->handle(new CreateSellerCommand(
            name: 'test name',
            description: 'test description',
            isActive: true,
            soldProductTypes: 'digital',
            payoutAmount: 500000, // 5000.00
            payoutCurrency: 'GBP',
        ));

        $seller = $storage->findSellerById($sellerId);

        $this->assertInstanceOf(Seller::class, $seller);
        $this->assertEquals('test name', $seller->getName());
        $this->assertEquals('test description', $seller->getDescription());
        $this->assertTrue($seller->isActive());
        $this->assertEquals('digital', $seller->getSoldProductType()->value);
        $this->assertEquals('GBP', $seller->getPayoutAmount()->getCurrency()->getCurrencyCode());
        $this->assertEquals(500000, $seller->getPayoutAmount()->getMinorAmount()->toInt());
    }
}