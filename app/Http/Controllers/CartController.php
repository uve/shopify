<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(Request $request): View
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        $summary = $this->cartService->getCartSummary($cart);

        return view('cart.index', compact('cart', 'summary'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:99',
        ]);

        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        try {
            $this->cartService->addToCart(
                $cart,
                $product,
                $validated['quantity'] ?? 1
            );

            return redirect()->route('cart.index')
                ->with('success', "{$product->name} added to cart!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        try {
            $this->cartService->updateQuantity(
                $cart,
                $product,
                $validated['quantity']
            );

            if ($validated['quantity'] === 0) {
                return redirect()->route('cart.index')
                    ->with('success', 'Item removed from cart.');
            }

            return redirect()->route('cart.index')
                ->with('success', 'Cart updated.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        $this->cartService->removeFromCart($cart, $product);

        return redirect()->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        $this->cartService->clearCart($cart);

        return redirect()->route('cart.index')
            ->with('success', 'Cart cleared.');
    }
}
