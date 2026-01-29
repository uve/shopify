<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    public function test_get_or_create_cart_creates_cart_for_session(): void
    {
        $cart = $this->cartService->getOrCreateCart(null, 'test-session-123');

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals('test-session-123', $cart->session_id);
        $this->assertNull($cart->user_id);
    }

    public function test_get_or_create_cart_creates_cart_for_user(): void
    {
        $user = User::factory()->create();

        $cart = $this->cartService->getOrCreateCart($user);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($user->id, $cart->user_id);
        $this->assertNull($cart->session_id);
    }

    public function test_get_or_create_cart_returns_existing_cart(): void
    {
        $existingCart = Cart::factory()->create(['session_id' => 'test-session']);

        $cart = $this->cartService->getOrCreateCart(null, 'test-session');

        $this->assertEquals($existingCart->id, $cart->id);
    }

    public function test_get_or_create_cart_throws_exception_without_user_or_session(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->cartService->getOrCreateCart(null, null);
    }

    public function test_add_to_cart_creates_cart_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'price' => 29.99,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $item = $this->cartService->addToCart($cart, $product, 2);

        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals('29.99', $item->unit_price);
    }

    public function test_add_to_cart_throws_exception_for_zero_quantity(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero');

        $this->cartService->addToCart($cart, $product, 0);
    }

    public function test_add_to_cart_throws_exception_for_inactive_product(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->inactive()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product is not available');

        $this->cartService->addToCart($cart, $product, 1);
    }

    public function test_add_to_cart_throws_exception_for_out_of_stock_product(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->outOfStock()->create(['is_active' => true]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product is out of stock');

        $this->cartService->addToCart($cart, $product, 1);
    }

    public function test_add_to_cart_throws_exception_for_insufficient_stock(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->cartService->addToCart($cart, $product, 10);
    }

    public function test_update_quantity_modifies_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = $this->cartService->updateQuantity($cart, $product, 5);

        $this->assertEquals(5, $item->quantity);
    }

    public function test_update_quantity_to_zero_removes_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $item = $this->cartService->updateQuantity($cart, $product, 0);

        $this->assertNull($item);
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_remove_from_cart_deletes_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $result = $this->cartService->removeFromCart($cart, $product);

        $this->assertTrue($result);
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_clear_cart_removes_all_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->cartService->clearCart($cart);

        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_merge_guest_cart_to_user(): void
    {
        $user = User::factory()->create();
        $sessionId = 'guest-session-123';
        
        $guestCart = Cart::factory()->create(['session_id' => $sessionId]);
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $guestCart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $userCart = $this->cartService->mergeGuestCartToUser($sessionId, $user);

        $this->assertEquals($user->id, $userCart->user_id);
        $this->assertCount(1, $userCart->items);
        $this->assertEquals(2, $userCart->items->first()->quantity);
        $this->assertNull(Cart::find($guestCart->id));
    }

    public function test_get_cart_summary_returns_correct_data(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product']);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25.00,
        ]);

        $summary = $this->cartService->getCartSummary($cart);

        $this->assertEquals(2, $summary['total_items']);
        $this->assertEquals(50.00, $summary['subtotal']);
        $this->assertCount(1, $summary['items']);
        $this->assertEquals('Test Product', $summary['items']->first()['product_name']);
    }
}
