<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    public function test_create_order_from_cart(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Shirt',
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        $shippingData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'US',
        ];

        $order = $this->orderService->createOrderFromCart($cart, $shippingData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertStringStartsWith('ORD-', $order->order_number);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals('100.00', $order->subtotal);
        $this->assertEquals('John Doe', $order->shipping_name);
        $this->assertCount(1, $order->items);
    }

    public function test_create_order_throws_exception_for_empty_cart(): void
    {
        $cart = Cart::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create order from empty cart');

        $this->orderService->createOrderFromCart($cart, ['name' => 'Test']);
    }

    public function test_create_order_decrements_product_stock(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 25.00,
        ]);

        $shippingData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
        ];

        $this->orderService->createOrderFromCart($cart, $shippingData);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_create_order_marks_cart_as_converted(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $shippingData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
        ];

        $this->orderService->createOrderFromCart($cart, $shippingData);

        $this->assertEquals(Cart::STATUS_CONVERTED, $cart->fresh()->status);
    }

    public function test_calculate_tax(): void
    {
        $tax = $this->orderService->calculateTax(100.00);

        $this->assertEquals(10.00, $tax);
    }

    public function test_calculate_shipping_with_base_rate(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 2,
            'unit_price' => 20.00,
        ]);
        $cart->load('items');

        $shipping = $this->orderService->calculateShipping($cart);

        $this->assertGreaterThan(0, $shipping);
    }

    public function test_calculate_shipping_free_above_threshold(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'quantity' => 1,
            'unit_price' => 150.00,
        ]);
        $cart->load('items');

        $shipping = $this->orderService->calculateShipping($cart);

        $this->assertEquals(0.00, $shipping);
    }

    public function test_get_orders_by_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->forUser($user)->create();
        Order::factory()->create(); // Another user's order

        $orders = $this->orderService->getOrdersByUser($user);

        $this->assertCount(3, $orders);
    }

    public function test_get_order_by_number(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-TEST-123']);

        $found = $this->orderService->getOrderByNumber('ORD-TEST-123');

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_get_order_by_number_returns_null_when_not_found(): void
    {
        $found = $this->orderService->getOrderByNumber('NONEXISTENT');

        $this->assertNull($found);
    }

    public function test_cancel_order(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => 25.00,
            'total_price' => 75.00,
        ]);

        $cancelled = $this->orderService->cancelOrder($order);

        $this->assertEquals(Order::STATUS_CANCELLED, $cancelled->status);
        $this->assertEquals(8, $product->fresh()->stock_quantity);
    }
}
