<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories for a clothing store
        $categories = [
            [
                'name' => 'T-Shirts',
                'slug' => 't-shirts',
                'description' => 'Comfortable and stylish t-shirts for everyday wear.',
            ],
            [
                'name' => 'Shirts',
                'slug' => 'shirts',
                'description' => 'Formal and casual shirts for all occasions.',
            ],
            [
                'name' => 'Pants',
                'slug' => 'pants',
                'description' => 'Jeans, chinos, and dress pants.',
            ],
            [
                'name' => 'Dresses',
                'slug' => 'dresses',
                'description' => 'Beautiful dresses for any event.',
            ],
            [
                'name' => 'Jackets',
                'slug' => 'jackets',
                'description' => 'Stay warm and stylish with our jacket collection.',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Belts, scarves, and more.',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create sample products
        $products = [
            // T-Shirts
            [
                'category' => 't-shirts',
                'name' => 'Classic White Tee',
                'slug' => 'classic-white-tee',
                'description' => 'A timeless white t-shirt made from 100% organic cotton. Perfect for layering or wearing on its own.',
                'price' => 29.99,
                'compare_at_price' => null,
                'sku' => 'TS-WHT-001',
                'stock_quantity' => 50,
                'is_featured' => true,
                'size' => 'M',
                'color' => 'White',
                'material' => 'Cotton',
            ],
            [
                'category' => 't-shirts',
                'name' => 'Vintage Logo Tee',
                'slug' => 'vintage-logo-tee',
                'description' => 'Retro-inspired graphic tee with a vintage wash finish.',
                'price' => 34.99,
                'compare_at_price' => 44.99,
                'sku' => 'TS-VNT-002',
                'stock_quantity' => 35,
                'is_featured' => true,
                'size' => 'L',
                'color' => 'Black',
                'material' => 'Cotton',
            ],
            // Shirts
            [
                'category' => 'shirts',
                'name' => 'Oxford Button-Down',
                'slug' => 'oxford-button-down',
                'description' => 'Classic oxford cloth button-down shirt. A wardrobe essential.',
                'price' => 79.99,
                'compare_at_price' => null,
                'sku' => 'SH-OXF-001',
                'stock_quantity' => 25,
                'is_featured' => true,
                'size' => 'M',
                'color' => 'Blue',
                'material' => 'Cotton',
            ],
            [
                'category' => 'shirts',
                'name' => 'Linen Summer Shirt',
                'slug' => 'linen-summer-shirt',
                'description' => 'Breathable linen shirt perfect for warm weather.',
                'price' => 89.99,
                'compare_at_price' => 109.99,
                'sku' => 'SH-LIN-002',
                'stock_quantity' => 20,
                'is_featured' => false,
                'size' => 'L',
                'color' => 'White',
                'material' => 'Linen',
            ],
            // Pants
            [
                'category' => 'pants',
                'name' => 'Slim Fit Chinos',
                'slug' => 'slim-fit-chinos',
                'description' => 'Versatile slim-fit chinos that work for office or weekend.',
                'price' => 69.99,
                'compare_at_price' => null,
                'sku' => 'PN-CHN-001',
                'stock_quantity' => 40,
                'is_featured' => true,
                'size' => '32',
                'color' => 'Khaki',
                'material' => 'Cotton',
            ],
            [
                'category' => 'pants',
                'name' => 'Classic Denim Jeans',
                'slug' => 'classic-denim-jeans',
                'description' => 'Premium denim jeans with a classic straight fit.',
                'price' => 99.99,
                'compare_at_price' => null,
                'sku' => 'PN-DNM-002',
                'stock_quantity' => 30,
                'is_featured' => true,
                'size' => '32',
                'color' => 'Blue',
                'material' => 'Denim',
            ],
            // Dresses
            [
                'category' => 'dresses',
                'name' => 'Floral Midi Dress',
                'slug' => 'floral-midi-dress',
                'description' => 'Elegant midi dress with a beautiful floral print.',
                'price' => 129.99,
                'compare_at_price' => 159.99,
                'sku' => 'DR-FLR-001',
                'stock_quantity' => 15,
                'is_featured' => true,
                'size' => 'S',
                'color' => 'Multicolor',
                'material' => 'Polyester',
            ],
            [
                'category' => 'dresses',
                'name' => 'Little Black Dress',
                'slug' => 'little-black-dress',
                'description' => 'The essential little black dress for any occasion.',
                'price' => 149.99,
                'compare_at_price' => null,
                'sku' => 'DR-BLK-002',
                'stock_quantity' => 20,
                'is_featured' => false,
                'size' => 'M',
                'color' => 'Black',
                'material' => 'Silk',
            ],
            // Jackets
            [
                'category' => 'jackets',
                'name' => 'Leather Biker Jacket',
                'slug' => 'leather-biker-jacket',
                'description' => 'Premium leather biker jacket with classic styling.',
                'price' => 299.99,
                'compare_at_price' => 399.99,
                'sku' => 'JK-LTH-001',
                'stock_quantity' => 10,
                'is_featured' => true,
                'size' => 'M',
                'color' => 'Black',
                'material' => 'Leather',
            ],
            [
                'category' => 'jackets',
                'name' => 'Denim Trucker Jacket',
                'slug' => 'denim-trucker-jacket',
                'description' => 'Classic denim jacket with a modern fit.',
                'price' => 119.99,
                'compare_at_price' => null,
                'sku' => 'JK-DNM-002',
                'stock_quantity' => 25,
                'is_featured' => false,
                'size' => 'L',
                'color' => 'Blue',
                'material' => 'Denim',
            ],
            // Accessories
            [
                'category' => 'accessories',
                'name' => 'Leather Belt',
                'slug' => 'leather-belt',
                'description' => 'Genuine leather belt with brushed metal buckle.',
                'price' => 49.99,
                'compare_at_price' => null,
                'sku' => 'AC-BLT-001',
                'stock_quantity' => 50,
                'is_featured' => false,
                'size' => 'One Size',
                'color' => 'Brown',
                'material' => 'Leather',
            ],
            [
                'category' => 'accessories',
                'name' => 'Wool Scarf',
                'slug' => 'wool-scarf',
                'description' => 'Warm and soft wool scarf for cold days.',
                'price' => 39.99,
                'compare_at_price' => 54.99,
                'sku' => 'AC-SCF-002',
                'stock_quantity' => 35,
                'is_featured' => false,
                'size' => 'One Size',
                'color' => 'Gray',
                'material' => 'Wool',
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            unset($productData['category']);
            
            Product::create(array_merge($productData, [
                'category_id' => $category->id,
            ]));
        }
    }
}
