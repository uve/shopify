<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clothing Shop')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        header { background: #1a1a2e; color: white; padding: 1rem 0; }
        header nav { display: flex; justify-content: space-between; align-items: center; }
        header a { color: white; text-decoration: none; margin-left: 20px; }
        header a:hover { text-decoration: underline; }
        .logo { font-size: 1.5rem; font-weight: bold; }
        main { padding: 2rem 0; min-height: calc(100vh - 200px); }
        footer { background: #1a1a2e; color: white; padding: 2rem 0; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background: #4a4e69; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #22223b; }
        .btn-danger { background: #c9184a; }
        .btn-danger:hover { background: #a4133c; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; }
        .card img { width: 100%; height: 200px; object-fit: cover; background: #f0f0f0; }
        .card-body { padding: 15px; }
        .card h3 { margin-bottom: 10px; }
        .price { font-size: 1.25rem; font-weight: bold; color: #4a4e69; }
        .price-compare { text-decoration: line-through; color: #999; font-size: 0.9rem; margin-left: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group { margin-bottom: 15px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -10px; }
        .col { flex: 1; padding: 0 10px; min-width: 200px; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 2rem; }
        .mb-2 { margin-bottom: 2rem; }
        /* Pagination styles */
        nav[role="navigation"] { display: flex; justify-content: center; align-items: center; gap: 5px; flex-wrap: wrap; }
        nav[role="navigation"] svg { width: 16px; height: 16px; }
        nav[role="navigation"] a, nav[role="navigation"] span { 
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; 
            text-decoration: none; color: #333; background: white; font-size: 14px;
        }
        nav[role="navigation"] a:hover { background: #f0f0f0; }
        nav[role="navigation"] span[aria-current="page"] span { background: #4a4e69; color: white; border-color: #4a4e69; }
        nav[role="navigation"] span[aria-disabled="true"] { color: #999; cursor: not-allowed; }
        nav[role="navigation"] .hidden { display: none; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="{{ route('home') }}" class="logo">👕 ClothingShop</a>
                <div>
                    <a href="{{ route('products.index') }}">Products</a>
                    <a href="{{ route('cart.index') }}">🛒 Cart</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} ClothingShop. Ready for Shopify integration.</p>
        </div>
    </footer>
</body>
</html>
