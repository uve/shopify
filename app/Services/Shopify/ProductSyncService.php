<?php

declare(strict_types=1);

namespace App\Services\Shopify;

use App\Contracts\Shopify\ShopifyClientInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSyncService
{
    public function __construct(
        private readonly ShopifyClientInterface $shopifyClient,
    ) {}

    public function syncFromShopify(): array
    {
        $result = ['created' => 0, 'updated' => 0, 'failed' => 0];

        $shopifyProducts = $this->shopifyClient->getProducts(['limit' => 250]);

        foreach ($shopifyProducts as $shopifyProduct) {
            try {
                $existing = Product::where('shopify_product_id', $shopifyProduct->id)->first();
                $product = $existing ?? new Product();

                $product->name = $shopifyProduct->title;
                $product->slug = $shopifyProduct->handle ?? Str::slug($shopifyProduct->title);
                $product->description = $shopifyProduct->bodyHtml;
                $product->is_active = $shopifyProduct->status === 'active';

                $variant = $shopifyProduct->getDefaultVariant();
                if ($variant) {
                    $product->price = (float) ($variant->price ?? 0);
                    $product->stock_quantity = $variant->inventoryQuantity ?? 0;
                }

                $mainImage = $shopifyProduct->getMainImage();
                if ($mainImage) {
                    $product->image = $mainImage->src;
                }

                if ($shopifyProduct->images->isNotEmpty()) {
                    $product->images = $shopifyProduct->images
                        ->map(fn($img) => $img->src)
                        ->filter()
                        ->values()
                        ->all();
                }

                $product->shopify_product_id = $shopifyProduct->id;
                $product->shopify_synced_at = now();

                if ($shopifyProduct->productType) {
                    $category = Category::firstOrCreate(
                        ['name' => $shopifyProduct->productType],
                        ['slug' => Str::slug($shopifyProduct->productType), 'is_active' => true]
                    );
                    $product->category_id = $category->id;
                } elseif (!$product->category_id) {
                    $category = Category::firstOrCreate(
                        ['name' => 'Uncategorized'],
                        ['slug' => 'uncategorized', 'is_active' => true]
                    );
                    $product->category_id = $category->id;
                }

                $product->save();

                $existing ? $result['updated']++ : $result['created']++;
            } catch (\Throwable $e) {
                $result['failed']++;
            }
        }

        return $result;
    }
}
