<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_featured_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('featuredProducts');
        $response->assertViewHas('categories');
    }

    public function test_products_index_displays_all_active_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);
        Product::factory()->inactive()->create(['category_id' => $category->id]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $this->assertCount(5, $response->viewData('products'));
    }

    public function test_products_index_can_search_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Blue Cotton Shirt',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Red Wool Sweater',
        ]);

        $response = $this->get(route('products.index', ['search' => 'cotton']));

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('products'));
    }

    public function test_product_show_displays_product_details(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
        ]);

        $response = $this->get(route('products.show', 'test-product'));

        $response->assertStatus(200);
        $response->assertViewHas('product');
        $response->assertSee('Test Product');
    }

    public function test_product_show_returns_404_for_inactive_product(): void
    {
        $category = Category::factory()->create();
        Product::factory()->inactive()->create([
            'category_id' => $category->id,
            'slug' => 'inactive-product',
        ]);

        $response = $this->get(route('products.show', 'inactive-product'));

        $response->assertStatus(404);
    }

    public function test_product_show_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get(route('products.show', 'nonexistent'));

        $response->assertStatus(404);
    }

    public function test_category_page_displays_products_in_category(): void
    {
        $category1 = Category::factory()->create(['slug' => 'shirts']);
        $category2 = Category::factory()->create(['slug' => 'pants']);
        
        Product::factory()->count(3)->create(['category_id' => $category1->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->get(route('products.category', 'shirts'));

        $response->assertStatus(200);
        $response->assertViewHas('category');
        $response->assertViewHas('products');
        $this->assertCount(3, $response->viewData('products'));
    }

    public function test_category_page_returns_404_for_nonexistent_category(): void
    {
        $response = $this->get(route('products.category', 'nonexistent'));

        $response->assertStatus(404);
    }
}
