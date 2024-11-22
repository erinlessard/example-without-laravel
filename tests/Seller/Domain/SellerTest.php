<?php

declare(strict_types=1);

namespace Tests\Seller\Domain;

use Brick\Money\Money;
use Example\Seller\Domain\ProductType;
use Example\Seller\Domain\Seller;
use PHPUnit\Framework\TestCase;

class SellerTest extends TestCase
{
    public function test_create_valid_seller()
    {
        $seller = Seller::create(
            'Test Seller',
            'This is a test description.',
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(10000, 'GBP'),
        );

        $this->assertInstanceOf(Seller::class, $seller);
        $this->assertEquals('Test Seller', $seller->getName());
        $this->assertEquals('This is a test description.', $seller->getDescription());
        $this->assertTrue($seller->isActive());
        $this->assertEquals(ProductType::DIGITAL_PRODUCTS, $seller->getSoldProductType());
        $this->assertEquals(Money::ofMinor(10000, 'GBP'), $seller->getPayoutAmount());
    }

    public function test_invalid_name_throws_exception()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Seller name is too long.');

        Seller::create(
            str_repeat('a', 256),
            'This is a test description.',
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(10000, 'GBP'),
        );
    }

    public function test_invalid_description_throws_exception()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Seller description is too long.');

        Seller::create(
            'Test Seller',
            str_repeat('a', 256),
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(10000, 'GBP'),
        );
    }

    public function test_invalid_payout_amount_throws_exception()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Payout below minimum');

        Seller::create(
            'Test Seller',
            'This is a test description.',
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(9999, 'GBP'),
        );
    }

    public function test_unsupported_currency_throws_exception()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Currency not supported');

        Seller::create(
            'Test Seller',
            'This is a test description.',
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(10000, 'USD'),
        );
    }

    public function test_invalid_product_type_throws_exception()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Sold product type is invalid.');

        Seller::create(
            'Test Seller',
            'This is a test description.',
            true,
            'goijoij',
            Money::ofMinor(9999, 'GBP'),
        );
    }

    public function test_json_serialize()
    {
        $seller = Seller::create(
            'Test Seller',
            'This is a test description.',
            true,
            ProductType::DIGITAL_PRODUCTS->value,
            Money::ofMinor(10000, 'GBP'),
        );

        $jsonData = $seller->jsonSerialize();

        $this->assertArrayHasKey('id', $jsonData);
        $this->assertArrayHasKey('name', $jsonData);
        $this->assertArrayHasKey('description', $jsonData);
        $this->assertArrayHasKey('is_active', $jsonData);
        $this->assertArrayHasKey('created_at', $jsonData);
        $this->assertArrayHasKey('sold_product_type', $jsonData);
        $this->assertArrayHasKey('payout_amount', $jsonData);
        $this->assertArrayHasKey('payout_currency', $jsonData);

        $this->assertEquals('Test Seller', $jsonData['name']);
        $this->assertEquals('This is a test description.', $jsonData['description']);
        $this->assertTrue($jsonData['is_active']);
        $this->assertEquals(ProductType::DIGITAL_PRODUCTS->value, $jsonData['sold_product_type']);
        $this->assertEquals(10000, $jsonData['payout_amount']);
        $this->assertEquals('GBP', $jsonData['payout_currency']);
    }

    public function test_hydrate()
    {
        $data = [
            'id' => 'test-id',
            'name' => 'Test Seller',
            'description' => 'This is a test description.',
            'is_active' => true,
            'created_at' => '2024-01-01T00:00:00+00:00',
            'sold_product_type' => ProductType::DIGITAL_PRODUCTS->value,
            'payout_amount' => 10000,
            'payout_currency' => 'GBP',
        ];

        $seller = Seller::hydrate($data);

        $this->assertEquals($data['id'], $seller->getId());
        $this->assertEquals($data['name'], $seller->getName());
        $this->assertEquals($data['description'], $seller->getDescription());
        $this->assertTrue($seller->isActive());
        $this->assertEquals($data['sold_product_type'], $seller->getSoldProductType()->value);
        $this->assertEquals($data['payout_amount'], $seller->getPayoutAmount()->getMinorAmount()->toInt());
        $this->assertEquals($data['payout_currency'], $seller->getPayoutAmount()->getCurrency()->getCurrencyCode());
        $this->assertEquals(new \DateTimeImmutable('2024-01-01T00:00:00+00:00'), $seller->getCreatedAt());
    }
}
