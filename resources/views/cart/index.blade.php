@extends('layouts.app')

@section('title', 'Shopping Cart - Clothing Shop')

@section('content')
<h1>Shopping Cart</h1>

@if($cart->isEmpty())
<div style="text-align: center; padding: 60px 0;">
    <p style="font-size: 4rem;">🛒</p>
    <h2>Your cart is empty</h2>
    <p style="margin: 20px 0;">Looks like you haven't added anything to your cart yet.</p>
    <a href="{{ route('products.index') }}" class="btn">Browse Products</a>
</div>
@else
<div class="row">
    <div style="flex: 2; padding-right: 30px;">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart->items as $item)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                <span>👕</span>
                            </div>
                            <div>
                                <strong>{{ $item->product?->name ?? 'Product Unavailable' }}</strong>
                                @if($item->product?->size || $item->product?->color)
                                <br><small>{{ $item->product?->size }} {{ $item->product?->color }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>
                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST" style="display: flex; gap: 5px;">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99" style="width: 70px;">
                            <button type="submit" class="btn" style="padding: 5px 10px;">Update</button>
                        </form>
                    </td>
                    <td><strong>${{ number_format($item->getTotal(), 2) }}</strong></td>
                    <td>
                        <form action="{{ route('cart.remove', $item->product_id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px;">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <form action="{{ route('cart.clear') }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Clear Cart</button>
            </form>
            <a href="{{ route('products.index') }}" class="btn" style="margin-left: 10px;">Continue Shopping</a>
        </div>
    </div>
    
    <div style="flex: 1;">
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h3>Order Summary</h3>
            <div style="margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Items ({{ $summary['total_items'] }})</span>
                    <span>${{ number_format($summary['subtotal'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <hr>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-top: 10px;">
                    <span>Subtotal</span>
                    <span>${{ number_format($summary['subtotal'], 2) }}</span>
                </div>
            </div>
            <a href="{{ route('checkout.index') }}" class="btn" style="width: 100%; text-align: center; padding: 15px;">Proceed to Checkout</a>
        </div>
    </div>
</div>
@endif
@endsection
