@extends('layouts.app')

@section('title', $category->name . ' - Clothing Shop')

@section('content')
<h1>{{ $category->name }}</h1>

@if($category->description)
<p style="margin: 20px 0;">{{ $category->description }}</p>
@endif

<div class="row">
    <div style="width: 200px; padding-right: 20px;">
        <h3>Categories</h3>
        <ul style="list-style: none; margin-top: 10px;">
            <li style="margin-bottom: 8px;">
                <a href="{{ route('products.index') }}" style="color: inherit;">All Products</a>
            </li>
            @foreach($categories as $cat)
            <li style="margin-bottom: 8px;">
                <a href="{{ route('products.category', $cat->slug) }}" 
                   style="color: inherit; {{ $cat->id === $category->id ? 'font-weight: bold;' : '' }}">
                    {{ $cat->name }} ({{ $cat->products_count }})
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    
    <div style="flex: 1;">
        @if($products->count() > 0)
        <div class="grid">
            @foreach($products as $product)
            <div class="card">
                <div style="height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 3rem;">👕</span>
                </div>
                <div class="card-body">
                    <h3>{{ $product->name }}</h3>
                    <p class="price">
                        ${{ number_format($product->price, 2) }}
                    </p>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
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
        <p>No products in this category.</p>
        @endif
    </div>
</div>
@endsection
