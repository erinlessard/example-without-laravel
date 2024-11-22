<?php

declare(strict_types=1);

namespace Example\Seller\Infrastructure\Persistence;

use Example\Seller\Domain\Seller;

interface StorageInterface
{
    public function save(Seller $item): string;

    public function findSellerById(string $id): ?Seller;

    /**
     * @return Seller[]
     */
    public function findAllSellers(): array;

    public function exists(string $id): bool;
}