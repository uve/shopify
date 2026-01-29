<?php

declare(strict_types=1);

namespace App\DTOs\Shopify;

use Illuminate\Support\Collection;

final readonly class ShopifyProductDTO
{
    public function __construct(
        public ?int $id,
        public string $title,
        public ?string $bodyHtml = null,
        public ?string $productType = null,
        public string $status = 'active',
        public ?string $handle = null,
        public Collection $variants = new Collection(),
        public Collection $images = new Collection(),
    ) {}

    public static function fromArray(array $data): self
    {
        $variants = collect($data['variants'] ?? [])
            ->map(fn(array $v) => ShopifyVariantDTO::fromArray($v));

        $images = collect($data['images'] ?? [])
            ->map(fn(array $i) => ShopifyImageDTO::fromArray($i));

        return new self(
            id: $data['id'] ?? null,
            title: $data['title'] ?? '',
            bodyHtml: $data['body_html'] ?? null,
            productType: $data['product_type'] ?? null,
            status: $data['status'] ?? 'active',
            handle: $data['handle'] ?? null,
            variants: $variants,
            images: $images,
        );
    }

    public function getDefaultVariant(): ?ShopifyVariantDTO
    {
        return $this->variants->first();
    }

    public function getMainImage(): ?ShopifyImageDTO
    {
        return $this->images->first();
    }
}
