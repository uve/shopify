@extends('layouts.app')

@section('title', 'Order Confirmed - Clothing Shop')

@section('content')
<div style="text-align: center; padding: 40px 0;">
    <p style="font-size: 4rem;">✅</p>
    <h1>Thank You for Your Order!</h1>
    <p style="font-size: 1.2rem; margin: 20px 0;">Your order has been placed successfully.</p>
    
    <div style="background: #f8f9fa; padding: 30px; border-radius: 8px; max-width: 600px; margin: 30px auto; text-align: left;">
        <h2 style="margin-bottom: 20px;">Order #{{ $order->order_number }}</h2>
        
        <div class="row">
            <div class="col">
                <h4>Shipping Address</h4>
                <p>
                    {{ $order->shipping_name }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
            <div class="col">
                <h4>Contact</h4>
                <p>
                    {{ $order->shipping_email }}<br>
                    {{ $order->shipping_phone ?? 'N/A' }}
                </p>
            </div>
        </div>
        
        <hr style="margin: 20px 0;">
        
        <h4>Order Items</h4>
        <table style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px; text-align: right;">
            <p>Subtotal: ${{ number_format($order->subtotal, 2) }}</p>
            <p>Tax: ${{ number_format($order->tax, 2) }}</p>
            <p>Shipping: ${{ number_format($order->shipping_cost, 2) }}</p>
            @if($order->discount > 0)
            <p>Discount: -${{ number_format($order->discount, 2) }}</p>
            @endif
            <p style="font-size: 1.3rem; font-weight: bold;">Total: ${{ number_format($order->total, 2) }}</p>
        </div>
    </div>
    
    <a href="{{ route('products.index') }}" class="btn">Continue Shopping</a>
</div>
@endsection
