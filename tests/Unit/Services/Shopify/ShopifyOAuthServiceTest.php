<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Shopify;

use App\Contracts\Shopify\ShopifyOAuthServiceInterface;
use App\Models\ShopifyStore;
use App\Services\Shopify\ShopifyOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopifyOAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShopifyOAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        config([
            'shopify.oauth.api_key' => 'test-api-key',
            'shopify.oauth.api_secret' => 'test-api-secret',
            'shopify.oauth.scopes' => 'read_products,write_products',
            'shopify.oauth.redirect_uri' => 'http://localhost/shopify/oauth/callback',
        ]);

        $this->service = new ShopifyOAuthService();
    }

    public function test_implements_interface(): void
    {
        $this->assertInstanceOf(ShopifyOAuthServiceInterface::class, $this->service);
    }

    public function test_normalizes_domain_without_suffix(): void
    {
        $result = $this->service->normalizeDomain('my-store');
        
        $this->assertEquals('my-store.myshopify.com', $result);
    }

    public function test_normalizes_domain_with_protocol(): void
    {
        $result = $this->service->normalizeDomain('https://my-store.myshopify.com');
        
        $this->assertEquals('my-store.myshopify.com', $result);
    }

    public function test_normalizes_domain_with_trailing_slash(): void
    {
        $result = $this->service->normalizeDomain('my-store.myshopify.com/');
        
        $this->assertEquals('my-store.myshopify.com', $result);
    }

    public function test_normalizes_domain_to_lowercase(): void
    {
        $result = $this->service->normalizeDomain('MY-STORE.MYSHOPIFY.COM');
        
        $this->assertEquals('my-store.myshopify.com', $result);
    }

    public function test_generates_authorization_url(): void
    {
        $url = $this->service->getAuthorizationUrl('test-store.myshopify.com');

        $this->assertStringContainsString('https://test-store.myshopify.com/admin/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=test-api-key', $url);
        $this->assertStringContainsString('scope=read_products', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('state=', $url);
    }

    public function test_stores_nonce_in_cache(): void
    {
        $url = $this->service->getAuthorizationUrl('test-store.myshopify.com');

        // Extract state from URL
        preg_match('/state=([^&]+)/', $url, $matches);
        $state = $matches[1];

        // Verify nonce is stored in cache with shop domain
        $this->assertEquals('test-store.myshopify.com', Cache::get("shopify_oauth_nonce:{$state}"));
    }

    public function test_validates_hmac_with_valid_signature(): void
    {
        $params = [
            'code' => 'test-code',
            'shop' => 'test-store.myshopify.com',
            'timestamp' => '1234567890',
        ];

        ksort($params);
        $queryString = http_build_query($params);
        $hmac = hash_hmac('sha256', $queryString, 'test-api-secret');

        $params['hmac'] = $hmac;

        $this->assertTrue($this->service->validateHmac($params));
    }

    public function test_validates_hmac_with_invalid_signature(): void
    {
        $params = [
            'code' => 'test-code',
            'shop' => 'test-store.myshopify.com',
            'hmac' => 'invalid-hmac',
        ];

        $this->assertFalse($this->service->validateHmac($params));
    }

    public function test_validates_hmac_without_hmac_param(): void
    {
        $params = [
            'code' => 'test-code',
            'shop' => 'test-store.myshopify.com',
        ];

        $this->assertFalse($this->service->validateHmac($params));
    }

    public function test_validates_state_with_valid_nonce(): void
    {
        Cache::put('shopify_oauth_nonce:test-nonce', 'test-store.myshopify.com', now()->addMinutes(10));

        $this->assertTrue($this->service->validateState('test-nonce'));
        
        // Verify nonce was deleted after validation (one-time use)
        $this->assertNull(Cache::get('shopify_oauth_nonce:test-nonce'));
    }

    public function test_validates_state_with_invalid_nonce(): void
    {
        Cache::put('shopify_oauth_nonce:test-nonce', 'test-store.myshopify.com', now()->addMinutes(10));

        $this->assertFalse($this->service->validateState('wrong-nonce'));
    }

    public function test_validates_state_without_cached_nonce(): void
    {
        $this->assertFalse($this->service->validateState('any-nonce'));
    }

    public function test_exchanges_code_for_token(): void
    {
        Http::fake([
            'test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'shpat_test_token_123',
                'scope' => 'read_products,write_products',
            ], 200),
        ]);

        $store = $this->service->exchangeCodeForToken('test-store.myshopify.com', 'test-code');

        $this->assertInstanceOf(ShopifyStore::class, $store);
        $this->assertEquals('test-store.myshopify.com', $store->shop_domain);
        $this->assertEquals('read_products,write_products', $store->scope);
        $this->assertTrue($store->is_active);
        $this->assertNotNull($store->installed_at);
    }

    public function test_exchanges_code_updates_existing_store(): void
    {
        $existingStore = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'old-token',
            'is_active' => false,
        ]);

        Http::fake([
            'test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'new-token',
                'scope' => 'read_products',
            ], 200),
        ]);

        $store = $this->service->exchangeCodeForToken('test-store.myshopify.com', 'test-code');

        $this->assertEquals($existingStore->id, $store->id);
        $this->assertTrue($store->is_active);
        $this->assertEquals('read_products', $store->scope);
    }

    public function test_exchanges_code_throws_on_api_failure(): void
    {
        Http::fake([
            'test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'error' => 'invalid_request',
            ], 400),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to exchange authorization code');

        $this->service->exchangeCodeForToken('test-store.myshopify.com', 'invalid-code');
    }

    public function test_get_store_by_domain_returns_active_store(): void
    {
        ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'test-token',
            'is_active' => true,
        ]);

        $store = $this->service->getStoreByDomain('test-store.myshopify.com');

        $this->assertNotNull($store);
        $this->assertEquals('test-store.myshopify.com', $store->shop_domain);
    }

    public function test_get_store_by_domain_returns_null_for_inactive(): void
    {
        ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'test-token',
            'is_active' => false,
        ]);

        $store = $this->service->getStoreByDomain('test-store.myshopify.com');

        $this->assertNull($store);
    }

    public function test_get_store_by_domain_normalizes_input(): void
    {
        ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'test-token',
            'is_active' => true,
        ]);

        $store = $this->service->getStoreByDomain('https://TEST-STORE.myshopify.com/');

        $this->assertNotNull($store);
    }
}
