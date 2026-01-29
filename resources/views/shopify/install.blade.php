<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Shopify Store</title>
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
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        form { display: flex; flex-direction: column; gap: 16px; }
        label {
            font-weight: 500;
            color: #202223;
            font-size: 14px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #c9cccf;
            border-radius: 6px;
            font-size: 16px;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #5c6ac4;
            box-shadow: 0 0 0 1px #5c6ac4;
        }
        button {
            background: #5c6ac4;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #4959bd; }
        .hint {
            font-size: 12px;
            color: #8c9196;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Connect Your Store</h1>
        <p>Enter your Shopify store domain to get started.</p>

        @if(isset($error) && $error)
            <div class="error">{{ $error }}</div>
        @endif

        <form action="{{ route('shopify.oauth.install') }}" method="GET">
            <div>
                <label for="shop">Store Domain</label>
                <input 
                    type="text" 
                    name="shop" 
                    id="shop" 
                    placeholder="your-store.myshopify.com"
                    value="{{ request('shop') }}"
                    required
                >
                <p class="hint">Enter your .myshopify.com domain</p>
            </div>
            <button type="submit">Connect Store</button>
        </form>
    </div>
</body>
</html>
