<?php

declare(strict_types=1);

namespace Tests\Feature\Shopify;

use App\Models\ShopifyStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shopify.oauth.api_key' => 'test-api-key',
            'shopify.oauth.api_secret' => 'test-api-secret',
            'shopify.oauth.scopes' => 'read_products,write_products',
            'shopify.oauth.redirect_uri' => 'http://localhost/shopify/oauth/callback',
        ]);
    }

    public function test_install_shows_form_without_shop(): void
    {
        $response = $this->get(route('shopify.oauth.install'));

        $response->assertStatus(200);
        $response->assertViewIs('shopify.install');
    }

    public function test_install_redirects_to_shopify_oauth(): void
    {
        $response = $this->get(route('shopify.oauth.install', ['shop' => 'test-store.myshopify.com']));

        $response->assertRedirect();
        $this->assertStringContainsString('test-store.myshopify.com/admin/oauth/authorize', $response->headers->get('Location'));
    }

    public function test_install_shows_error_for_invalid_domain(): void
    {
        $response = $this->get(route('shopify.oauth.install', ['shop' => 'invalid-domain']));

        $response->assertStatus(200);
        $response->assertViewIs('shopify.install');
        $response->assertViewHas('error');
    }

    public function test_install_redirects_to_success_for_connected_store(): void
    {
        ShopifyStore::create([
            'shop_domain' => 'connected-store.myshopify.com',
            'access_token' => 'valid-token',
            'is_active' => true,
        ]);

        $response = $this->get(route('shopify.oauth.install', ['shop' => 'connected-store.myshopify.com']));

        $response->assertRedirect(route('shopify.oauth.success'));
    }

    public function test_callback_shows_error_without_required_params(): void
    {
        $response = $this->get(route('shopify.oauth.callback'));

        $response->assertStatus(200);
        $response->assertViewIs('shopify.install');
        $response->assertViewHas('error');
    }

    public function test_callback_shows_error_for_invalid_hmac(): void
    {
        $response = $this->get(route('shopify.oauth.callback', [
            'shop' => 'test-store.myshopify.com',
            'code' => 'test-code',
            'hmac' => 'invalid-hmac',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('shopify.install');
        $response->assertViewHas('error');
    }

    public function test_callback_exchanges_code_and_redirects_to_success(): void
    {
        // Build params including state for correct HMAC calculation
        $params = [
            'code' => 'test-code',
            'shop' => 'test-store.myshopify.com',
            'state' => 'test-nonce',
            'timestamp' => (string) time(),
        ];
        
        // Generate valid HMAC (must include all params except hmac itself)
        ksort($params);
        $hmac = hash_hmac('sha256', http_build_query($params), 'test-api-secret');
        $params['hmac'] = $hmac;

        // Mock the token exchange
        Http::fake([
            'test-store.myshopify.com/admin/oauth/access_token' => Http::response([
                'access_token' => 'shpat_new_token',
                'scope' => 'read_products,write_products',
            ], 200),
        ]);

        // Store nonce in cache (not session - we switched to cache-based validation)
        Cache::put('shopify_oauth_nonce:test-nonce', 'test-store.myshopify.com', now()->addMinutes(10));

        $response = $this->get(route('shopify.oauth.callback', $params));

        $response->assertRedirect(route('shopify.oauth.success'));

        $this->assertDatabaseHas('shopify_stores', [
            'shop_domain' => 'test-store.myshopify.com',
            'is_active' => true,
        ]);
    }

    public function test_success_page_displays(): void
    {
        $response = $this->withSession(['shop' => 'test-store.myshopify.com'])
            ->get(route('shopify.oauth.success'));

        $response->assertStatus(200);
        $response->assertViewIs('shopify.success');
    }
}
