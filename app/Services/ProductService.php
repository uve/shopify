<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function getAllActiveProducts(int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getProductsByCategory(Category $category, int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('is_active', true)
            ->where('category_id', $category->id)
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->first();
    }

    public function searchProducts(string $query, int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('category')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAllActiveCategories(): Collection
    {
        return Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();
    }

    public function getCategoryBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
