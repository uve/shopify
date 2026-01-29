<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_index_redirects_when_cart_empty(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_index_displays_form_when_cart_has_items(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Create cart for user
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee('Shipping Information');
    }

    public function test_checkout_store_creates_order(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'price' => 50.00,
        ]);

        // Create cart for user
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_phone' => '555-1234',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_state' => 'NY',
            'shipping_zip' => '10001',
            'shipping_country' => 'US',
            'billing_same_as_shipping' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'status' => Order::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_checkout_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Create cart for user
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), []);

        $response->assertSessionHasErrors([
            'shipping_name',
            'shipping_email',
            'shipping_address',
            'shipping_city',
            'shipping_state',
            'shipping_zip',
            'shipping_country',
        ]);
    }

    public function test_checkout_store_validates_email_format(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Create cart for user
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'not-an-email',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_state' => 'NY',
            'shipping_zip' => '10001',
            'shipping_country' => 'US',
        ]);

        $response->assertSessionHasErrors(['shipping_email']);
    }

    public function test_checkout_store_decrements_product_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 10,
        ]);

        // Create cart for user
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => $product->price,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'John Doe',
            'shipping_email' => 'john@example.com',
            'shipping_address' => '123 Main St',
            'shipping_city' => 'New York',
            'shipping_state' => 'NY',
            'shipping_zip' => '10001',
            'shipping_country' => 'US',
        ]);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_checkout_success_displays_order_confirmation(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-TEST-123']);

        $response = $this->get(route('checkout.success', 'ORD-TEST-123'));

        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-123');
        $response->assertSee('Thank You');
    }

    public function test_checkout_success_returns_404_for_nonexistent_order(): void
    {
        $response = $this->get(route('checkout.success', 'ORD-NONEXISTENT'));

        $response->assertStatus(404);
    }
}
