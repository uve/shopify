<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrderFromCart(Cart $cart, array $shippingData, array $billingData = []): Order
    {
        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('Cannot create order from empty cart');
        }

        $cart->load('items.product');

        return DB::transaction(function () use ($cart, $shippingData, $billingData) {
            $subtotal = $cart->getSubtotal();
            $tax = $this->calculateTax($subtotal);
            $shippingCost = $this->calculateShipping($cart);
            $total = $subtotal + $tax + $shippingCost;

            $order = Order::create([
                'user_id' => $cart->user_id,
                'order_number' => Order::generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => 0,
                'total' => $total,
                'currency' => 'USD',
                'shipping_name' => $shippingData['name'],
                'shipping_email' => $shippingData['email'],
                'shipping_phone' => $shippingData['phone'] ?? null,
                'shipping_address' => $shippingData['address'],
                'shipping_city' => $shippingData['city'],
                'shipping_state' => $shippingData['state'],
                'shipping_zip' => $shippingData['zip'],
                'shipping_country' => $shippingData['country'] ?? 'US',
                'billing_name' => $billingData['name'] ?? $shippingData['name'],
                'billing_address' => $billingData['address'] ?? $shippingData['address'],
                'billing_city' => $billingData['city'] ?? $shippingData['city'],
                'billing_state' => $billingData['state'] ?? $shippingData['state'],
                'billing_zip' => $billingData['zip'] ?? $shippingData['zip'],
                'billing_country' => $billingData['country'] ?? $shippingData['country'] ?? 'US',
                'notes' => $shippingData['notes'] ?? null,
            ]);

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => null,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'total_price' => $cartItem->getTotal(),
                    'options' => [],
                ]);

                $product->decrementStock($cartItem->quantity);
            }

            $cart->markAsConverted();

            return $order;
        });
    }

    public function calculateTax(float $subtotal): float
    {
        $taxRate = (float) config('shop.tax_rate', 0.10);
        return round($subtotal * $taxRate, 2);
    }

    public function calculateShipping(Cart $cart): float
    {
        $baseRate = (float) config('shop.shipping_base_rate', 5.99);
        $perItemRate = (float) config('shop.shipping_per_item_rate', 1.00);
        $freeShippingThreshold = (float) config('shop.free_shipping_threshold', 100.00);

        $subtotal = $cart->getSubtotal();

        if ($subtotal >= $freeShippingThreshold) {
            return 0.00;
        }

        return round($baseRate + ($cart->getTotalQuantity() * $perItemRate), 2);
    }

    public function getOrdersByUser(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Order::where('user_id', $user->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->first();
    }

    public function cancelOrder(Order $order): Order
    {
        $order->cancel();

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        return $order->fresh();
    }
}
