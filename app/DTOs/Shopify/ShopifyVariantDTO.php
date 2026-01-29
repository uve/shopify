<?php

declare(strict_types=1);

namespace App\DTOs\Shopify;

final readonly class ShopifyVariantDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $price = null,
        public ?int $inventoryQuantity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            price: isset($data['price']) ? (string) $data['price'] : null,
            inventoryQuantity: $data['inventory_quantity'] ?? null,
        );
    }
}
