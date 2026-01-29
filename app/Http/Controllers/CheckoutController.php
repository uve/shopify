<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $summary = $this->cartService->getCartSummary($cart);
        $tax = $this->orderService->calculateTax($summary['subtotal']);
        $shipping = $this->orderService->calculateShipping($cart);
        $total = $summary['subtotal'] + $tax + $shipping;

        return view('checkout.index', compact('cart', 'summary', 'tax', 'shipping', 'total'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart(
            $request->user(),
            $request->session()->getId()
        );

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        try {
            $order = $this->orderService->createOrderFromCart(
                $cart,
                $request->shippingData(),
                $request->billingData()
            );

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    public function success(string $orderNumber): View
    {
        $order = $this->orderService->getOrderByNumber($orderNumber);

        if (!$order) {
            abort(404);
        }

        return view('checkout.success', compact('order'));
    }
}
