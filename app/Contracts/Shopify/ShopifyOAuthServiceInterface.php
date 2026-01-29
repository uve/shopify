<?php

declare(strict_types=1);

namespace App\Contracts\Shopify;

use App\Models\ShopifyStore;

interface ShopifyOAuthServiceInterface
{
    public function getAuthorizationUrl(string $shopDomain): string;
    public function exchangeCodeForToken(string $shopDomain, string $code): ShopifyStore;
    public function validateHmac(array $queryParams): bool;
    public function validateState(string $state): bool;
    public function normalizeDomain(string $domain): string;
    public function getStoreByDomain(string $shopDomain): ?ShopifyStore;
}
