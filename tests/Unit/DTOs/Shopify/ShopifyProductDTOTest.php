<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Shopify;

use App\DTOs\Shopify\ShopifyImageDTO;
use App\DTOs\Shopify\ShopifyProductDTO;
use App\DTOs\Shopify\ShopifyVariantDTO;
use PHPUnit\Framework\TestCase;

class ShopifyProductDTOTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $data = [
            'id' => 12345,
            'title' => 'Test Product',
            'body_html' => '<p>Description</p>',
            'product_type' => 'T-Shirts',
            'status' => 'active',
            'handle' => 'test-product',
            'variants' => [
                ['id' => 111, 'price' => '29.99', 'inventory_quantity' => 100],
            ],
            'images' => [
                ['src' => 'https://example.com/image.jpg', 'position' => 1],
            ],
        ];

        $dto = ShopifyProductDTO::fromArray($data);

        $this->assertSame(12345, $dto->id);
        $this->assertSame('Test Product', $dto->title);
        $this->assertSame('<p>Description</p>', $dto->bodyHtml);
        $this->assertSame('T-Shirts', $dto->productType);
        $this->assertSame('active', $dto->status);
        $this->assertCount(1, $dto->variants);
        $this->assertCount(1, $dto->images);
    }

    public function test_gets_default_variant(): void
    {
        $variant = new ShopifyVariantDTO(id: 111, price: '29.99');
        $dto = new ShopifyProductDTO(
            id: 12345,
            title: 'Test',
            variants: collect([$variant]),
        );

        $this->assertSame($variant, $dto->getDefaultVariant());
    }

    public function test_gets_main_image(): void
    {
        $image = new ShopifyImageDTO(src: 'https://example.com/img.jpg');
        $dto = new ShopifyProductDTO(
            id: 12345,
            title: 'Test',
            images: collect([$image]),
        );

        $this->assertSame($image, $dto->getMainImage());
    }
}
