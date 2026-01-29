@extends('layouts.app')

@section('title', 'Home - Clothing Shop')

@section('content')
<section class="mb-2">
    <h1>Welcome to ClothingShop</h1>
    <p>Your one-stop destination for quality clothing.</p>
</section>

@if($categories->count() > 0)
<section class="mb-2">
    <h2>Shop by Category</h2>
    <div class="grid" style="margin-top: 1rem;">
        @foreach($categories as $category)
        @if($category->slug)
        <a href="{{ route('products.category', $category->slug) }}" class="card" style="text-decoration: none; color: inherit;">
            <div class="card-body">
                <h3>{{ $category->name }}</h3>
                <p>{{ $category->products_count }} products</p>
            </div>
        </a>
        @endif
        @endforeach
    </div>
</section>
@endif

@if($featuredProducts->count() > 0)
<section>
    <h2>Featured Products</h2>
    <div class="grid" style="margin-top: 1rem;">
        @foreach($featuredProducts as $product)
        <div class="card">
            <div style="height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}">
                @else
                    <span style="font-size: 3rem;">👕</span>
                @endif
            </div>
            <div class="card-body">
                <h3>{{ $product->name }}</h3>
                <p class="price">
                    ${{ number_format($product->price, 2) }}
                </p>
                <p style="margin: 10px 0; color: #666;">{{ $product->category?->name }}</p>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('products.show', $product->slug) }}" class="btn" style="flex: 1; text-align: center;">View</a>
                    <form action="{{ route('cart.add', $product) }}" method="POST" style="flex: 1;">
                        @csrf
                        <button type="submit" class="btn" style="width: 100%;">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@else
<section>
    <p>No products available yet. Check back soon!</p>
</section>
@endif
@endsection
