<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ShopifyStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopifyStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_valid_credentials_when_active_with_token(): void
    {
        $store = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'shpat_test_token',
            'is_active' => true,
        ]);

        $this->assertTrue($store->hasValidCredentials());
    }

    public function test_has_invalid_credentials_when_inactive(): void
    {
        $store = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'shpat_test_token',
            'is_active' => false,
        ]);

        $this->assertFalse($store->hasValidCredentials());
    }

    public function test_has_invalid_credentials_when_no_token(): void
    {
        $store = new ShopifyStore([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => '',
            'is_active' => true,
        ]);

        $this->assertFalse($store->hasValidCredentials());
    }

    public function test_mark_as_uninstalled(): void
    {
        $store = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'shpat_test_token',
            'is_active' => true,
        ]);

        $store->markAsUninstalled();

        $this->assertFalse($store->is_active);
        $this->assertNotNull($store->uninstalled_at);
    }

    public function test_get_api_base_url(): void
    {
        $store = new ShopifyStore([
            'shop_domain' => 'test-store.myshopify.com',
        ]);

        $url = $store->getApiBaseUrl('2024-01');

        $this->assertEquals('https://test-store.myshopify.com/admin/api/2024-01', $url);
    }

    public function test_active_scope(): void
    {
        ShopifyStore::create([
            'shop_domain' => 'active-store.myshopify.com',
            'access_token' => 'token1',
            'is_active' => true,
        ]);

        ShopifyStore::create([
            'shop_domain' => 'inactive-store.myshopify.com',
            'access_token' => 'token2',
            'is_active' => false,
        ]);

        $activeStores = ShopifyStore::active()->get();

        $this->assertCount(1, $activeStores);
        $this->assertEquals('active-store.myshopify.com', $activeStores->first()->shop_domain);
    }

    public function test_access_token_is_hidden(): void
    {
        $store = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'secret-token',
            'is_active' => true,
        ]);

        $array = $store->toArray();

        $this->assertArrayNotHasKey('access_token', $array);
    }

    public function test_casts_dates_correctly(): void
    {
        $store = ShopifyStore::create([
            'shop_domain' => 'test-store.myshopify.com',
            'access_token' => 'test-token',
            'is_active' => true,
            'installed_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $store->installed_at);
    }
}
