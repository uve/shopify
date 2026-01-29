<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Connected</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .success-icon svg {
            width: 32px;
            height: 32px;
            color: #059669;
        }
        h1 { 
            font-size: 24px;
            margin-bottom: 8px;
            color: #202223;
        }
        p {
            color: #6d7175;
            margin-bottom: 24px;
        }
        .shop-domain {
            background: #f4f6f8;
            padding: 12px 16px;
            border-radius: 6px;
            font-family: monospace;
            color: #202223;
            margin-bottom: 24px;
        }
        a {
            display: inline-block;
            background: #5c6ac4;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
        }
        a:hover { background: #4959bd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1>Store Connected!</h1>
        <p>{{ $message ?? 'Your Shopify store has been successfully connected.' }}</p>
        
        @if($shop)
            <div class="shop-domain">{{ $shop }}</div>
        @endif

        <a href="{{ url('/') }}">Go to Dashboard</a>
    </div>
</body>
</html>
