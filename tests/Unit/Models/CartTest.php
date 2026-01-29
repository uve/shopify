<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_can_belong_to_user(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals($user->id, $cart->user->id);
    }

    public function test_cart_can_have_session_id_without_user(): void
    {
        $cart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => 'test-session-123',
        ]);

        $this->assertNull($cart->user);
        $this->assertEquals('test-session-123', $cart->session_id);
    }

    public function test_cart_has_many_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->assertCount(3, $cart->items);
    }

    public function test_cart_calculates_subtotal_correctly(): void
    {
        $cart = Cart::factory()->create();
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 2,
            'unit_price' => 25.00,
        ]);
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $cart->load('items');

        $this->assertEquals(100.00, $cart->getSubtotal());
    }

    public function test_cart_calculates_total_quantity(): void
    {
        $cart = Cart::factory()->create();
        
        CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'quantity' => 3]);

        $cart->load('items');

        $this->assertEquals(5, $cart->getTotalQuantity());
    }

    public function test_cart_is_empty_when_no_items(): void
    {
        $cart = Cart::factory()->create();

        $this->assertTrue($cart->isEmpty());
    }

    public function test_cart_is_not_empty_when_has_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->create(['cart_id' => $cart->id]);

        $cart->load('items');

        $this->assertFalse($cart->isEmpty());
    }

    public function test_add_item_creates_new_cart_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price' => 29.99]);

        $item = $cart->addItem($product, 2);

        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals('29.99', $item->unit_price);
    }

    public function test_add_item_increments_existing_item_quantity(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = $cart->addItem($product, 3);

        $this->assertEquals(5, $item->quantity);
        $this->assertCount(1, $cart->fresh()->items);
    }

    public function test_remove_item_deletes_cart_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $result = $cart->removeItem($product);

        $this->assertTrue($result);
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_remove_item_returns_false_when_item_not_found(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();

        $result = $cart->removeItem($product);

        $this->assertFalse($result);
    }

    public function test_update_item_quantity_modifies_quantity(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = $cart->updateItemQuantity($product, 5);

        $this->assertEquals(5, $item->quantity);
    }

    public function test_update_item_quantity_to_zero_deletes_item(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $result = $cart->updateItemQuantity($product, 0);

        $this->assertNull($result);
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_clear_removes_all_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $cart->clear();

        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_mark_as_converted_changes_status(): void
    {
        $cart = Cart::factory()->create(['status' => Cart::STATUS_ACTIVE]);

        $cart->markAsConverted();

        $this->assertEquals(Cart::STATUS_CONVERTED, $cart->fresh()->status);
    }
}
