<?php

declare(strict_types=1);

namespace App\DTOs\Shopify;

final readonly class ShopifyImageDTO
{
    public function __construct(
        public ?string $src = null,
        public int $position = 1,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            src: $data['src'] ?? null,
            position: $data['position'] ?? 1,
        );
    }
}
