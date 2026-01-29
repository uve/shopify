@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="row">
    <div class="col">
        <div style="height: 400px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
            @if($product->image)
                <img src="{{ $product->image }}" alt="{{ $product->name }}" style="max-height: 100%; max-width: 100%;">
            @else
                <span style="font-size: 6rem;">👕</span>
            @endif
        </div>
    </div>
    
    <div class="col">
        <nav style="margin-bottom: 20px;">
            <a href="{{ route('products.index') }}">Products</a> &gt;
            {{ $product->name }}
        </nav>
        
        <h1>{{ $product->name }}</h1>
        
        <p class="price" style="font-size: 2rem; margin: 20px 0;">
            ${{ number_format($product->price, 2) }}
        </p>
        
        <div style="margin: 20px 0;">
            @if($product->isInStock())
                <span style="color: green; font-weight: bold;">✓ In Stock</span>
            @else
                <span style="color: red; font-weight: bold;">✗ Out of Stock</span>
            @endif
        </div>
        
        @if($product->description)
        <div style="margin: 20px 0;">
            <h3>Description</h3>
            <p>{{ $product->description }}</p>
        </div>
        @endif
        
        @if($product->isInStock())
        <form action="{{ route('cart.add', $product) }}" method="POST" style="margin-top: 30px;">
            @csrf
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" style="width: 100px;">
            </div>
            <button type="submit" class="btn" style="padding: 15px 40px; font-size: 1.1rem;">Add to Cart</button>
        </form>
        @endif
    </div>
</div>

@if($relatedProducts->count() > 0)
<section class="mt-2">
    <h2>Related Products</h2>
    <div class="grid" style="margin-top: 1rem;">
        @foreach($relatedProducts as $related)
        <div class="card">
            <div style="height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                @if($related->image)
                    <img src="{{ $related->image }}" alt="{{ $related->name }}" style="max-height: 100%; max-width: 100%;">
                @else
                    <span style="font-size: 3rem;">👕</span>
                @endif
            </div>
            <div class="card-body">
                <h3>{{ $related->name }}</h3>
                <p class="price">${{ number_format($related->price, 2) }}</p>
                <a href="{{ route('products.show', $related) }}" class="btn">View</a>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif
@endsection
