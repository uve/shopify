@extends('layouts.app')

@section('title', 'Checkout - Clothing Shop')

@section('content')
<h1>Checkout</h1>

<form action="{{ route('checkout.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div style="flex: 2; padding-right: 30px;">
            <h2>Shipping Information</h2>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_name">Full Name *</label>
                        <input type="text" name="shipping_name" id="shipping_name" value="{{ old('shipping_name') }}" required>
                        @error('shipping_name')<small style="color: red;">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_email">Email *</label>
                        <input type="email" name="shipping_email" id="shipping_email" value="{{ old('shipping_email') }}" required>
                        @error('shipping_email')<small style="color: red;">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="shipping_phone">Phone</label>
                <input type="tel" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}">
            </div>
            
            <div class="form-group">
                <label for="shipping_address">Address *</label>
                <input type="text" name="shipping_address" id="shipping_address" value="{{ old('shipping_address') }}" required>
                @error('shipping_address')<small style="color: red;">{{ $message }}</small>@enderror
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_city">City *</label>
                        <input type="text" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_state">State *</label>
                        <input type="text" name="shipping_state" id="shipping_state" value="{{ old('shipping_state') }}" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_zip">ZIP Code *</label>
                        <input type="text" name="shipping_zip" id="shipping_zip" value="{{ old('shipping_zip') }}" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="shipping_country">Country *</label>
                        <select name="shipping_country" id="shipping_country" required>
                            <option value="US" {{ old('shipping_country', 'US') == 'US' ? 'selected' : '' }}>United States</option>
                            <option value="CA" {{ old('shipping_country') == 'CA' ? 'selected' : '' }}>Canada</option>
                            <option value="GB" {{ old('shipping_country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Order Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
            
            <input type="hidden" name="billing_same_as_shipping" value="1">
        </div>
        
        <div style="flex: 1;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; position: sticky; top: 20px;">
                <h3>Order Summary</h3>
                
                <div style="margin: 20px 0; max-height: 200px; overflow-y: auto;">
                    @foreach($cart->items as $item)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <div>
                            <strong>{{ $item->product?->name }}</strong>
                            <br><small>Qty: {{ $item->quantity }}</small>
                        </div>
                        <span>${{ number_format($item->getTotal(), 2) }}</span>
                    </div>
                    @endforeach
                </div>
                
                <div style="margin: 20px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Subtotal</span>
                        <span>${{ number_format($summary['subtotal'], 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Tax (10%)</span>
                        <span>${{ number_format($tax, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Shipping</span>
                        <span>{{ $shipping > 0 ? '$' . number_format($shipping, 2) : 'FREE' }}</span>
                    </div>
                    <hr>
                    <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; margin-top: 10px;">
                        <span>Total</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; padding: 15px; font-size: 1.1rem;">Place Order</button>
                
                <p style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: #666;">
                    <a href="{{ route('cart.index') }}">← Back to Cart</a>
                </p>
            </div>
        </div>
    </div>
</form>
@endsection
