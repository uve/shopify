<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('search');
        
        if ($search) {
            $products = $this->productService->searchProducts($search);
        } else {
            $products = $this->productService->getAllActiveProducts();
        }

        $categories = $this->productService->getAllActiveCategories();

        return view('products.index', compact('products', 'categories', 'search'));
    }

    public function show(string $slug): View
    {
        $product = $this->productService->getProductBySlug($slug);

        if (!$product) {
            abort(404);
        }

        $relatedProducts = $this->productService->getRelatedProducts($product);

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function category(string $slug): View
    {
        $category = $this->productService->getCategoryBySlug($slug);

        if (!$category) {
            abort(404);
        }

        $products = $this->productService->getProductsByCategory($category);
        $categories = $this->productService->getAllActiveCategories();

        return view('products.category', compact('category', 'products', 'categories'));
    }
}
