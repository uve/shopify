<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_generates_unique_order_number(): void
    {
        $orderNumber1 = Order::generateOrderNumber();
        $orderNumber2 = Order::generateOrderNumber();

        $this->assertStringStartsWith('ORD-', $orderNumber1);
        $this->assertNotEquals($orderNumber1, $orderNumber2);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->items);
    }

    public function test_is_pending_returns_true_for_pending_status(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $this->assertTrue($order->isPending());
    }

    public function test_is_pending_returns_false_for_other_statuses(): void
    {
        $order = Order::factory()->processing()->create();

        $this->assertFalse($order->isPending());
    }

    public function test_can_be_cancelled_when_pending(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $this->assertTrue($order->canBeCancelled());
    }

    public function test_can_be_cancelled_when_processing(): void
    {
        $order = Order::factory()->processing()->create();

        $this->assertTrue($order->canBeCancelled());
    }

    public function test_cannot_be_cancelled_when_shipped(): void
    {
        $order = Order::factory()->shipped()->create();

        $this->assertFalse($order->canBeCancelled());
    }

    public function test_cancel_changes_status_to_cancelled(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $order->cancel();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_cancel_throws_exception_when_already_shipped(): void
    {
        $order = Order::factory()->shipped()->create();

        $this->expectException(\InvalidArgumentException::class);

        $order->cancel();
    }

    public function test_mark_as_processing_changes_status(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_PENDING]);

        $order->markAsProcessing();

        $this->assertEquals(Order::STATUS_PROCESSING, $order->fresh()->status);
    }

    public function test_mark_as_shipped_changes_status(): void
    {
        $order = Order::factory()->processing()->create();

        $order->markAsShipped();

        $this->assertEquals(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_mark_as_delivered_changes_status(): void
    {
        $order = Order::factory()->shipped()->create();

        $order->markAsDelivered();

        $this->assertEquals(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_order_casts_monetary_values_to_decimal(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 99.99,
            'tax' => 10.00,
            'total' => 109.99,
        ]);

        $this->assertEquals('99.99', $order->subtotal);
        $this->assertEquals('10.00', $order->tax);
        $this->assertEquals('109.99', $order->total);
    }
}
