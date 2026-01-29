<?php

declare(strict_types=1);

namespace App\Services\Shopify;

use App\Contracts\Shopify\ShopifyOAuthServiceInterface;
use App\Models\ShopifyStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ShopifyOAuthService implements ShopifyOAuthServiceInterface
{
    private string $apiKey;
    private string $apiSecret;
    private string $scopes;
    private string $redirectUri;

    public function __construct()
    {
        $this->apiKey = config('shopify.oauth.api_key', '');
        $this->apiSecret = config('shopify.oauth.api_secret', '');
        $this->scopes = config('shopify.oauth.scopes', 'read_products,write_products');
        $this->redirectUri = config('shopify.oauth.redirect_uri', '');
    }

    public function getAuthorizationUrl(string $shopDomain): string
    {
        $shopDomain = $this->normalizeDomain($shopDomain);
        $nonce = Str::random(32);

        Cache::put("shopify_oauth_nonce:{$nonce}", $shopDomain, now()->addMinutes(10));

        $params = [
            'client_id' => $this->apiKey,
            'scope' => $this->scopes,
            'redirect_uri' => $this->redirectUri,
            'state' => $nonce,
        ];

        return "https://{$shopDomain}/admin/oauth/authorize?" . http_build_query($params);
    }

    public function exchangeCodeForToken(string $shopDomain, string $code): ShopifyStore
    {
        $shopDomain = $this->normalizeDomain($shopDomain);

        $response = Http::post("https://{$shopDomain}/admin/oauth/access_token", [
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            Log::error('OAuth failed', ['shop' => $shopDomain, 'status' => $response->status()]);
            throw new RuntimeException('Failed to exchange authorization code');
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;

        if (!$accessToken) {
            throw new RuntimeException('No access token in response');
        }

        $store = ShopifyStore::updateOrCreate(
            ['shop_domain' => $shopDomain],
            [
                'access_token' => $accessToken,
                'scope' => $data['scope'] ?? '',
                'is_active' => true,
                'installed_at' => now(),
            ]
        );

        session()->forget(['shopify_oauth_nonce', 'shopify_oauth_shop']);

        return $store;
    }

    public function validateHmac(array $queryParams): bool
    {
        if (!isset($queryParams['hmac'])) {
            return false;
        }

        $hmac = $queryParams['hmac'];
        unset($queryParams['hmac']);
        ksort($queryParams);

        $calculatedHmac = hash_hmac('sha256', http_build_query($queryParams), $this->apiSecret);

        return hash_equals($calculatedHmac, $hmac);
    }

    public function validateState(string $state): bool
    {
        $cacheKey = "shopify_oauth_nonce:{$state}";
        $storedShop = Cache::get($cacheKey);
        
        if (!$storedShop) {
            return false;
        }

        Cache::forget($cacheKey);
        return true;
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        $domain = strtolower($domain);

        if (!str_ends_with($domain, '.myshopify.com')) {
            $domain .= '.myshopify.com';
        }

        return $domain;
    }

    public function getStoreByDomain(string $shopDomain): ?ShopifyStore
    {
        return ShopifyStore::where('shop_domain', $this->normalizeDomain($shopDomain))
            ->where('is_active', true)
            ->first();
    }

    public function getSessionShop(): ?string
    {
        return session('shopify_oauth_shop');
    }
}
