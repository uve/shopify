<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | The tax rate to apply to orders. Default is 10% (0.10).
    |
    */
    'tax_rate' => env('SHOP_TAX_RATE', 0.10),

    /*
    |--------------------------------------------------------------------------
    | Shipping Rates
    |--------------------------------------------------------------------------
    |
    | Base shipping rate and per-item rate for calculating shipping costs.
    |
    */
    'shipping_base_rate' => env('SHOP_SHIPPING_BASE_RATE', 5.99),
    'shipping_per_item_rate' => env('SHOP_SHIPPING_PER_ITEM_RATE', 1.00),
    'free_shipping_threshold' => env('SHOP_FREE_SHIPPING_THRESHOLD', 100.00),

    /*
    |--------------------------------------------------------------------------
    | Shopify Integration (future)
    |--------------------------------------------------------------------------
    |
    | Configuration for Shopify API integration.
    |
    */
    'shopify' => [
        'api_key' => env('SHOPIFY_API_KEY'),
        'api_secret' => env('SHOPIFY_API_SECRET'),
        'store_url' => env('SHOPIFY_STORE_URL'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),
    ],
];
