<?php

declare(strict_types=1);

namespace Example\Seller\Domain;

use Brick\Money\Money;
use Example\Seller\Domain\ProductType;

readonly class Seller implements \JsonSerializable
{
    private ?string $id; // Could be a UUID value object but let's not overcomplicate

    private string $name;

    private string $description;

    private bool $isActive;

    private \DateTimeImmutable $createdAt;

    private ProductType $soldProductType; // ProductType value object / Enum
    private Money $payoutAmount; // Money value object that contains currency

    private function __construct(string $id = null, \DateTimeImmutable $createdAt = null)
    {
        $this->id = $id;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public static function create(
        string $name,
        string $description,
        bool $isActive,
        string $soldProductType, // Could probably change this to take the ProductType enum instead of string?
        Money $payoutAmount,
    ): self {
        $seller = new self();
        $seller->setName($name);
        $seller->setDescription($description);
        $seller->setActive($isActive);
        $seller->setSoldProductType($soldProductType);
        $seller->setPayoutAmount($payoutAmount);

        return $seller;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSoldProductType(): ProductType
    {
        return $this->soldProductType;
    }

    public function getPayoutAmount(): Money
    {
        return $this->payoutAmount;
    }

    // quick and dirty data mapper methods in place of an ORM for storage
    public function jsonSerialize(): mixed
    {
        return [
          'id' => $this->getId(),
          'name' => $this->getName(),
          'description' => $this->getDescription(),
          'is_active' => $this->isActive(),
          'created_at' => $this->getCreatedAt()->format(DATE_ISO8601_EXPANDED),
          'sold_product_type' => $this->getSoldProductType()->value,
          'payout_amount' => $this->getPayoutAmount()->getMinorAmount()->toInt(),
          'payout_currency' => $this->getPayoutAmount()->getCurrency()->jsonSerialize(),
        ];
    }

    private function setName(string $name): void
    {
        if (strlen($name) > 255) {
            throw new \DomainException('Seller name is too long.');
        }

        $this->name = $name;
    }

    private function setDescription(string $description): void
    {
        if (strlen($description) > 255) {
            throw new \DomainException('Seller description is too long.');
        }

        $this->description = $description;
    }

    private function setActive(bool $isActive): void
    {
        // some sort of validation before a seller is set active - only certain product types?
        $this->isActive = $isActive;
    }

    private function setSoldProductType(string $soldProductType): void
    {
        if (ProductType::tryFrom($soldProductType) == null) {
            throw new \DomainException('Sold product type is invalid.');
        }

        $this->soldProductType = ProductType::from($soldProductType);
    }

    // Validate the VO before setting, doing it here keeps the rules on what's specifically allowed in the Seller entity only
    private function setPayoutAmount(Money $payoutAmount): void
    {
        // Min payout is 10000 cents ($100.00, 100 pounds, 100 euros etc.)
        if ($payoutAmount->getMinorAmount()->toInt() < 10000) {
            throw new \DomainException('Payout below minimum');
        }

        // Only allow payouts to be accepted in GBP for now
        if ($payoutAmount->getCurrency()->getCurrencyCode() !== 'GBP') {
            throw new \DomainException('Currency not supported');
        }

        $this->payoutAmount = $payoutAmount;
    }

    public static function hydrate(array $seller): self
    {
        $self = new self(id: $seller['id'], createdAt: new \DateTimeImmutable($seller['created_at']));
        $self->setName($seller['name']);
        $self->setDescription($seller['description']);
        $self->setActive($seller['is_active']);
        $self->setSoldProductType($seller['sold_product_type']);
        $self->setPayoutAmount(Money::ofMinor($seller['payout_amount'], $seller['payout_currency']));

        return $self;
    }
}
