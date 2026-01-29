<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_is_in_stock_when_quantity_greater_than_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->assertTrue($product->isInStock());
    }

    public function test_product_is_not_in_stock_when_quantity_is_zero(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->assertFalse($product->isInStock());
    }

    public function test_decrement_stock_reduces_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $product->decrementStock(3);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_decrement_stock_throws_exception_when_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $product->decrementStock(10);
    }

    public function test_product_casts_price_to_decimal(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);

        $this->assertEquals('99.99', $product->price);
    }

    public function test_product_casts_images_to_array(): void
    {
        $images = ['image1.jpg', 'image2.jpg'];
        $product = Product::factory()->create(['images' => $images]);

        $this->assertIsArray($product->images);
        $this->assertEquals($images, $product->images);
    }

    public function test_is_synced_with_shopify(): void
    {
        $product = Product::factory()->create(['shopify_product_id' => null]);
        $this->assertFalse($product->isSyncedWithShopify());

        $product = Product::factory()->create(['shopify_product_id' => 12345]);
        $this->assertTrue($product->isSyncedWithShopify());
    }
}
