<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Shopify;

use App\Contracts\Shopify\ShopifyClientInterface;
use App\DTOs\Shopify\ShopifyProductDTO;
use App\Services\Shopify\ShopifyApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopifyApiClientTest extends TestCase
{
    private ShopifyApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new ShopifyApiClient('test.myshopify.com', null, 'test-token');
    }

    public function test_implements_interface(): void
    {
        $this->assertInstanceOf(ShopifyClientInterface::class, $this->client);
    }

    public function test_get_products_returns_collection_of_dtos(): void
    {
        Http::fake([
            '*/products.json*' => Http::response([
                'products' => [
                    [
                        'id' => 12345,
                        'title' => 'Test Product',
                        'status' => 'active',
                        'variants' => [['id' => 111, 'price' => '29.99']],
                        'images' => [['src' => 'https://cdn.shopify.com/img.jpg']],
                    ],
                ],
            ]),
        ]);

        $products = $this->client->getProducts();

        $this->assertCount(1, $products);
        $this->assertInstanceOf(ShopifyProductDTO::class, $products->first());
        $this->assertSame(12345, $products->first()->id);
    }

    public function test_get_products_sends_correct_headers(): void
    {
        Http::fake(['*/products.json*' => Http::response(['products' => []])]);

        $this->client->getProducts();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('X-Shopify-Access-Token', 'test-token');
        });
    }
}
