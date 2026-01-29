<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Shopify\ShopifyClientInterface;
use App\Contracts\Shopify\ShopifyOAuthServiceInterface;
use App\Services\Shopify\ShopifyApiClient;
use App\Services\Shopify\ShopifyOAuthService;
use Illuminate\Support\ServiceProvider;

class ShopifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ShopifyClientInterface::class, ShopifyApiClient::class);
        $this->app->bind(ShopifyOAuthServiceInterface::class, ShopifyOAuthService::class);
    }

    public function boot(): void {}
}
