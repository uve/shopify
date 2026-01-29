<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(): View
    {
        $featuredProducts = $this->productService->getFeaturedProducts(8);
        $categories = $this->productService->getAllActiveCategories();

        return view('home', compact('featuredProducts', 'categories'));
    }
}
