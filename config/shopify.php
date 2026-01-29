<?php

declare(strict_types=1);

/**
 * Shopify Integration Configuration
 * 
 * All values must be set via environment variables.
 * No defaults for production-critical values.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials for the Shopify OAuth 2.0 flow.
    | Get these from your Shopify Partner Dashboard.
    |
    */
    'oauth' => [
        'api_key' => env('SHOPIFY_API_KEY'),
        'api_secret' => env('SHOPIFY_API_SECRET'),
        'scopes' => env('SHOPIFY_SCOPES', 'read_products,write_products'),
        'redirect_uri' => env('SHOPIFY_REDIRECT_URI', env('APP_URL') . '/shopify/oauth/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shopify Store Domain
    |--------------------------------------------------------------------------
    |
    | Your Shopify store's myshopify.com domain.
    | Example: your-store.myshopify.com
    |
    */
    'store_domain' => env('SHOPIFY_STORE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Admin API Access Token
    |--------------------------------------------------------------------------
    |
    | Direct access token for the Admin API.
    | Generate this in the Shopify admin when you install a custom app.
    |
    */
    'access_token' => env('SHOPIFY_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The Shopify Admin API version to use.
    | See: https://shopify.dev/docs/api/usage/versioning
    |
    */
    'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The shared secret for verifying webhook signatures.
    | Generate this when creating webhooks in Shopify.
    |
    */
    'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Vendor
    |--------------------------------------------------------------------------
    |
    | Default vendor name for products synced to Shopify.
    |
    */
    'default_vendor' => env('SHOPIFY_DEFAULT_VENDOR', env('APP_NAME', 'My Store')),

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for product synchronization behavior.
    |
    */
    'sync' => [
        /*
        | Enable automatic sync via webhooks
        */
        'webhooks_enabled' => env('SHOPIFY_WEBHOOKS_ENABLED', true),

        /*
        | Delete local products when deleted in Shopify
        | When false, products are marked as inactive instead
        */
        'delete_on_shopify_delete' => env('SHOPIFY_DELETE_ON_SHOPIFY_DELETE', false),

        /*
        | Batch size for sync operations
        */
        'batch_size' => env('SHOPIFY_SYNC_BATCH_SIZE', 50),

        /*
        | Enable sync to Shopify (push local changes)
        */
        'push_enabled' => env('SHOPIFY_PUSH_ENABLED', true),

        /*
        | Enable sync from Shopify (pull remote changes)
        */
        'pull_enabled' => env('SHOPIFY_PULL_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to communicate with Shopify.
    |
    */
    'http' => [
        /*
        | Request timeout in seconds
        */
        'timeout' => env('SHOPIFY_HTTP_TIMEOUT', 30),

        /*
        | Maximum retry attempts for failed requests
        */
        'max_retries' => env('SHOPIFY_HTTP_MAX_RETRIES', 3),

        /*
        | Base delay between retries in milliseconds
        */
        'retry_delay' => env('SHOPIFY_HTTP_RETRY_DELAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Shopify-related logging.
    |
    */
    'logging' => [
        /*
        | Log channel for Shopify operations
        */
        'channel' => env('SHOPIFY_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

        /*
        | Enable verbose API request/response logging
        */
        'verbose' => env('SHOPIFY_LOG_VERBOSE', false),
    ],
];
