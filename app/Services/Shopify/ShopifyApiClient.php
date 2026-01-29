<?php

declare(strict_types=1);

namespace App\Services\Shopify;

use App\Contracts\Shopify\ShopifyClientInterface;
use App\DTOs\Shopify\ShopifyProductDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ShopifyApiClient implements ShopifyClientInterface
{
    private string $storeDomain;
    private string $apiVersion;
    private string $accessToken;

    public function __construct(
        string $storeDomain,
        ?string $apiVersion = null,
        string $accessToken = '',
    ) {
        $this->storeDomain = $this->normalizeDomain($storeDomain);
        $this->apiVersion = $apiVersion ?? config('shopify.api_version', '2026-01');
        $this->accessToken = $accessToken;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        if (!str_ends_with($domain, '.myshopify.com')) {
            $domain .= '.myshopify.com';
        }
        return $domain;
    }

    public function getProducts(array $params = []): Collection
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ])->get($this->url('products.json'), $params);

        if (!$response->successful()) {
            return collect();
        }

        return collect($response->json('products', []))
            ->map(fn(array $data) => ShopifyProductDTO::fromArray($data));
    }

    public function getProduct(int $productId): ?ShopifyProductDTO
    {
        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ])->get($this->url("products/{$productId}.json"));

        if (!$response->successful()) {
            return null;
        }

        $product = $response->json('product');
        return $product ? ShopifyProductDTO::fromArray($product) : null;
    }

    private function url(string $endpoint): string
    {
        return "https://{$this->storeDomain}/admin/api/{$this->apiVersion}/{$endpoint}";
    }
}
