<?php

declare(strict_types=1);

namespace Example\Seller\Infrastructure\Persistence;

use Example\Seller\Domain\Seller;
use Ramsey\Uuid\Uuid;

class InMemoryStorage implements StorageInterface
{
    private array $items = [];

    public function save(Seller $item): string
    {
        $id = Uuid::uuid7()->toString();

        // serialize then rehydrate the object so we can set the ID without ever exposing the property or using a setter
        // Typically an ORM would use Reflection to do this, but let's K.I.S.S
        $itemData = $item->jsonSerialize();
        $itemData['id'] = $id;
        $item = Seller::hydrate($itemData);

        $this->items[$id] = $item;

        return $id;
    }

    public function findSellerById(string $id): ?Seller
    {
        return ($this->exists($id)) ? $this->items[$id] : null;
//        return $this->items[$id];
    }

    public function findAllSellers(): array
    {
        return $this->items;
    }

    public function exists($id): bool
    {
        return array_key_exists($id, $this->items);
    }
}
