<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Shopify;

use App\Contracts\Shopify\ShopifyClientInterface;
use App\DTOs\Shopify\ShopifyImageDTO;
use App\DTOs\Shopify\ShopifyProductDTO;
use App\DTOs\Shopify\ShopifyVariantDTO;
use App\Models\Product;
use App\Services\Shopify\ProductSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProductSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_from_shopify_creates_new_products(): void
    {
        $mockClient = Mockery::mock(ShopifyClientInterface::class);
        $mockClient->shouldReceive('getProducts')
            ->once()
            ->andReturn(collect([
                new ShopifyProductDTO(
                    id: 12345,
                    title: 'Test Product',
                    bodyHtml: 'Description',
                    productType: 'T-Shirts',
                    status: 'active',
                    variants: collect([new ShopifyVariantDTO(id: 111, price: '29.99', inventoryQuantity: 50)]),
                    images: collect([new ShopifyImageDTO(src: 'https://example.com/img.jpg')]),
                ),
            ]));

        $service = new ProductSyncService($mockClient);
        $result = $service->syncFromShopify();

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['updated']);
        $this->assertDatabaseHas('products', [
            'shopify_product_id' => 12345,
            'name' => 'Test Product',
        ]);
    }

    public function test_sync_from_shopify_updates_existing_products(): void
    {
        $category = \App\Models\Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'shopify_product_id' => 12345,
            'name' => 'Old Name',
        ]);

        $mockClient = Mockery::mock(ShopifyClientInterface::class);
        $mockClient->shouldReceive('getProducts')
            ->once()
            ->andReturn(collect([
                new ShopifyProductDTO(
                    id: 12345,
                    title: 'New Name',
                    status: 'active',
                    variants: collect([new ShopifyVariantDTO(price: '39.99')]),
                ),
            ]));

        $service = new ProductSyncService($mockClient);
        $result = $service->syncFromShopify();

        $this->assertEquals(0, $result['created']);
        $this->assertEquals(1, $result['updated']);
        $this->assertDatabaseHas('products', [
            'shopify_product_id' => 12345,
            'name' => 'New Name',
        ]);
    }
}
