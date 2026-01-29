@extends('layouts.app')

@section('title', 'Products - Clothing Shop')

@section('content')
<h1>Products</h1>

<div style="margin: 20px 0;">
    <form action="{{ route('products.index') }}" method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search products..." style="flex: 1;">
        <button type="submit" class="btn">Search</button>
    </form>
</div>

<div class="row">
    <div style="width: 200px; padding-right: 20px;">
        <h3>Categories</h3>
        <ul style="list-style: none; margin-top: 10px;">
            <li style="margin-bottom: 8px;">
                <a href="{{ route('products.index') }}" style="color: inherit;">All Products</a>
            </li>
            @foreach($categories as $category)
            <li style="margin-bottom: 8px;">
                <a href="{{ route('products.category', $category->slug) }}" style="color: inherit;">
                    {{ $category->name }} ({{ $category->products_count }})
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    
    <div style="flex: 1;">
        @if($search)
            <p style="margin-bottom: 20px;">Showing results for: <strong>{{ $search }}</strong></p>
        @endif
        
        @if($products->count() > 0)
        <div class="grid">
            @foreach($products as $product)
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
                    <p style="margin: 10px 0;">
                        @if($product->isInStock())
                            <span style="color: green;">In Stock</span>
                        @else
                            <span style="color: red;">Out of Stock</span>
                        @endif
                    </p>
                    <div style="display: flex; gap: 10px;">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn" style="flex: 1; text-align: center;">View</a>
                        @if($product->isInStock())
                        <form action="{{ route('cart.add', $product) }}" method="POST" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%;">Add</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div style="margin-top: 20px;">
            {{ $products->links() }}
        </div>
        @else
        <p>No products found.</p>
        @endif
    </div>
</div>
@endsection
