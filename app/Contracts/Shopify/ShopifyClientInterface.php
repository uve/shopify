<?php

declare(strict_types=1);

namespace App\Contracts\Shopify;

use App\DTOs\Shopify\ShopifyProductDTO;
use Illuminate\Support\Collection;

interface ShopifyClientInterface
{
    public function getProducts(array $params = []): Collection;
    public function getProduct(int $productId): ?ShopifyProductDTO;
}
