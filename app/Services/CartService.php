<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            return $this->getOrCreateCartForUser($user);
        }

        if ($sessionId) {
            return $this->getOrCreateCartForSession($sessionId);
        }

        throw new \InvalidArgumentException('Either user or session ID is required');
    }

    private function getOrCreateCartForUser(User $user): Cart
    {
        return Cart::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => Cart::STATUS_ACTIVE,
            ],
            [
                'session_id' => null,
            ]
        );
    }

    private function getOrCreateCartForSession(string $sessionId): Cart
    {
        return Cart::firstOrCreate(
            [
                'session_id' => $sessionId,
                'status' => Cart::STATUS_ACTIVE,
            ],
            [
                'user_id' => null,
            ]
        );
    }

    public function addToCart(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        if (!$product->is_active) {
            throw new \InvalidArgumentException('Product is not available');
        }

        if (!$product->isInStock()) {
            throw new \InvalidArgumentException('Product is out of stock');
        }

        if ($product->stock_quantity < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient stock. Available: {$product->stock_quantity}"
            );
        }

        $item = $cart->addItem($product, $quantity);

        Log::info('Item added to cart', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);

        return $item;
    }

    public function updateQuantity(Cart $cart, Product $product, int $quantity): ?CartItem
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        if ($quantity > 0 && $product->stock_quantity < $quantity) {
            throw new \InvalidArgumentException(
                "Insufficient stock. Available: {$product->stock_quantity}"
            );
        }

        $item = $cart->updateItemQuantity($product, $quantity);

        Log::info('Cart item quantity updated', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'new_quantity' => $quantity,
        ]);

        return $item;
    }

    public function removeFromCart(Cart $cart, Product $product): bool
    {
        $removed = $cart->removeItem($product);

        if ($removed) {
            Log::info('Item removed from cart', [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);
        }

        return $removed;
    }

    public function clearCart(Cart $cart): void
    {
        $cart->clear();

        Log::info('Cart cleared', [
            'cart_id' => $cart->id,
        ]);
    }

    public function mergeGuestCartToUser(string $sessionId, User $user): Cart
    {
        $guestCart = Cart::where('session_id', $sessionId)
            ->where('status', Cart::STATUS_ACTIVE)
            ->first();

        $userCart = $this->getOrCreateCartForUser($user);

        if (!$guestCart) {
            return $userCart;
        }

        foreach ($guestCart->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $item->quantity);
            } else {
                $userCart->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);
            }
        }

        $guestCart->delete();

        Log::info('Guest cart merged to user cart', [
            'session_id' => $sessionId,
            'user_id' => $user->id,
        ]);

        return $userCart->fresh(['items']);
    }

    public function getCartSummary(Cart $cart): array
    {
        $cart->load('items.product');

        return [
            'items' => $cart->items->map(fn (CartItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->getTotal(),
            ]),
            'total_items' => $cart->getTotalQuantity(),
            'subtotal' => $cart->getSubtotal(),
        ];
    }
}
