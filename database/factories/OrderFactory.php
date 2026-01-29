<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $tax = round($subtotal * 0.1, 2);
        $shippingCost = fake()->randomFloat(2, 5, 25);
        $discount = fake()->boolean(20) ? fake()->randomFloat(2, 5, 50) : 0;
        $total = $subtotal + $tax + $shippingCost - $discount;

        return [
            'user_id' => null,
            'order_number' => Order::generateOrderNumber(),
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $total,
            'currency' => 'USD',
            'shipping_name' => fake()->name(),
            'shipping_email' => fake()->email(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->stateAbbr(),
            'shipping_zip' => fake()->postcode(),
            'shipping_country' => 'US',
            'billing_name' => fake()->name(),
            'billing_address' => fake()->streetAddress(),
            'billing_city' => fake()->city(),
            'billing_state' => fake()->stateAbbr(),
            'billing_zip' => fake()->postcode(),
            'billing_country' => 'US',
            'notes' => null,
            'shopify_id' => null,
        ];
    }

    public function forUser(User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PROCESSING,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_SHIPPED,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DELIVERED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_CANCELLED,
        ]);
    }
}
